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

    <!-- All Applications -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Applications</h5>
        </div>
        <div class="card-body">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
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
                                <td>{{ ucfirst($app->registrar_status ?? '-') }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('president.applications.show', $app->id) }}">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($applications, 'links'))
                {{ $applications->links() }}
            @endif
        </div>
    </div>
</div>
@endsection