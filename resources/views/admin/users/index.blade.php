@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>User Management</h4>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-10">
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Search by name, email, phone, PIN, serial number, role, department, bank name, or branch..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        @if(request('search'))
                            <div class="mt-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times"></i> Clear Search
                                </a>
                                <small class="text-muted ms-2">Searching for: "{{ request('search') }}" ({{ $users->total() }} result(s))</small>
                            </div>
                        @endif
                    </form>

                    @if($users->total() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>PIN</th>
                                        <th>Serial Number</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Created By</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone }}</td>
                                            <td><code>{{ $user->pin ?? '—' }}</code></td>
                                            <td><code>{{ $user->serial_number ?? '—' }}</code></td>
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
                                                    @case('bank')
                                                        <span class="badge bg-success">Bank</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">Student</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($user->department)
                                                    {{ $user->department->name }}
                                                @else
                                                    <span class="text-muted">No Department</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->creator)
                                                    @if($user->creator->isBank())
                                                        <span class="badge bg-success">
                                                            {{ $user->creator->bank_name ?? $user->creator->name }}
                                                            @if($user->creator->branch)
                                                                ({{ $user->creator->branch }})
                                                            @endif
                                                        </span>
                                                    @else
                                                        {{ $user->creator->name }}
                                                        <small class="text-muted d-block">{{ $user->creator->role_display }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Self-registered</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <!-- @if($user->id !== auth()->id())
                                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif -->
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $users->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            @if(request('search'))
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No users found matching your search criteria.
                                    <a href="{{ route('admin.users.index') }}" class="alert-link">Clear search</a> to see all users.
                                </div>
                            @else
                                <p class="text-muted">No users found.</p>
                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create First User</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection