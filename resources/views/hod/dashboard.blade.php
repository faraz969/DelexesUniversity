@extends('layouts.app')

@section('content')
<div class="container">
    <h3>HOD Dashboard - {{ $department->name }}</h3>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

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
                                <th>Submitted</th>
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
                                    <td>{{ $app->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($app->department_ids && count($app->department_ids) > 1)
                                            <small class="text-muted">Multiple departments</small><br>
                                        @endif
                                        <a class="btn btn-sm btn-primary" href="{{ route('hod.applications.show', $app->id) }}">Review</a>
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
                                <th>HOD Status</th>
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
                                    <td>
                                        @if($app->hod_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($app->hod_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($app->hod_status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $app->hod_reviewed_at ? $app->hod_reviewed_at->format('M d, Y') : '-' }}</td>
                                    <td>
                                        @if($app->department_ids && count($app->department_ids) > 1)
                                            <small class="text-muted">Multiple departments</small><br>
                                        @endif
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hod.applications.show', $app->id) }}">View</a>
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