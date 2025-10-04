@extends('layouts.app')

@section('content')
<div class="container py-2">
    <div class="row">
        <div class="col-md-12">
            <h3>Dashboard</h3>
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Your Login Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>PIN (Password):</strong> 
                                <code class="fs-5 bg-light px-2 py-1 rounded">{{ Auth::user()->pin ?? 'N/A' }}</code>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Serial Number:</strong> 
                                <code class="fs-5 bg-light px-2 py-1 rounded">{{ Auth::user()->serial_number ?? 'N/A' }}</code>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>PIN Expires:</strong> 
                                <span class="badge {{ optional(Auth::user()->pin_expires_at)->isFuture() ? 'bg-success' : 'bg-danger' }}">
                                    {{ optional(Auth::user()->pin_expires_at)->toDayDateTimeString() ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @if(Auth::user()->serial_number)
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Login Options:</strong> You can login using either your email address or your serial number: <code>{{ Auth::user()->serial_number }}</code>
                        </div>
                    @endif
                    @if(Auth::user()->pin_expires_at && Auth::user()->pin_expires_at->isPast())
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Your PIN has expired!</strong> Please contact the administration to get a new PIN.
                        </div>
                    @elseif(Auth::user()->pin_expires_at && Auth::user()->pin_expires_at->diffInDays() <= 7)
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>PIN expires soon!</strong> Your PIN will expire in {{ Auth::user()->pin_expires_at->diffInDays() }} days.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ Auth::user()->phone ?? 'N/A' }}</p>
                            <p><strong>Nationality:</strong> {{ Auth::user()->nationality ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <p><strong>Form Type:</strong> 
                                @if(Auth::user()->formType)
                                    <span class="badge bg-primary">{{ Auth::user()->formType->name }}</span>
                                @else
                                    <span class="text-muted">No form type selected</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Application Number:</strong> {{ $application->application_number ?? 'N/A' }}</p>
                    <p><strong>Academic Year:</strong> {{ $application->academic_year ?? '2025/2026, September' }}</p>
                    
                   
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.application') }}" class="btn btn-primary">My Application</a>
                <a href="{{ route('portal.results') }}" class="btn btn-outline-secondary">Application Results</a>
            </div>
        </div>
    </div>
</div>
@endsection

