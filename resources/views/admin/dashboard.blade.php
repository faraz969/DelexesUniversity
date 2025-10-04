@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Admin - Applications</h3>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Application #</th>
                    <th>Academic Year</th>
                    <th>Form Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td>{{ $app->id }}</td>
                        <td>{{ $app->user->name ?? '-' }}</td>
                        <td>{{ $app->user->email ?? '-' }}</td>
                        <td>{{ $app->application_number }}</td>
                        <td>{{ $app->academic_year }}</td>
                        <td>{{ ucfirst($app->form_type) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.applications.show', $app->id) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No applications yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $applications->links() }}
</div>
@endsection

