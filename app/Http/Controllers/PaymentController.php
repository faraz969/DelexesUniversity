<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\FormType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PaymentController extends Controller
{
    private $merchantKey = '4b67b159-e26d-4d23-96f0-b270b38e5cb7';
    private $hashingKey = 'f715db94f4bfe648cd69d85c4c89229668e8520f2eddd81d5f841297f55e15e0e010dac5be89738c4d540dce1d5aa587d25566abac6a6b7d303a6dbc9350679b';
    private $gatewayUrl = 'https://pgw.paywithonline.com/v1/mobile_agents_v2';
    private $statusCheckUrl = 'https://pgw.paywithonline.com/v1/gateway/json_status_chk';

    /**
     * Initiate payment with EcobankPay
     */
    public function initiatePayment(Request $request)
    {
        // Log incoming request for debugging
        Log::info('Payment Initiation Request', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'country_code' => 'required|string',
                'phone' => 'required|string|max:20',
                'nationality' => 'required|string|max:255',
                'form_type' => 'required|exists:form_types,id',
                
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Payment Validation Error', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_flatten($e->errors())),
                'errors' => $e->errors()
            ], 400);
        }

        // Get form type details
        $formType = FormType::find($validated['form_type']);
        
        if (!$formType) {
            Log::error('Form Type Not Found', ['form_type_id' => $validated['form_type']]);
            return response()->json([
                'success' => false,
                'message' => 'Selected form type not found.'
            ], 400);
        }
        
        // Determine student type and calculate price
        $isLocal = $validated['nationality'] === 'Ghana';
        $price = $isLocal ? $formType->local_price : $formType->international_price;
        
        // Convert to GHS if international student
        if (!$isLocal && $formType->conversion_rate) {
            $price = $price * $formType->conversion_rate;
        }

        Log::info('Price Calculation', [
            'form_type' => $formType->name,
            'is_local' => $isLocal,
            'original_price' => $isLocal ? $formType->local_price : $formType->international_price,
            'conversion_rate' => $formType->conversion_rate,
            'final_price' => $price
        ]);

        // Generate unique invoice ID - using simpler format
        $invoiceId = 'DUC' . time();

        // Format total WITHOUT thousand separators to avoid gateway rejection
        // Ensure a dot as decimal separator and no grouping separators
        $formattedTotal = number_format((float) $price, 2, '.', '');

        // Create secure hash - parameters must be sorted alphabetically
        // According to sample: invoice_id=test001&merchant_key=xxx-xxx&total=1.00
        $queryString = "invoice_id={$invoiceId}&merchant_key={$this->merchantKey}&total={$formattedTotal}";
        $secureHash = strtoupper(hash_hmac('sha256', $queryString, hex2bin($this->hashingKey)));

        Log::info('Hash Generation', [
            'query_string' => $queryString,
            'hashing_key' => $this->hashingKey,
            'generated_hash' => $secureHash,
            'invoice_id' => $invoiceId,
            'merchant_key' => $this->merchantKey,
            'total' => $formattedTotal
        ]);

        // Prepare payment data - following the sample format
        $paymentData = [
            'merchant_key' => $this->merchantKey,
            'total' => $formattedTotal, // Use dot-decimal, no thousand separators
            'invoice_id' => $invoiceId,
    "success_url"=> route('payment.success'),
    "cancel_url"=> route('payment.cancelled'),
    "ipn_url"=>"https://webhook.site/4324234243",
    "extra_outlet"=>1061,
    "generate_checkout_url"=>true,
            
            'secure_hash' => $secureHash,
            //'pymt_instrument' => $validated['country_code'] . $validated['phone'],
        ];

        // Store pending registration data in session
        session([
            'pending_registration' => [
                'user_data' => $validated,
                'form_type' => $formType,
                'invoice_id' => $invoiceId,
                'amount' => $price,
                'is_local' => $isLocal,
                'payment_mode' => $request->input('payment_mode', 'ecobank')
            ]
        ]);

        // Branch: GCB vs Ecobank based on selected payment method
        $selectedPaymentMode = strtolower($request->input('payment_mode', 'ecobank'));

        if ($selectedPaymentMode === 'gcb') {
            try {
                // Build GCB Checkout request
                $gcbApiKey = env('GCB_API_KEY', 'GCB-Cp98RM7YKey6JUlMyzk1uQALX7IkhQuC');
                $maskedKey = substr($gcbApiKey, 0, 6) . '...' . substr($gcbApiKey, -4);
                // Use provided base URL for GCB UAT, configurable via env
                $gcbBaseUrl = rtrim(env('GCB_BASE_URL', 'https://epay.gcbltd.com:211/'), '/');
                $gcbCheckoutUrl = $gcbBaseUrl . '/checkout';

                $payload = [
                    'merchantRef' => $invoiceId,
                    'amount' => (float) number_format((float) $price, 2, '.', ''),
                    'currency' => 'GHS',
                    'description' => 'Admission Form Purchase - ' . $formType->name,
                    // Intentionally omit paymentOption to let gateway decide (UAT docs show it optional)
                    // 'paymentOption' => null,
                    'callBackUrl' => route('payment.success'),
                ];
                // Remove any null/empty values to avoid schema rejection
                $payload = array_filter($payload, function ($v) { return !is_null($v) && $v !== ''; });

                Log::info('Initiating GCB Checkout', [
                    'url' => $gcbCheckoutUrl,
                    'payload' => $payload,
                    'api_key_present' => !empty($gcbApiKey),
                    'api_key_masked' => $maskedKey,
                ]);

                $response = Http::timeout(30)
                    // UAT may use self-signed certs; allow disabling via env (default false)
                    ->withOptions(['verify' => env('GCB_TLS_VERIFY', false)])
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        // Some servers are finicky about header casing; send both
                        'X-Api-Key' => $gcbApiKey,
                        
                        // Some API gateways map the OpenAPI security scheme name to a header
                       
                        'Accept' => 'application/json',
                    ])
                    ->post($gcbCheckoutUrl, $payload);

                Log::info('GCB API Response', [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body(),
                ]);

                if (!$response->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'GCB gateway error (HTTP ' . $response->status() . ').',
                        'gateway_response' => $response->body(),
                        'headers' => $response->headers(),
                    ], 500);
                }

                $data = [];
                try { $data = $response->json(); } catch (\Throwable $t) { /* ignore json errors */ }

                // Try common fields for redirect URL
                $redirectUrl = $data['checkOutUrl'] ?? $data['payment_url'] ?? $data['url'] ?? $data['redirectUrl'] ?? null;
                if (!$redirectUrl) {
                    // Sometimes redirect comes via Location header
                    $locationHeader = $response->header('Location') ?? ($response->headers()['Location'][0] ?? null);
                    if ($locationHeader) {
                        $redirectUrl = $locationHeader;
                    }
                }

                if ($redirectUrl) {
                    return response()->json([
                        'success' => true,
                        'payment_url' => $redirectUrl,
                        'invoice_id' => $invoiceId,
                    ]);
                }

                // If API returns a checkout identifier only
                $checkoutId = $data['checkOutId'] ?? $data['checkoutId'] ?? null;
                session([
                    'checkoutid' => $checkoutId
                ]);
                $sessioncheck=session('checkoutid');

                Log::info('GCB Checkout ID', ['checkoutid' => $sessioncheck]);
                if ($checkoutId) {
                    // Try building a hosted URL under the same prefix if applicable
                    $hostedUrl = 'https://epay.gcbltd.com:211/checkout?id=' . $checkoutId;
                    return response()->json([
                        'success' => true,
                        'payment_url' => $hostedUrl,
                        'invoice_id' => $invoiceId,
                    ]);

                   
                }

                // Fallback: unknown response
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected GCB response; no redirect URL provided.',
                    'gateway_response' => $data ?: $response->body(),
                ], 500);
            } catch (\Exception $e) {
                Log::error('GCB Initiation Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while initiating GCB payment.',
                ], 500);
            }
        }

        try {
            // Log payment data being sent
            Log::info('Sending Payment Data to EcobankPay', [
                'payment_data' => $paymentData,
                'gateway_url' => $this->gatewayUrl
            ]);

            // Make API call to EcobankPay with JSON content type
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen(json_encode($paymentData))
                ])
                ->post($this->gatewayUrl, $paymentData);

            Log::info('EcobankPay API Response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('EcobankPay Response Data', ['response_data' => $responseData]);
                
                if (isset($responseData['success']) && $responseData['success']) {
                    return response()->json([
                        'success' => true,
                        'payment_url' => $responseData['url'],
                        'invoice_id' => $invoiceId
                    ]);
                } else {
                    Log::error('EcobankPay API Success False', [
                        'response_data' => $responseData
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment gateway returned error: ' . ($responseData['message'] ?? 'Unknown error'),
                        'gateway_response' => $responseData
                    ], 400);
                }
            } else {
                Log::error('EcobankPay API HTTP Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'headers' => $response->headers()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error (HTTP ' . $response->status() . '). Please try again.',
                    'gateway_response' => $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Payment Initiation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle payment success
     */
    public function paymentSuccess(Request $request)
    {
        // Log all incoming parameters for debugging
        Log::info('Payment Success Callback', [
            'query_params' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Get invoice ID from request or session
        $invoiceId = $request->get('invoice_id') ?? $request->get('merchantRef') ?? session('pending_registration.invoice_id');
        
        if (!$invoiceId) {
            return redirect()->route('registration.create')
                ->with('error', 'Invalid payment response.');
        }

        // Check if this was a GCB payment
        $paymentMode = session('pending_registration.payment_mode');
        if ($paymentMode === 'gcb') {
            // GCB may return checkOutId or status in query params
            $checkOutId = session('checkoutid');
            $statusParam = $request->get('statusCode') ?? $request->get('paymentStatus');

            Log::info('GCB Payment Return', [
                'invoice_id' => $invoiceId,
                'checkOutId' => $checkOutId,
                'status_param' => $statusParam,
            ]);

            // If checkOutId is present, verify status with GCB
            if ($checkOutId) {
                $verifiedStatus = $this->checkGcbPaymentStatus($checkOutId);
                Log::info('GCB Status Check Result', ['verified_status' => $verifiedStatus]);

                if (!in_array(strtolower($verifiedStatus), ['paid', 'success', 'completed', 'successful','00'])) {
                    return redirect()->route('payment.cancelled')
                        ->with('error', 'Payment was not completed. Status: ' . $verifiedStatus);
                }
            } elseif ($statusParam) {
                // Use status from query param if no checkOutId
                if (!in_array(strtolower($statusParam), ['paid', 'success', 'completed', 'successful'])) {
                    return redirect()->route('payment.cancelled')
                        ->with('error', 'Payment was not completed. Status: ' . $statusParam);
                }
            } else {
                // No status info: treat as cancelled
                return redirect()->route('payment.cancelled')
                    ->with('error', 'Payment status could not be verified.');
            }
        }

        // Simply complete registration without verification
        return $this->completeRegistration($invoiceId, ['status' => 'paid']);
    }

    /**
     * Handle payment cancellation
     */
    public function paymentCancelled(Request $request)
    {
        return redirect()->route('registration.create')
            ->with('error', 'Payment was cancelled. Please try again.');
    }

    /**
     * Handle IPN notifications
     */
    public function handleIpn(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        
        if (!$invoiceId) {
            return response('Invalid request', 400);
        }

        // Check payment status
        $paymentStatus = $this->checkPaymentStatus($invoiceId);
        
        Log::info('IPN Notification Received', [
            'invoice_id' => $invoiceId,
            'status' => $paymentStatus['status']
        ]);

        // If payment is successful, complete registration
        if ($paymentStatus['status'] === 'paid') {
            $this->completeRegistration($invoiceId, $paymentStatus);
        }

        return response('OK', 200);
    }

    /**
     * Check payment status with EcobankPay
     */
    private function checkPaymentStatus($invoiceId)
    {
        try {
            $response = Http::get($this->statusCheckUrl, [
                'merchant_key' => $this->merchantKey,
                'invoice_id' => $invoiceId
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Payment Status Check Failed', [
                    'invoice_id' => $invoiceId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['status' => 'failed'];
            }
        } catch (\Exception $e) {
            Log::error('Payment Status Check Error', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'failed'];
        }
    }

    /**
     * Check payment status with GCB
     */
    private function checkGcbPaymentStatus($checkOutId)
    {
        try {
            $gcbApiKey = env('GCB_API_KEY', 'GCB-Cp98RM7YKey6JUlMyzk1uQALX7IkhQuC');
            $gcbBaseUrl = rtrim(env('GCB_BASE_URL', 'https://epay.gcbltd.com:211'), '/');
            $statusUrl = $gcbBaseUrl . '/transactions/' . $checkOutId . '/status';

            Log::info('Checking GCB Payment Status', [
                'checkOutId' => $checkOutId,
                'url' => $statusUrl,
            ]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => env('GCB_TLS_VERIFY', false)])
                ->withHeaders([
                    'X-Api-Key' => $gcbApiKey,
                    'Accept' => 'application/json',
                ])
                ->get($statusUrl);

            Log::info('GCB Status API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Return the status field from response (adjust based on actual API response)
                return $data['status'] ?? $data['paymentStatus'] ?? 'unknown';
            } else {
                Log::error('GCB Status Check Failed', [
                    'checkOutId' => $checkOutId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return 'failed';
            }
        } catch (\Exception $e) {
            Log::error('GCB Status Check Error', [
                'checkOutId' => $checkOutId,
                'error' => $e->getMessage()
            ]);
            return 'failed';
        }
    }

    /**
     * Complete user registration after successful payment
     */
    private function completeRegistration($invoiceId, $paymentStatus)
    {
        $pendingData = session('pending_registration');
        $paymentAmount = $pendingData['amount'];
        
        if (!$pendingData || $pendingData['invoice_id'] !== $invoiceId) {
            return redirect()->route('registration.create')
                ->with('error', 'Invalid payment session. Please try again.');
        }

        $userData = $pendingData['user_data'];
        $formType = $pendingData['form_type'];
        $isLocal = $pendingData['is_local'];

        // Generate PIN
        $pin = Str::upper(Str::random(8));
        $pinExpiry = Carbon::now()->addMonths(3);
        
        // Generate unique serial number (DUC + random 6 digits)
        $serialNumber = $this->generateUniqueSerialNumber();

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['full_name'],
                'phone' => $userData['country_code'] . $userData['phone'],
                'nationality' => $userData['nationality'],
                'form_type_id' => isset($pendingData['form_type']->id) ? $pendingData['form_type']->id : null,
                'password' => Hash::make($pin),
                'pin' => $pin,
                'serial_number' => $serialNumber,
                'pin_expires_at' => $pinExpiry,
                'role' => 'user',
                'invoice_id' => $invoiceId,
                'payment'=> $paymentAmount
            ]
        );

        // Clear session data
        session()->forget('pending_registration');

        // Calculate display price and currency
        $displayPrice = $isLocal ? $formType->local_price : $formType->international_price;
        $currency = $isLocal ? '₵' : '$';
        $studentType = $isLocal ? 'local' : 'international';

        // Send SMS with PIN
        $this->sendSMS($user->phone, $pin, $user->name, $serialNumber);

        return view('registration.success', [
            'pin' => $pin,
            'serial_number' => $serialNumber,
            'email' => $user->email,
            'user' => $user,
            'pin_expires_at' => $pinExpiry,
            'form_type' => $formType->name,
            'price' => $displayPrice,
            'currency' => $currency,
            'student_type' => $studentType,
            'nationality' => $userData['nationality'],
            'payment_amount' => $pendingData['amount'],
            'payment_currency' => '₵',
            'invoice_id' => $invoiceId,
        ]);
    }

    /**
     * Send SMS notification
     */
    private function sendSMS($phone, $pin, $name, $serialNumber)
    {
            $message = "Hello {$name}, your registration Serial Number is: {$serialNumber} and PIN is: {$pin}. This PIN expires in 3 months. Use this PIN to login to your dashboard.";
            
            // Clean phone number (remove any non-numeric characters except +)
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            
        // Convert to format without + for Nalo (e.g., +233249318768 -> 0249318768)
        $naloPhone = $cleanPhone;
        if (strpos($cleanPhone, '+233') === 0) {
            $naloPhone = '0' . substr($cleanPhone, 4); // Replace +233 with 0
        } elseif (strpos($cleanPhone, '233') === 0) {
            $naloPhone = '0' . substr($cleanPhone, 3); // Replace 233 with 0
        }
        
        try {
            // Primary: Try Nalo SMS API
            $naloKey = env('NALO_SMS_KEY', 'LNMKky07fqvxVO6IK33I7UvuWMVXDR_sZnf8bDRnG7qu2ErL3vTM1farB5UYw26L');
            $naloSenderId = env('NALO_SENDER_ID', 'DELEXESUC');
            
            Log::info('Attempting SMS via Nalo API', [
                'phone' => $naloPhone,
                'original_phone' => $cleanPhone,
            ]);
            
            $naloResponse = Http::timeout(10)
                ->post('https://sms.nalosolutions.com/smsbackend/Resl_Nalo/send-message/', [
                    'key' => $naloKey,
                    'msisdn' => $naloPhone,
                    'message' => $message,
                    'sender_id' => $naloSenderId
                ]);

            // Log the response for debugging
            Log::info('Nalo SMS API Response', [
                'phone' => $naloPhone,
                'status' => $naloResponse->status(),
                'response' => $naloResponse->body(),
            ]);

            // Check if Nalo was successful
            if ($naloResponse->successful()) {
                $responseData = $naloResponse->json();
                // Nalo returns status codes like "1701" for success
                // Check if status exists and is not an error code (errors are usually 17xx range except 1701)
                if (isset($responseData['status']) && isset($responseData['job_id'])) {
                    // If job_id is present, SMS was queued/sent successfully
                    Log::info('SMS sent successfully via Nalo', [
                        'job_id' => $responseData['job_id'],
                        'status_code' => $responseData['status']
                    ]);
                    return;
                }
            }
            
            // If Nalo failed, log and fall through to backup
            Log::warning('Nalo SMS API failed or returned error, trying backup Arkesel API');

        } catch (\Exception $e) {
            Log::error('Nalo SMS API Exception', [
                'phone' => $naloPhone,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: Try Arkesel SMS API
        try {
            $arkeselApiKey = env('ARKESEL_SMS_KEY', 'Ok1GNWlYWFB0VHI1NHJZUUQ=');
            $arkeselSenderId = env('ARKESEL_SENDER_ID', 'UNIVERSITY');
            
            Log::info('Attempting SMS via Arkesel API (Backup)', [
                'phone' => $cleanPhone,
            ]);
            
            $arkeselResponse = Http::timeout(10)
                ->get('https://sms.arkesel.com/sms/api', [
                'action' => 'send-sms',
                    'api_key' => $arkeselApiKey,
                'to' => $cleanPhone,
                    'from' => $arkeselSenderId,
                'sms' => $message
            ]);

            // Log the response for debugging
            Log::info('Arkesel SMS API Response (Backup)', [
                'phone' => $cleanPhone,
                'response' => $arkeselResponse->body(),
                'status' => $arkeselResponse->status()
            ]);

        } catch (\Exception $e) {
            Log::error('Both SMS APIs failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get wallet issuer hint based on payment mode
     */
    private function getWalletIssuerHint($paymentMode)
    {
        switch (strtolower($paymentMode)) {
            case 'mtn mobile money':
            case 'mtn':
                return 'mtn';
            case 'vodafone cash':
            case 'vodafone':
                return 'vodafone';
            case 'airteltigo money':
            case 'airteltigo':
                return 'airteltigo';
            case 'visa':
            case 'mastercard':
                return 'card';
            case 'qr':
            case 'qr code':
                return 'qr';
            default:
                return 'mtn'; // Default to MTN
        }
    }

    /**
     * Generate a unique serial number: DUC + 6 random digits
     */
    private function generateUniqueSerialNumber()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            // Generate DUC + 6 random digits (100000 to 999999)
            $randomNumber = rand(100000, 999999);
            $serialNumber = 'DUC' . $randomNumber;

            // Check if it already exists
            $exists = User::where('serial_number', $serialNumber)->exists();
            
            $attempt++;
            
            if (!$exists) {
                return $serialNumber;
            }
            
            if ($attempt >= $maxAttempts) {
                // Fallback: use timestamp-based unique serial
                return 'DUC' . substr(time(), -6);
            }
        } while ($exists);

        return $serialNumber;
    }
}