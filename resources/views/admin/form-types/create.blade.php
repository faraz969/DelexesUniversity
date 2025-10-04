@extends('layouts.app')

@section('title', 'Create Form Type - DELEXES UNIVERSITY COLLEGE')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Create New Form Type
                    </h3>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.form-types.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Form Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., Undergraduate Admission Form" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="local_price" class="form-label">Local Price (₵) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('local_price') is-invalid @enderror" 
                                       id="local_price" name="local_price" value="{{ old('local_price') }}" 
                                       placeholder="0.00" required>
                                @error('local_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="international_price" class="form-label">International Price ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('international_price') is-invalid @enderror" 
                                       id="international_price" name="international_price" value="{{ old('international_price') }}" 
                                       placeholder="0.00" required>
                                @error('international_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="conversion_rate" class="form-label">Conversion Rate (₵ to $)</label>
                                <input type="number" step="0.0001" min="0" max="9999.9999"
                                       class="form-control @error('conversion_rate') is-invalid @enderror" 
                                       id="conversion_rate" name="conversion_rate" value="{{ old('conversion_rate') }}" 
                                       placeholder="e.g., 6.5000">
                                <small class="form-text text-muted">Exchange rate from Ghana Cedis to USD (optional)</small>
                                @error('conversion_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Optional description of this form type">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (Available for selection)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-delexes-primary">
                                <i class="fas fa-save me-1"></i>Create Form Type
                            </button>
                            <a href="{{ route('admin.form-types.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection