<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\FormType;

class BankController extends Controller
{
    public function dashboard(Request $request)
    {
        $bankUser = Auth::user();
        
        // Get only users created by this bank user
        $query = User::where('created_by', $bankUser->id);
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('bank.dashboard', compact('users', 'bankUser'));
    }

    public function createUser()
    {
        $formTypes = FormType::active()->orderBy('name')->get();
        return view('bank.create-user', compact('formTypes'));
    }

    public function storeUser(Request $request)
    {
        $bankUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string|max:255',
            'form_type_id' => 'required|exists:form_types,id',
            'voucher_for' => 'nullable|string|max:255',
        ]);

        // Generate a unique email if not provided (to satisfy unique constraint)
        if (empty($validated['email'])) {
            $validated['email'] = 'user_' . time() . '_' . rand(1000, 9999) . '@bank.created';
        }

        // Generate a random PIN (8 uppercase alphanumeric)
        $pin = Str::upper(Str::random(8));
        $pinExpiry = \Carbon\Carbon::now()->addMonths(3);
        
        // Generate serial number for students
        $serialNumber = $this->generateUniqueSerialNumber();
        
        // Get form type for pricing
        $formType = FormType::find($validated['form_type_id']);
        $isLocal = strtolower(trim($validated['nationality'])) === 'ghana';
        $amount = $isLocal ? $formType->local_price : $formType->international_price;
        
        // Convert to GHS if international student
        if (!$isLocal && $formType->conversion_rate) {
            $amount = $amount * $formType->conversion_rate;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'nationality' => $validated['nationality'],
            'form_type_id' => $validated['form_type_id'],
            'role' => 'user',
            'password' => Hash::make($pin),
            'pin' => $pin,
            'serial_number' => $serialNumber,
            'pin_expires_at' => $pinExpiry,
            'created_by' => $bankUser->id,
        ]);

        // Generate receipt number
        $receiptNumber = $this->generateReceiptNumber();
        
        // Store receipt data in user's payment field or create a receipt record
        // For now, we'll store it in a JSON format in a custom field or use the payment field
        $user->payment = json_encode([
            'receipt_number' => $receiptNumber,
            'amount' => $amount,
            'form_type' => $formType->name,
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'academic_year' => '2025/2026',
            'voucher_for' => $validated['voucher_for'] ?? null,
        ]);
        $user->save();

        return redirect()->route('bank.dashboard')
            ->with('success', 'User created successfully. PIN: ' . $pin . ', Serial Number: ' . $serialNumber);
    }

    public function downloadReceipt($userId)
    {
        $bankUser = Auth::user();
        $user = User::where('id', $userId)
            ->where('created_by', $bankUser->id)
            ->firstOrFail();
        
        $formType = $user->formType;
        if (!$formType) {
            return redirect()->route('bank.dashboard')
                ->with('error', 'Form type not found for this user.');
        }

        // Get payment data
        $paymentData = $user->payment ? json_decode($user->payment, true) : [];
        $receiptNumber = $paymentData['receipt_number'] ?? $this->generateReceiptNumber();
        $isLocalForAmount = strtolower(trim($user->nationality ?? '')) === 'ghana';
        $amount = $paymentData['amount'] ?? ($isLocalForAmount ? $formType->local_price : $formType->international_price);
        $transactionDate = $paymentData['transaction_date'] ?? $user->created_at->format('Y-m-d H:i:s');
        $academicYear = $paymentData['academic_year'] ?? '2025/2026';
        $voucherFor = $paymentData['voucher_for'] ?? null;

        // Determine amount based on nationality if not stored
        if (!isset($paymentData['amount'])) {
            $isLocal = strtolower(trim($user->nationality ?? '')) === 'ghana';
            $amount = $isLocal ? $formType->local_price : $formType->international_price;
            if (!$isLocal && $formType->conversion_rate) {
                $amount = $amount * $formType->conversion_rate;
            }
        }

        // Store receipt number if not already stored
        if (!isset($paymentData['receipt_number'])) {
            $user->payment = json_encode([
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'form_type' => $formType->name,
                'transaction_date' => $transactionDate,
                'academic_year' => $academicYear,
                'voucher_for' => $voucherFor,
            ]);
            $user->save();
        }

        // Build payment description
        $paymentDescription = 'Payment of Voucher';
        if ($voucherFor) {
            $paymentDescription .= ' for ' . $voucherFor;
        } else {
            $paymentDescription .= ' for ' . $user->name;
        }

        $data = [
            'receipt_number' => $receiptNumber,
            'institution' => 'Delexes University College',
            'form_type' => $formType->name,
            'serial_number' => $user->serial_number,
            'pin' => $user->pin,
            'bank_name' => $bankUser->bank_name,
            'branch' => $bankUser->branch,
            'bank_logo' => $bankUser->logo ? asset('storage/' . $bankUser->logo) : null,
            'academic_year' => $academicYear,
            'transaction_date' => $transactionDate,
            'payment_description' => $paymentDescription,
            'amount_paid' => number_format($amount, 2),
            'paid_by' => $user->name,
            'voucher_for' => $voucherFor,
        ];

        return view('bank.receipt', $data);
    }

    private function generateUniqueSerialNumber()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $randomNumber = rand(100000, 999999);
            $serialNumber = 'DUC' . $randomNumber;
            $exists = User::where('serial_number', $serialNumber)->exists();
            $attempt++;
            
            if (!$exists) {
                return $serialNumber;
            }
            
            if ($attempt >= $maxAttempts) {
                return 'DUC' . substr(time(), -6);
            }
        } while ($exists);

        return $serialNumber;
    }

    private function generateReceiptNumber()
    {
        return strtoupper(Str::random(20));
    }
}
