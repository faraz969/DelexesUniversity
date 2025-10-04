<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\FormType;

class ApiController extends Controller
{
    /**
     * Create user API endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:20',
                'nationality' => 'required|string|max:255',
                'form_type_id' => 'required|exists:form_types,id',
                'invoice_id' => 'nullable|string|max:255',
                'payment_amount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Get form type details
            $formType = FormType::find($validated['form_type_id']);
            if (!$formType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form type not found'
                ], 404);
            }

            // Check if user already exists
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already exists with this email',
                    'user_id' => $existingUser->id,
                    'serial_number' => $existingUser->serial_number,
                    'pin' => $existingUser->pin,
                    'pin_expires_at' => $existingUser->pin_expires_at
                ], 409);
            }

            // Generate PIN
            $pin = Str::upper(Str::random(8));
            $pinExpiry = Carbon::now()->addMonths(3);
            
            // Generate serial number
            $serialNumber = 'DUX' . date('Y') . str_pad(User::count() + 1, 6, '0', STR_PAD_LEFT);

            // Create user
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'nationality' => $validated['nationality'],
                'form_type_id' => $validated['form_type_id'],
                'password' => Hash::make($pin),
                'pin' => $pin,
                'serial_number' => $serialNumber,
                'pin_expires_at' => $pinExpiry,
                'role' => 'user',
                'invoice_id' => $validated['invoice_id'] ?? null,
                'payment' => $validated['payment_amount'] ?? null,
            ]);

            // Send SMS notification if phone is provided
            if (!empty($validated['phone'])) {
                $this->sendSMS($validated['phone'], $pin, $validated['full_name'], $serialNumber);
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'nationality' => $user->nationality,
                    'form_type' => $formType->name,
                    'serial_number' => $user->serial_number,
                    'pin' => $user->pin,
                    'pin_expires_at' => $user->pin_expires_at->format('Y-m-d H:i:s'),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMS($phone, $pin, $name, $serialNumber)
    {
        try {
            $apiKey = 'Ok1GNWlYWFB0VHI1NHJZUUQ=';
            $senderId = 'UNIVERSITY';
            $message = "Hello {$name}, your registration Serial Number is: {$serialNumber} and PIN is: {$pin}. This PIN expires in 3 months. Use this PIN to login to your dashboard.";
            
            // Clean phone number (remove any non-numeric characters except +)
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            
            $response = \Illuminate\Support\Facades\Http::get('https://sms.arkesel.com/sms/api', [
                'action' => 'send-sms',
                'api_key' => $apiKey,
                'to' => $cleanPhone,
                'from' => $senderId,
                'sms' => $message
            ]);

            // Log the response for debugging
            \Illuminate\Support\Facades\Log::info('SMS API Response (API User Creation)', [
                'phone' => $cleanPhone,
                'response' => $response->body(),
                'status' => $response->status()
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SMS sending failed (API User Creation)', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get user by ID API endpoint
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request, $id)
    {
        try {
            $user = User::with('formType')->find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'nationality' => $user->nationality,
                    'form_type' => $user->formType->name ?? null,
                    'serial_number' => $user->serial_number,
                    'pin_expires_at' => $user->pin_expires_at->format('Y-m-d H:i:s'),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'role' => $user->role,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List users API endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUsers(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            
            $query = User::with('formType');
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->map(function($user) {
                    return [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'nationality' => $user->nationality,
                        'form_type' => $user->formType->name ?? null,
                        'serial_number' => $user->serial_number,
                        'pin_expires_at' => $user->pin_expires_at->format('Y-m-d H:i:s'),
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        'role' => $user->role,
                    ];
                }),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while listing users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all form types API endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormTypes()
    {
        try {
            $formTypes = FormType::select('id', 'name', 'local_price', 'international_price', 'conversion_rate', 'description', 'is_active', 'created_at')
                ->where('is_active', true) // Only return active form types
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Form types retrieved successfully',
                'data' => $formTypes->map(function($formType) {
                    return [
                        'id' => $formType->id,
                        'name' => $formType->name,
                        'local_price' => round($formType->local_price, 2),
                        'international_price' => round($formType->international_price, 2),
                        'conversion_rate' => $formType->conversion_rate ? round($formType->conversion_rate, 4) : null,
                        'description' => $formType->description,
                        'is_active' => $formType->is_active,
                        'created_at' => $formType->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching form types: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single form type API endpoint
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormType($id)
    {
        try {
            $formType = FormType::select('id', 'name', 'local_price', 'international_price', 'conversion_rate', 'description', 'is_active', 'created_at')
                ->find($id);

            if (!$formType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form type not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $formType->id,
                    'name' => $formType->name,
                    'local_price' => round($formType->local_price, 2),
                    'international_price' => round($formType->international_price, 2),
                    'conversion_rate' => $formType->conversion_rate ? round($formType->conversion_rate, 4) : null,
                    'description' => $formType->description,
                    'is_active' => $formType->is_active,
                    'created_at' => $formType->created_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching form type: ' . $e->getMessage()
            ], 500);
        }
    }
}