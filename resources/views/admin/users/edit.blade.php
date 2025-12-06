@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit User</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" 
                                    id="department_id" name="department_id">
                                <option value="">-- Select Department (Optional) --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Required for HOD role, optional for others</div>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bank-only fields -->
                        <div id="bankFields" style="display:none;">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('branch') is-invalid @enderror" 
                                       id="branch" name="branch" value="{{ old('branch', $user->branch) }}">
                                @error('branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label">Logo</label>
                                @if($user->logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $user->logo) }}" alt="Bank Logo" style="max-height: 100px; max-width: 200px;" class="img-thumbnail">
                                        <br><small class="text-muted">Current logo</small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB. Leave empty to keep current logo.</small>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const departmentSelect = document.getElementById('department_id');
    const role = this.value;
    const bankFields = document.getElementById('bankFields');
    const bankNameInput = document.getElementById('bank_name');
    const branchInput = document.getElementById('branch');
    
    if (role === 'hod') {
        departmentSelect.required = true;
        departmentSelect.closest('.mb-3').querySelector('.form-text').textContent = 'Required for Head of Department';
    } else {
        departmentSelect.required = false;
        departmentSelect.closest('.mb-3').querySelector('.form-text').textContent = 'Required for HOD role, optional for others';
    }

    // Toggle bank fields
    if (role === 'bank') {
        bankFields.style.display = 'block';
        if (bankNameInput) bankNameInput.required = true;
        if (branchInput) branchInput.required = true;
    } else {
        bankFields.style.display = 'none';
        if (bankNameInput) bankNameInput.required = false;
        if (branchInput) branchInput.required = false;
    }
});

// Set initial state
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const departmentSelect = document.getElementById('department_id');
    const role = roleSelect.value;
    const bankFields = document.getElementById('bankFields');
    const bankNameInput = document.getElementById('bank_name');
    const branchInput = document.getElementById('branch');
    
    if (role === 'hod') {
        departmentSelect.required = true;
        departmentSelect.closest('.mb-3').querySelector('.form-text').textContent = 'Required for Head of Department';
    }

    if (role === 'bank') {
        bankFields.style.display = 'block';
        if (bankNameInput) bankNameInput.required = true;
        if (branchInput) branchInput.required = true;
    }
});
</script>
@endsection