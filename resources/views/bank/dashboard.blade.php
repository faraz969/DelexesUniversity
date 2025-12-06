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
            <h5 class="mb-0">Created Users ({{ $users->count() }})</h5>
            <a href="{{ route('bank.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create New Student
            </a>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
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
            @else
                <p class="text-muted">No users created yet. Click "Create New Student" to get started.</p>
            @endif
        </div>
    </div>
</div>
@endsection

