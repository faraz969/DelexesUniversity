@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Programs Management</h4>
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Program
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($programs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Department</th>
                                        <th>Duration</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Sort Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programs as $program)
                                        <tr>
                                            <td>{{ $program->name }}</td>
                                            <td>{{ $program->department->name }}</td>
                                            <td>{{ $program->duration ?: 'N/A' }}</td>
                                            <td>{{ $program->mode ?: 'N/A' }}</td>
                                            <td>
                                                @if($program->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $program->sort_order }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.programs.destroy', $program) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this program?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No programs found.</p>
                            <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">Create First Program</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection