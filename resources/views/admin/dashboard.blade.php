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
                    <th>Qualification</th>
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
                        <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.applications.show', $app->id) }}">View</a>
                                <form action="{{ route('admin.applications.destroy', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this application? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No applications yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $applications->links() }}
</div>
@endsection

