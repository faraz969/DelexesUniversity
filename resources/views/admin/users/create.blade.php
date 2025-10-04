@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Create New User</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}" required>
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
                                    <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Student-only fields -->
                        <div id="studentFields" style="display:none;">
                            <div class="mb-3">
                                <label for="nationality" class="form-label">Nationality</label>
                                <input type="text" class="form-control" id="nationality" name="nationality" value="{{ old('nationality') }}">
                            </div>

                            <div class="mb-3">
                                <label for="form_type_id" class="form-label">Form to Buy</label>
                                <select class="form-control" id="form_type_id" name="form_type_id">
                                    <option value="">-- Select Form --</option>
                                    @php $formTypes = \App\Models\FormType::active()->orderBy('name')->get(); @endphp
                                    @foreach($formTypes as $ft)
                                        <option value="{{ $ft->id }}" {{ old('form_type_id') == $ft->id ? 'selected' : '' }}>
                                            {{ $ft->name }} (Local: ₵{{ number_format($ft->local_price,2) }} / Intl: ${{ number_format($ft->international_price,2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" 
                                    id="department_id" name="department_id">
                                <option value="">-- Select Department (Optional) --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Required for HOD role, optional for others</div>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> A random PIN will be generated for this user. The PIN will be displayed after creation and saved in the user's PIN column.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create User</button>
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
    const studentFields = document.getElementById('studentFields');
    
    if (role === 'hod') {
        departmentSelect.required = true;
        departmentSelect.closest('.mb-3').querySelector('.form-text').textContent = 'Required for Head of Department';
    } else {
        departmentSelect.required = false;
        departmentSelect.closest('.mb-3').querySelector('.form-text').textContent = 'Required for HOD role, optional for others';
    }

    // Toggle student fields
    if (role === 'user') {
        studentFields.style.display = 'block';
    } else {
        studentFields.style.display = 'none';
    }
});

// Initialize on load
(function(){
    const role = document.getElementById('role').value;
    if (role === 'user') {
        document.getElementById('studentFields').style.display = 'block';
    }
})();
</script>
@endsection