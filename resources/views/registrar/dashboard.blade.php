@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Registrar Dashboard</h3>
    
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
                    <h5 class="card-title">Total Applications</h5>
                    <h3>{{ $stats['total_applications'] }}</h3>
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
                                <th>Qualification</th>
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
                                    <td>
                                        @php
                                            $qualifiedPrograms = $app->getQualifiedPrograms();
                                        @endphp
                                        @if($qualifiedPrograms->isNotEmpty())
                                            <span class="badge bg-success">Qualified</span>
                                            <div class="mt-1">
                                                @foreach($qualifiedPrograms as $program)
                                                    <small class="d-block text-muted">{{ $program->name }}</small>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge bg-danger">Unqualified</span>
                                        @endif
                                    </td>
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
                                        <a class="btn btn-sm btn-primary" href="{{ route('registrar.applications.show', $app->id) }}">Review</a>
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
                                <th>Qualification</th>
                                <th>Department</th>
                                <th>Registrar Status</th>
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
                                    <td>
                                        @php
                                            $qualifiedPrograms = $app->getQualifiedPrograms();
                                        @endphp
                                        @if($qualifiedPrograms->isNotEmpty())
                                            <span class="badge bg-success">Qualified</span>
                                            <div class="mt-1">
                                                @foreach($qualifiedPrograms as $program)
                                                    <small class="d-block text-muted">{{ $program->name }}</small>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge bg-danger">Unqualified</span>
                                        @endif
                                    </td>
                                    <td>{{ $app->department->name ?? '-' }}</td>
                                    <td>
                                        @if($app->registrar_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($app->registrar_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($app->registrar_status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $app->registrar_reviewed_at ? $app->registrar_reviewed_at->format('M d, Y') : '-' }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('registrar.applications.show', $app->id) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- All Applications Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Applications Overview ({{ $allApplications->count() }})</h5>
        </div>
        <div class="card-body">
            @if($allApplications->count() > 0)
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
                                <th>Qualification</th>
                                <th>Department</th>
                                <th>HOD Status</th>
                                <th>Registrar Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allApplications as $app)
                                <tr>
                                    <td>{{ $app->id }}</td>
                                    <td>{{ $app->user->name ?? '-' }}</td>
                                    <td>{{ $app->user->email ?? '-' }}</td>
                                    <td>{{ $app->application_number }}</td>
                                    <td>{{ $app->academic_year }}</td>
                                    <td>{{ ucfirst($app->form_type) }}</td>
                                    <td>
                                        @php
                                            $qualifiedPrograms = $app->getQualifiedPrograms();
                                        @endphp
                                        @if($qualifiedPrograms->isNotEmpty())
                                            <span class="badge bg-success">Qualified</span>
                                            <div class="mt-1">
                                                @foreach($qualifiedPrograms as $program)
                                                    <small class="d-block text-muted">{{ $program->name }}</small>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge bg-danger">Unqualified</span>
                                        @endif
                                    </td>
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
                                        @if($app->registrar_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($app->registrar_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($app->registrar_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('registrar.applications.show', $app->id) }}">View</a>
                                        @if($app->hod_status === 'approved' && $app->registrar_status === 'pending')
                                            <span class="badge bg-info ms-1">Can Review</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No applications found.</p>
            @endif
        </div>
    </div>
</div>
@endsection