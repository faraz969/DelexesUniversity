@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $program->name }}</h4>
                    <div>
                        <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Program Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $program->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>{{ $program->department->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $program->description ?: 'No description provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>{{ $program->duration ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mode:</strong></td>
                                    <td>{{ $program->mode ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($program->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Sort Order:</strong></td>
                                    <td>{{ $program->sort_order }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cut Off Grade:</strong></td>
                                    <td>{{ $program->cut_off_grade ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $program->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Department Information</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h6>{{ $program->department->name }}</h6>
                                    <p class="text-muted">{{ $program->department->description ?: 'No description available' }}</p>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            Status: 
                                            @if($program->department->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </small>
                                        <a href="{{ route('admin.departments.show', $program->department) }}" class="btn btn-sm btn-outline-primary">
                                            View Department
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection