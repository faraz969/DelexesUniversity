@extends('layouts.app')

@section('title', 'Form Types - DELEXES UNIVERSITY COLLEGE')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Form Types Management
                    </h3>
                    <a href="{{ route('admin.form-types.create') }}" class="btn btn-delexes-primary">
                        <i class="fas fa-plus me-1"></i>Add New Form Type
                    </a>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($formTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Form Name</th>
                                        <th>Local Price</th>
                                        <th>International Price</th>
                                        <th>Conversion Rate</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formTypes as $index => $formType)
                                        <tr>
                                            <td>{{ $formTypes->firstItem() + $index }}</td>
                                            <td>
                                                <strong>{{ $formType->name }}</strong>
                                                @if($formType->description)
                                                    <br><small class="text-muted">{{ Str::limit($formType->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success">₵{{ number_format($formType->local_price, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">${{ number_format($formType->international_price, 2) }}</span>
                                            </td>
                                            <td>
                                                @if($formType->conversion_rate)
                                                    <span class="badge bg-warning">₵{{ number_format($formType->conversion_rate, 4) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($formType->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $formType->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.form-types.show', $formType) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.form-types.edit', $formType) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.form-types.destroy', $formType) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this form type?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $formTypes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Form Types Found</h5>
                            <p class="text-muted">Get started by creating your first form type.</p>
                            <a href="{{ route('admin.form-types.create') }}" class="btn btn-delexes-primary">
                                <i class="fas fa-plus me-1"></i>Add First Form Type
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection