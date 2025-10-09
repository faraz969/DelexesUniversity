<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\FormType;
use App\Models\User;

use App\Helpers\CountryCodes;
use Illuminate\Support\Facades\Http;

class RegistrationController extends Controller
{
    public function show()
    {
        $formTypes = FormType::active()->orderBy('name')->get();
        $countries = CountryCodes::getCountries();
        return view('registration.create', compact('formTypes', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_code' => 'required|string',
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string|max:255',
            'form_type' => 'required|exists:form_types,id',
        ]);

        // Automatically determine student type based on nationality
        $validated['student_type'] = $validated['nationality'] === 'Ghana' ? 'local' : 'international';

        $pin = Str::upper(Str::random(8));
        $serialNumber = $this->generateUniqueSerialNumber();
        $pinExpiry = Carbon::now()->addMonths(3);

        $user = User::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['full_name'],
                'phone' => $validated['country_code'] . $validated['phone'],
                'nationality' => $validated['nationality'],
                'form_type_id' => (int) $validated['form_type'],
                'password' => Hash::make($pin),
                'pin' => $pin,
                'serial_number' => $serialNumber,
                'pin_expires_at' => $pinExpiry,
                'role' => 'user',
            ]
        );

        // Get form type details
        $formType = FormType::find($validated['form_type']);
        $price = $validated['student_type'] === 'local' ? $formType->local_price : $formType->international_price;
        $currency = $validated['student_type'] === 'local' ? '₵' : '$';

        // Send SMS with PIN
        $this->sendSMS($validated['country_code'] . $validated['phone'], $pin, $validated['full_name']);

        return view('registration.success', [
            'pin' => $pin,
            'serial_number' => $serialNumber,
            'email' => $user->email,
            'user' => $user,
            'pin_expires_at' => $pinExpiry,
            'form_type' => $formType->name,
            'price' => $price,
            'currency' => $currency,
            'student_type' => $validated['student_type'],
            'nationality' => $validated['nationality'],
        ]);
    }

    private function sendSMS($phoneNumber, $pin, $fullName)
    {
        $message = "Hello {$fullName}, your registration PIN is: {$pin}. This PIN expires in 3 months. Use this PIN to login to your dashboard.";
        
        // Clean phone number (remove any non-numeric characters except +)
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
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
            $naloSenderId = env('NALO_SENDER_ID', 'delexesuc');
            
            \Log::info('Attempting SMS via Nalo API', [
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
            \Log::info('Nalo SMS API Response', [
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
                    \Log::info('SMS sent successfully via Nalo', [
                        'job_id' => $responseData['job_id'],
                        'status_code' => $responseData['status']
                    ]);
                    return;
                }
            }
            
            // If Nalo failed, log and fall through to backup
            \Log::warning('Nalo SMS API failed or returned error, trying backup Arkesel API');

        } catch (\Exception $e) {
            \Log::error('Nalo SMS API Exception', [
                'phone' => $naloPhone,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: Try Arkesel SMS API
        try {
            $arkeselApiKey = env('ARKESEL_SMS_KEY', 'Ok1GNWlYWFB0VHI1NHJZUUQ=');
            $arkeselSenderId = env('ARKESEL_SENDER_ID', 'UNIVERSITY');
            
            \Log::info('Attempting SMS via Arkesel API (Backup)', [
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
            \Log::info('Arkesel SMS API Response (Backup)', [
                'phone' => $cleanPhone,
                'response' => $arkeselResponse->body(),
                'status' => $arkeselResponse->status()
            ]);

        } catch (\Exception $e) {
            \Log::error('Both SMS APIs failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a unique serial number: DUC + 6 random digits
     *
     * @return string
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
