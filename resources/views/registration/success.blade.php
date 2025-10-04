@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-success">
                <h4 class="alert-heading">Registration Successful</h4>
                <p>Your PIN has been generated and sent to your phone via SMS. Please save it securely.</p>
                <hr>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</p>
                <p><strong>Nationality:</strong> {{ $nationality }}</p>
                <p><strong>PIN (Password):</strong> <code>{{ $pin }}</code></p>
                <p><strong>Serial Number:</strong> <code>{{ $serial_number }}</code></p>
                <p><strong>Expires:</strong> {{ $pin_expires_at->toDayDateTimeString() }}</p>
                <p><strong>Form Selected:</strong> {{ $form_type }}</p>
                <p><strong>Student Type:</strong> {{ ucfirst($student_type) }} Student 
                    <small class="text-muted">(Auto-determined from nationality: {{ $nationality }})</small>
                </p>
                <p><strong>Form Price:</strong> <span class="badge bg-success fs-6">{{ $currency }}{{ number_format($price, 2) }}</span></p>
        @if(isset($payment_amount) && isset($payment_currency))
            <p><strong>Amount Paid:</strong> <span class="badge bg-primary fs-6">{{ $payment_currency }}{{ number_format($payment_amount, 2) }}</span></p>
            <p><strong>Transaction ID:</strong> <code>{{ $invoice_id ?? 'N/A' }}</code></p>
        @endif
                <div class="alert alert-info mt-3">
                    <small><strong>Note:</strong> You should also receive this PIN via SMS on your registered phone number.</small>
                </div>
                <div class="alert alert-warning mt-2">
                    <small><strong>Login Options:</strong> You can login using either your email address or your serial number: <code>{{ $serial_number }}</code></small>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</div>
@endsection

