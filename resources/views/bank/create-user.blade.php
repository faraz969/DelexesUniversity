@extends('layouts.app')

@section('head')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1e3a8a;
    }
</style>
@endsection

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
                            <select class="form-control @error('nationality') is-invalid @enderror" 
                                    id="nationality" name="nationality" required>
                                <option value="">-- Select Nationality --</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country['name'] }}" 
                                            data-flag="{{ $country['flag'] }}"
                                            {{ old('nationality', 'Ghana') === $country['name'] ? 'selected' : '' }}>
                                        {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>
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
                                            data-conversion-rate="{{ $formType->conversion_rate ?? 1 }}"
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

<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for nationality dropdown with search
    $('#nationality').select2({
        placeholder: '-- Select Nationality --',
        allowClear: false,
        width: '100%',
        theme: 'default',
        minimumResultsForSearch: 0, // Always show search box
        templateResult: formatCountry,
        templateSelection: formatCountrySelection,
        escapeMarkup: function(markup) {
            return markup;
        }
    });

    // Format country options with flags in dropdown
    function formatCountry(country) {
        if (country.loading) {
            return country.text;
        }
        
        // Get the flag from the option's data attribute
        var flag = $(country.element).data('flag') || '';
        var countryName = country.text || $(country.element).text();
        
        var $container = $(
            "<div class='select2-result-country' style='display: flex; align-items: center; padding: 5px 0;'>" +
                "<span style='font-size: 1.2em; margin-right: 8px;'>" + flag + "</span>" +
                "<span>" + countryName + "</span>" +
            "</div>"
        );
        
        return $container;
    }

    // Format selected country
    function formatCountrySelection(country) {
        // Get the flag from the option's data attribute
        var flag = $(country.element).data('flag') || '';
        var countryName = country.text || $(country.element).text();
        return flag + ' ' + countryName;
    }

    const formTypeSelect = document.getElementById('form_type_id');
    
    function updateFormTypePrices() {
        const nationality = $('#nationality').val() || '';
        const isLocal = nationality.toLowerCase().trim() === 'ghana';
        
        // Update all option texts
        formTypeSelect.querySelectorAll('option').forEach(option => {
            if (option.value === '') return;
            
            const formTypeName = option.textContent.split(' - ')[0];
            const localPrice = parseFloat(option.dataset.localPrice);
            const internationalPrice = parseFloat(option.dataset.internationalPrice);
            const conversionRate = parseFloat(option.dataset.conversionRate) || 1;
            
            if (isLocal) {
                option.textContent = formTypeName + ' - ₵' + localPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                // Calculate GHS equivalent
                const ghsEquivalent = internationalPrice * conversionRate;
                option.textContent = formTypeName + ' - $' + internationalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                    ' (₵' + ghsEquivalent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
            }
        });
    }
    
    // Update price when nationality changes
    $('#nationality').on('select2:select', function (e) {
        updateFormTypePrices();
    });
    
    // Initialize on page load
    updateFormTypePrices();
});
</script>
@endsection

