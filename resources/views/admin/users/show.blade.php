@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $user->name }}</h4>
                    <div>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $user->phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        @switch($user->role)
                                            @case('admin')
                                                <span class="badge bg-danger">Administrator</span>
                                                @break
                                            @case('hod')
                                                <span class="badge bg-warning">Head of Department</span>
                                                @break
                                            @case('registrar')
                                                <span class="badge bg-info">Registrar</span>
                                                @break
                                            @case('president')
                                                <span class="badge bg-primary">President</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">Student</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>
                                        @if($user->department)
                                            {{ $user->department->name }}
                                        @else
                                            <span class="text-muted">No Department Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit User
                                </a>
                                
                                <form action="{{ route('admin.users.resetPassword', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100" onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                                        <i class="fas fa-key"></i> Reset Password
                                    </button>
                                </form>
                                
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete User
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="fas fa-ban"></i> Cannot Delete Own Account
                                    </button>
                                @endif
                            </div>

                            @if($user->department)
                                <div class="mt-4">
                                    <h6>Department Information</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>{{ $user->department->name }}</h6>
                                            <p class="text-muted">{{ $user->department->description ?: 'No description available' }}</p>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    Status: 
                                                    @if($user->department->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </small>
                                                <a href="{{ route('admin.departments.show', $user->department) }}" class="btn btn-sm btn-outline-primary">
                                                    View Department
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection