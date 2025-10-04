@extends('layouts.app')

@section('content')
<div class="container">
    <h3>President Dashboard</h3>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Review</h5>
                    <h3>{{ $stats['total_pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved Today</h5>
                    <h3>{{ $stats['approved_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Rejected Today</h5>
                    <h3>{{ $stats['rejected_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Reviewed</h5>
                    <h3>{{ $stats['total_reviewed'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Applications Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Pending Applications ({{ $pendingApplications->count() }})</h5>
        </div>
        <div class="card-body">
            @if($pendingApplications->count() > 0)
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
                                <th>Department</th>
                                <th>HOD Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApplications as $app)
                                <tr>
                                    <td>{{ $app->id }}</td>
                                    <td>{{ $app->user->name ?? '-' }}</td>
                                    <td>{{ $app->user->email ?? '-' }}</td>
                                    <td>{{ $app->application_number }}</td>
                                    <td>{{ $app->academic_year }}</td>
                                    <td>{{ ucfirst($app->form_type) }}</td>
                                    <td>{{ $app->department->name ?? '-' }}</td>
                                    <td>
                                        @if($app->hod_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($app->hod_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($app->hod_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="{{ route('president.applications.show', $app->id) }}">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No pending applications to review.</p>
            @endif
        </div>
    </div>

    <!-- Reviewed Applications Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Reviewed Applications ({{ $reviewedApplications->count() }})</h5>
        </div>
        <div class="card-body">
            @if($reviewedApplications->count() > 0)
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
                                <th>Department</th>
                                <th>President Status</th>
                                <th>Reviewed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviewedApplications as $app)
                                <tr>
                                    <td>{{ $app->id }}</td>
                                    <td>{{ $app->user->name ?? '-' }}</td>
                                    <td>{{ $app->user->email ?? '-' }}</td>
                                    <td>{{ $app->application_number }}</td>
                                    <td>{{ $app->academic_year }}</td>
                                    <td>{{ ucfirst($app->form_type) }}</td>
                                    <td>{{ $app->department->name ?? '-' }}</td>
                                    <td>
                                        @if($app->president_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($app->president_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($app->president_status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $app->president_reviewed_at ? $app->president_reviewed_at->format('M d, Y') : '-' }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('president.applications.show', $app->id) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No reviewed applications yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection