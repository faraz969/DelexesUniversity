<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('department')->orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = [
            'user' => 'Student',
            'hod' => 'Head of Department',
            'registrar' => 'Registrar',
            'president' => 'President',
            'admin' => 'Administrator',
            'bank' => 'Bank'
        ];
        return view('admin.users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:user,hod,registrar,president,admin,bank',
            'department_id' => 'nullable|exists:departments,id',
            'nationality' => 'nullable|string|max:255',
            'form_type_id' => 'nullable|exists:form_types,id',
            'bank_name' => 'required_if:role,bank|nullable|string|max:255',
            'branch' => 'required_if:role,bank|nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Generate a random PIN (8 uppercase alphanumeric) used as login password
        $pin = Str::upper(Str::random(8));
        $pinExpiry = \Carbon\Carbon::now()->addMonths(3);
        
        // Generate serial number for students
        $serialNumber = null;
        if ($validated['role'] === 'user') {
            $serialNumber = $this->generateUniqueSerialNumber();
        }
        
        // Handle logo upload for bank users
        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('bank_logos', 'public');
        }
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'password' => Hash::make($pin),
            'pin' => $pin,
            'serial_number' => $serialNumber,
            'pin_expires_at' => $pinExpiry,
            'nationality' => $validated['nationality'] ?? null,
            'form_type_id' => $validated['form_type_id'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'logo' => $logoPath,
            'created_by' => Auth::id(),
        ]);

        // If admin picked a form to buy for a student, record it in user's data JSON (or create a relation later)
        if (!empty($validated['form_type_id']) && $user->role === 'user') {
            // Attach last selected form type into a simple column on users table if available, else store to meta in application draft
            // For now, we will persist into the latest application draft data if exists
            $application = $user->applications()->latest()->first();
            if ($application) {
                $data = is_array($application->data) ? $application->data : [];
                $data['form_type_id'] = (int) $validated['form_type_id'];
                $application->data = $data;
                $application->save();
            }
        }

        $successMessage = "User created successfully. PIN: {$pin}";
        if ($serialNumber) {
            $successMessage .= ", Serial Number: {$serialNumber}";
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('department');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = [
            'user' => 'Student',
            'hod' => 'Head of Department',
            'registrar' => 'Registrar',
            'president' => 'President',
            'admin' => 'Administrator',
            'bank' => 'Bank'
        ];
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:user,hod,registrar,president,admin,bank',
            'department_id' => 'nullable|exists:departments,id',
            'bank_name' => 'required_if:role,bank|nullable|string|max:255',
            'branch' => 'required_if:role,bank|nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload for bank users
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            // Delete old logo if exists
            if ($user->logo && Storage::disk('public')->exists($user->logo)) {
                Storage::disk('public')->delete($user->logo);
            }
            $validated['logo'] = $request->file('logo')->store('bank_logos', 'public');
        } else {
            // Keep existing logo if not uploading new one
            unset($validated['logo']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Reset user password
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(User $user)
    {
        $password = Str::random(12);
        $user->update(['password' => Hash::make($password)]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Password reset successfully. New password: {$password}");
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
