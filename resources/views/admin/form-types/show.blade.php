@extends('layouts.app')

@section('title', 'View Form Type - DELEXES UNIVERSITY COLLEGE')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Form Type Details
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.form-types.edit', $formType) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('admin.form-types.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Form Name</label>
                            <p class="form-control-plaintext">{{ $formType->name }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                @if($formType->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Local Price</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-success fs-6">₵{{ number_format($formType->local_price, 2) }}</span>
                            </p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">International Price</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info fs-6">${{ number_format($formType->international_price, 2) }}</span>
                            </p>
                        </div>
                    </div>

                    @if($formType->conversion_rate)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Conversion Rate</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-warning fs-6">₵{{ number_format($formType->conversion_rate, 4) }} = $1.00</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($formType->description)
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">{{ $formType->description }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <p class="form-control-plaintext">{{ $formType->created_at->format('F d, Y \a\t g:i A') }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p class="form-control-plaintext">{{ $formType->updated_at->format('F d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection