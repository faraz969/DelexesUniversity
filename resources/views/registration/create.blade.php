@extends('layouts.app')

@section('title', 'Registration - DELEXES UNIVERSITY COLLEGE')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 45px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 43px;
        padding-left: 0;
        color: #374151;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px;
        right: 8px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 12px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6;
    }
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    /* Ensure flag and country name appear on one line in results */
    .select2-result-country {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .select2-result-country__flag,
    .select2-result-country__name {
        line-height: 1;
        display: inline-block;
    }
    /* Ensure the selected value (rendered) shows flag and name inline */
    #nationality + .select2 .select2-selection__rendered {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <!-- Background Section (70% width) -->
    <div class="auth-background">
    <div class="background-overlay">
            
            </div>
    </div>

    <!-- Form Section (30% width) -->
    <div class="auth-form-section">
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Registration</h2>
                <p class="form-subtitle">Buy Application Form - Generate your PIN to get started.</p>
            </div>

            <form method="post" action="{{ route('registration.store') }}" class="auth-form" novalidate>
                @csrf

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-4">
                            <select id="country_code" name="country_code" class="form-control" required>
                                @foreach($countries as $country)
                                    <option value="{{ $country['code'] }}" 
                                            {{ $country['code'] === '+233' ? 'selected' : '' }}>
                                        {{ $country['flag'] }} {{ $country['code'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="e.g., 55 123 4567" required>
                        </div>
                    </div>
                    <small class="form-text text-muted">Enter your phone number without the country code</small>
                </div>

                <div class="form-group">
                    <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                    <select id="nationality" name="nationality" class="form-control" required>
                        <option value="">-- Select Your Nationality --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country['name'] }}" 
                                    data-flag="{{ $country['flag'] }}"
                                    {{ $country['name'] === 'Ghana' ? 'selected' : '' }}>
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="form_type" class="form-label">Form you want to buy <span class="text-danger">*</span></label>
                    <select id="form_type" name="form_type" class="form-control" required>
                        <option value="">-- Select Form Type --</option>
                        @foreach($formTypes as $formType)
                            <option value="{{ $formType->id }}" 
                                    data-local-price="{{ $formType->local_price }}" 
                                    data-international-price="{{ $formType->international_price }}"
                                    data-conversion-rate="{{ $formType->conversion_rate }}">
                                {{ $formType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div id="price-display" class="form-group" style="display: none;">
                    <div class="alert alert-info">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Form Price</h6>
                        <div id="price-details">
                            <div id="price-amount"></div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-lightbulb me-1"></i>
                                Student type automatically determined from nationality
                            </small>
                        </div>
                    </div>
                </div>

                <div id="payment-mode-section" class="form-group" style="display: none;">
                    <label class="form-label">Select Payment Method <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <input type="checkbox" id="payment_ecobank" class="payment-radio" />
                            <label for="payment_ecobank" class="payment-label w-100">
                                <i class="fas fa-university"></i> Eco Bank
                            </label>
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="checkbox" id="payment_gcb" class="payment-radio" />
                            <label for="payment_gcb" class="payment-label w-100">
                                <i class="fas fa-university"></i> GCB Bank
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="payment_mode" id="payment_mode" value="" />
                </div>


                <div class="form-group">
                    <button type="submit" class="btn btn-delexes-primary btn-block" id="submit-btn">
                        Generate PIN
                    </button>
                </div>

                <div class="form-footer">
                    <p class="signup-link">
                        <a href="{{ url('/') }}" class="signup-link-text">← Back to Home</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    min-height: calc(100vh - 76px);
    margin: 0;
    padding: 0;
}

.auth-background {
    flex: 0 0 70%;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
    background-image: url('{{ asset("images/background.jpg") }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(30, 58, 138, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-content {
    text-align: center;
    color: white;
    padding: 2rem;
    max-width: 600px;
}

.university-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.university-subtitle {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.university-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.1rem;
}

.feature-item i {
    font-size: 1.5rem;
    color: #3b82f6;
}

.auth-form-section {
    flex: 0 0 30%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.form-container {
    width: 100%;
    max-width: 400px;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-title {
    font-size: 2rem;
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #6b7280;
    font-size: 0.9rem;
}

.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-delexes-primary {
    width: 100%;
    padding: 0.75rem;
    background-color: #1e3a8a;
    border: none;
    border-radius: 0.5rem;
    color: white;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.btn-delexes-primary:hover {
    background-color: #1e40af;
}

.form-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.signup-link {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0;
}

.signup-link-text {
    color: #1e3a8a;
    text-decoration: none;
    font-weight: 500;
}

.signup-link-text:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .auth-container {
        flex-direction: column;
    }
    
    .auth-background {
        flex: 0 0 40%;
        min-height: 300px;
    }
    
    .auth-form-section {
        flex: 0 0 60%;
    }
    
    .university-title {
        font-size: 2rem;
    }
    
    .university-subtitle {
        font-size: 1rem;
    }
    
    .feature-item {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .auth-form-section {
        padding: 1rem;
    }
    
    .form-container {
        max-width: 100%;
    }
    
    .university-title {
        font-size: 1.5rem;
    }
    
    .university-features {
        gap: 0.5rem;
    }
    
    .feature-item {
        font-size: 0.8rem;
    }
}

/* Phone number input styling */
.form-group .row {
    margin: 0;
}

.form-group .row .col-md-4,
.form-group .row .col-md-8 {
    padding: 0;
}

.form-group .row .col-md-4 {
    padding-right: 0.5rem;
}

.form-group .row .col-md-8 {
    padding-left: 0.5rem;
}

#country_code {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

#phone {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

#country_code:focus,
#phone:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-text {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

/* Nationality dropdown styling */
#nationality {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

#nationality:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Payment options styling */
.payment-option {
    position: relative;
}

.payment-radio {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.payment-label {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
}

.payment-label:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}

.payment-radio:checked + .payment-label {
    border-color: #3b82f6;
    background-color: #eff6ff;
    color: #1e40af;
}

.payment-label i {
    margin-right: 8px;
    font-size: 1.2em;
}

#payment-mode-section {
    margin-top: 20px;
    padding: 20px;
    background-color: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

#payment-mode-section label.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formTypeSelect = document.getElementById('form_type');
    const nationalitySelect = document.getElementById('nationality');
    const priceDisplay = document.getElementById('price-display');
    const priceAmount = document.getElementById('price-amount');

    // Expose globally so other scripts (e.g., Select2 handlers) can call it
    window.updatePrice = function() {
        const selectedFormType = formTypeSelect.options[formTypeSelect.selectedIndex];
        const selectedNationality = nationalitySelect.value;

        if (selectedFormType.value && selectedNationality) {
            const localPrice = selectedFormType.dataset.localPrice;
            const internationalPrice = selectedFormType.dataset.internationalPrice;
            const conversionRate = selectedFormType.dataset.conversionRate;
            
            let price, currency, studentType, convertedPrice = null;
            
            // Determine student type based on nationality
            if (selectedNationality === 'Ghana') {
                price = localPrice;
                currency = '₵';
                studentType = 'Local Student';
            } else {
                price = internationalPrice;
                currency = '$';
                studentType = 'International Student';
                
                // Calculate converted price if conversion rate is available
                if (conversionRate && parseFloat(conversionRate) > 0) {
                    convertedPrice = parseFloat(price) * parseFloat(conversionRate);
                }
            }

            let priceHtml = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">${currency}${parseFloat(price).toFixed(2)}</span>
                    <span class="badge ${studentType === 'Local Student' ? 'bg-success' : 'bg-primary'}">${studentType}</span>
                </div>
            `;
            
            // Add converted price for international students
            if (convertedPrice) {
                priceHtml += `
                    <div class="mt-2 p-2 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-exchange-alt me-1"></i>
                            Equivalent: <strong>₵${convertedPrice.toFixed(2)}</strong>
                            <br>
                            <small>(Rate: $1 = ₵${parseFloat(conversionRate).toFixed(4)})</small>
                        </small>
                    </div>
                `;
            }

            priceAmount.innerHTML = priceHtml;
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
        
        // Show/hide payment mode section
        showPaymentMode();
    }

    formTypeSelect.addEventListener('change', window.updatePrice);
    nationalitySelect.addEventListener('change', window.updatePrice);

    // Show payment mode section when price is displayed
    function showPaymentMode() {
        const priceDisplay = document.getElementById('price-display');
        const paymentModeSection = document.getElementById('payment-mode-section');
        
        if (priceDisplay.style.display === 'block') {
            paymentModeSection.style.display = 'block';
        } else {
            paymentModeSection.style.display = 'none';
        }
    }

    // Update payment mode visibility when price changes
    formTypeSelect.addEventListener('change', showPaymentMode);
    nationalitySelect.addEventListener('change', showPaymentMode);

    // Payment method selection logic (single-select behavior using checkboxes)
    const paymentEcobank = document.getElementById('payment_ecobank');
    const paymentGcb = document.getElementById('payment_gcb');
    const paymentModeInput = document.getElementById('payment_mode');

    function updatePaymentMode() {
        if (paymentEcobank && paymentGcb) {
            if (paymentEcobank.checked && paymentGcb.checked) {
                // If both are checked, uncheck the one not just changed last; default to the last clicked
                // This handler will be bound to both, so keep only the current target checked
            }

            if (paymentEcobank.checked && !paymentGcb.checked) {
                paymentModeInput.value = 'ecobank';
            } else if (!paymentEcobank.checked && paymentGcb.checked) {
                paymentModeInput.value = 'gcb';
            } else {
                paymentModeInput.value = '';
            }
        }
    }

    function onPaymentClick(e) {
        if (e.target === paymentEcobank) {
            if (paymentEcobank.checked) paymentGcb.checked = false;
        } else if (e.target === paymentGcb) {
            if (paymentGcb.checked) paymentEcobank.checked = false;
        }
        updatePaymentMode();
    }

    if (paymentEcobank) paymentEcobank.addEventListener('change', onPaymentClick);
    if (paymentGcb) paymentGcb.addEventListener('change', onPaymentClick);

    // Handle form submission with payment
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submit-btn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        const formData = new FormData(form);
        const requiredFields = ['full_name', 'email', 'country_code', 'phone', 'nationality', 'form_type', 'payment_mode'];
        
        for (let field of requiredFields) {
            if (!formData.get(field)) {
                alert(`Please fill in the ${field.replace('_', ' ')} field.`);
                return;
            }
        }
        
        // Require payment mode selection
        if (!formData.get('payment_mode')) {
            alert('Please select a payment method (Eco Bank or GCB Bank).');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
        
        // Prepare form data
        const requestData = {
            full_name: formData.get('full_name'),
            email: formData.get('email'),
            country_code: formData.get('country_code'),
            phone: formData.get('phone'),
            nationality: formData.get('nationality'),
            form_type: formData.get('form_type'),
            payment_mode: formData.get('payment_mode')
        };

        console.log('Sending payment request:', requestData);

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('CSRF Token:', csrfToken);

        // Submit form data to payment controller
        fetch('{{ route("payment.initiate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Redirect to payment gateway
                window.location.href = data.payment_url;
            } else {
                let errorMessage = data.message || 'Payment initiation failed. Please try again.';
                
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat().join(', ');
                    errorMessage += '\n\nValidation errors: ' + errorList;
                }
                
                if (data.gateway_response) {
                    console.error('Gateway response:', data.gateway_response);
                }
                
                alert(errorMessage);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Generate PIN';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Network error: ' + error.message + '\n\nPlease check your connection and try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Generate PIN';
        });
    });

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    const countryCodeSelect = document.getElementById('country_code');

    phoneInput.addEventListener('input', function(e) {
        // Remove any non-numeric characters except spaces
        let value = e.target.value.replace(/[^\d\s]/g, '');
        
        // Format the number based on country code
        const countryCode = countryCodeSelect.value;
        
        if (countryCode === '+233') { // Ghana
            // Format as XXX XXX XXXX
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        } else if (countryCode === '+1') { // US/Canada
            // Format as XXX XXX XXXX
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        } else if (countryCode === '+44') { // UK
            // Format as XXXX XXX XXXX
            value = value.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
        } else {
            // Default formatting - remove extra spaces
            value = value.replace(/\s+/g, ' ').trim();
        }
        
        e.target.value = value;
    });

    // Update placeholder based on country code
    countryCodeSelect.addEventListener('change', function() {
        const countryCode = this.value;
        const phoneInput = document.getElementById('phone');
        
        switch(countryCode) {
            case '+233': // Ghana
                phoneInput.placeholder = 'e.g., 55 123 4567';
                break;
            case '+1': // US/Canada
                phoneInput.placeholder = 'e.g., 555 123 4567';
                break;
            case '+44': // UK
                phoneInput.placeholder = 'e.g., 7700 123456';
                break;
            case '+234': // Nigeria
                phoneInput.placeholder = 'e.g., 803 123 4567';
                break;
            case '+254': // Kenya
                phoneInput.placeholder = 'e.g., 700 123456';
                break;
            default:
                phoneInput.placeholder = 'Enter phone number';
        }
    });
});
</script>

<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for nationality dropdown
    $('#nationality').select2({
        placeholder: '-- Select Your Nationality --',
        allowClear: true,
        width: '100%',
        templateResult: formatCountry,
        templateSelection: formatCountrySelection,
        escapeMarkup: function(markup) {
            return markup;
        }
    });

    // Format country options with flags
    function formatCountry(country) {
        if (country.loading) {
            return country.text;
        }
        
        // Get the flag from the option's data attribute
        var flag = $(country.element).data('flag') || '';
        
        var $container = $(
            "<div class='select2-result-country clearfix'>" +
                "<div class='select2-result-country__flag'></div>" +
                "<div class='select2-result-country__name'></div>" +
            "</div>"
        );
        
        $container.find('.select2-result-country__flag').text(flag);
        $container.find('.select2-result-country__name').text(country.text);
        
        return $container;
    }

    // Format selected country
    function formatCountrySelection(country) {
        // Get the flag from the option's data attribute
        var flag = $(country.element).data('flag') || '';
        return flag + ' ' + country.text;
    }

    // Update price when nationality changes
    $('#nationality').on('select2:select', function (e) {
        updatePrice();
    });
});
</script>
@endsection

