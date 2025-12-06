@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Bank Dashboard - {{ $bankUser->bank_name }} ({{ $bankUser->branch }})</h3>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Created Users ({{ $users->total() }})</h5>
            <a href="{{ route('bank.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create New Student
            </a>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="{{ route('bank.dashboard') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Search by name, email, or phone..." 
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
                        <a href="{{ route('bank.dashboard') }}" class="btn btn-sm btn-secondary">
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
                                <th>Serial Number</th>
                                <th>PIN</th>
                                <th>Form Type</th>
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
                                    <td><code>{{ $user->serial_number ?? '—' }}</code></td>
                                    <td><code>{{ $user->pin ?? '—' }}</code></td>
                                    <td>{{ $user->formType->name ?? '—' }}</td>
                                    <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('bank.users.receipt', $user->id) }}" class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Download Receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @else
                @if(request('search'))
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No users found matching your search criteria. 
                        <a href="{{ route('bank.dashboard') }}" class="alert-link">Clear search</a> to see all users.
                    </div>
                @else
                    <p class="text-muted">No users created yet. Click "Create New Student" to get started.</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

