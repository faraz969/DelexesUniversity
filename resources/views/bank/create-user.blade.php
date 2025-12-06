@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Student</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('bank.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Payee Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional - Leave blank if not available</small>
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
                            <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality') }}" required placeholder="e.g., Ghana">
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="form_type_id" class="form-label">Form Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('form_type_id') is-invalid @enderror" 
                                    id="form_type_id" name="form_type_id" required>
                                <option value="">-- Select Form Type --</option>
                                @foreach($formTypes as $formType)
                                    @php
                                        $oldNationality = strtolower(trim(old('nationality', 'Ghana')));
                                        $isLocal = $oldNationality === 'ghana';
                                        $price = $isLocal ? $formType->local_price : $formType->international_price;
                                    @endphp
                                    <option value="{{ $formType->id }}" 
                                            data-local-price="{{ $formType->local_price }}" 
                                            data-international-price="{{ $formType->international_price }}"
                                            {{ old('form_type_id') == $formType->id ? 'selected' : '' }}>
                                        {{ $formType->name }} - 
                                        @if($isLocal)
                                            ₵{{ number_format($formType->local_price, 2) }}
                                        @else
                                            ${{ number_format($formType->international_price, 2) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('form_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="voucher_for" class="form-label">Voucher For</label>
                            <input type="text" class="form-control @error('voucher_for') is-invalid @enderror" 
                                   id="voucher_for" name="voucher_for" value="{{ old('voucher_for') }}" 
                                   placeholder="e.g., John Doe">
                            @error('voucher_for')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional - Name of person this voucher is for</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> A random PIN and Serial Number will be generated for this student. The receipt will be available for download after creation.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bank.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nationalityInput = document.getElementById('nationality');
    const formTypeSelect = document.getElementById('form_type_id');
    
    function updateFormTypePrices() {
        const nationality = nationalityInput.value.trim().toLowerCase();
        const isLocal = nationality === 'ghana';
        
        // Update all option texts
        formTypeSelect.querySelectorAll('option').forEach(option => {
            if (option.value === '') return;
            
            const formTypeName = option.textContent.split(' - ')[0];
            const localPrice = parseFloat(option.dataset.localPrice);
            const internationalPrice = parseFloat(option.dataset.internationalPrice);
            
            if (isLocal) {
                option.textContent = formTypeName + ' - ₵' + localPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                option.textContent = formTypeName + ' - $' + internationalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });
    }
    
    if (nationalityInput && formTypeSelect) {
        nationalityInput.addEventListener('input', updateFormTypePrices);
        nationalityInput.addEventListener('change', updateFormTypePrices);
    }
});
</script>
@endsection

