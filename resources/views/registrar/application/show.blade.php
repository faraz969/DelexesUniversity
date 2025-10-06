@extends('layouts.app')

@php
    function safeDisplay($value) {
        if (is_array($value)) {
            return implode(', ', array_filter($value, function($v) { 
                return !is_array($v) && !is_object($v); 
            }));
        } elseif (is_object($value)) {
            return json_encode($value);
        } else {
            return $value ?? '-';
        }
    }
@endphp

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Application Review - {{ $application->application_number }}</h3>
                <a href="{{ route('registrar.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(isset($examRecords) && $examRecords->count())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Examination Details</h5>
                    </div>
                    <div class="card-body">
                        @foreach($examRecords as $rec)
                            <div class="mb-3" style="border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
                                <div class="row">
                                    <div class="col-md-3"><strong>Exam Type:</strong> {{ $rec->exam_type }}</div>
                                    <div class="col-md-3"><strong>Sitting Exam:</strong> {{ $rec->sitting_exam }}</div>
                                    <div class="col-md-2"><strong>Year:</strong> {{ $rec->year }}</div>
                                    <div class="col-md-4"><strong>Index Number:</strong> {{ $rec->index_number }}</div>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Grade (Letter)</th>
                                                <th>Grade (Number)</th>
                                                <th>Best 6</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rec->subjects as $row)
                                                <tr>
                                                    <td>{{ $row->subject }}</td>
                                                    <td>{{ $row->grade_letter }}</td>
                                                    <td>{{ $row->grade_number }}</td>
                                                    <td>{{ $row->is_best_six ? 'Yes' : 'No' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Application Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Application Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Applicant Name:</strong> {{ $application->user->name ?? '-' }}</p>
                            <p><strong>Email:</strong> {{ $application->user->email ?? '-' }}</p>
                            <p><strong>Application Number:</strong> {{ $application->application_number }}</p>
                            <p><strong>Academic Year:</strong> {{ safeDisplay($application->academic_year) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Form Type:</strong> {{ ucfirst(safeDisplay($application->form_type)) }}</p>
                            <p><strong>Applicant Type:</strong> {{ ucfirst(safeDisplay($application->applicant_type)) }}</p>
                            <p><strong>Primary Department:</strong> {{ $application->department->name ?? '-' }}</p>
                            @if($application->department_ids && is_array($application->department_ids) && count($application->department_ids) > 1)
                                <p><strong>All Departments:</strong> 
                                    @php
                                        $allDepartments = \App\Models\Department::whereIn('id', $application->department_ids)->pluck('name')->toArray();
                                    @endphp
                                    {{ implode(', ', $allDepartments) }}
                                </p>
                            @endif
                            <p><strong>Submitted:</strong> {{ $application->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Workflow Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>HOD Review</h6>
                            @if($application->hod_status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($application->hod_status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($application->hod_status) }}</span>
                            @endif
                            @if($application->hod_reviewed_at)
                                <br><small class="text-muted">{{ $application->hod_reviewed_at->format('M d, Y H:i') }}</small>
                            @endif
                            @if($application->hod_comments)
                                <div class="mt-2">
                                    <strong>HOD Comments:</strong>
                                    <p class="mb-0">{{ $application->hod_comments }}</p>
                                </div>
                            @endif
                        </div>
                       
                        <div class="col-md-4">
                            <h6>Registrar Review</h6>
                            @if($application->registrar_status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($application->registrar_status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($application->registrar_status) }}</span>
                            @endif
                            @if($application->registrar_reviewed_at)
                                <br><small class="text-muted">{{ $application->registrar_reviewed_at->format('M d, Y H:i') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Data -->
            @if($application->data && is_array($application->data))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Application Form Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($application->data as $key => $value)
                                @if($key !== '_files' && !empty($value))
                                    <div class="col-md-6 mb-3">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                        <p class="mb-0">{{ safeDisplay($value) }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @if(isset($examRecords) && $examRecords->count())
                            <hr>
                            <h5>Examination Details</h5>
                            @foreach($examRecords as $rec)
                                <div class="mb-3" style="border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
                                    <div class="row">
                                        <div class="col-md-3"><strong>Exam Type:</strong> {{ $rec->exam_type }}</div>
                                        <div class="col-md-3"><strong>Sitting Exam:</strong> {{ $rec->sitting_exam }}</div>
                                        <div class="col-md-2"><strong>Year:</strong> {{ $rec->year }}</div>
                                        <div class="col-md-4"><strong>Index Number:</strong> {{ $rec->index_number }}</div>
                                    </div>
                                    <div class="table-responsive mt-2">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Grade (Letter)</th>
                                                    <th>Grade (Number)</th>
                                                    <th>Best 6</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rec->subjects as $row)
                                                    <tr>
                                                        <td>{{ $row->subject }}</td>
                                                        <td>{{ $row->grade_letter }}</td>
                                                        <td>{{ $row->grade_number }}</td>
                                                        <td>{{ $row->is_best_six ? 'Yes' : 'No' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            <!-- Uploaded Files -->
            @if(isset($application->data['_files']) && is_array($application->data['_files']))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Uploaded Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($application->data['_files'] as $fieldName => $fileData)
                                @if(!empty($fileData))
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ ucfirst(str_replace('_', ' ', $fieldName)) }}</h6>
                                                <p class="card-text small text-muted">
                                                    @if(is_array($fileData))
                                                        {{ count($fileData) }} file(s)
                                                    @else
                                                        {{ basename($fileData) }}
                                                    @endif
                                                </p>
                                                @if(is_array($fileData))
                                                    @foreach($fileData as $file)
                                                        @if(isset($file['file']))
                                                            <a href="{{ asset('storage/' . $file['file']) }}" target="_blank" class="btn btn-sm btn-primary mb-1">View File</a><br>
                                                        @else
                                                            <a href="{{ asset('storage/' . $file) }}" target="_blank" class="btn btn-sm btn-primary mb-1">View File</a><br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <a href="{{ asset('storage/' . $fileData) }}" target="_blank" class="btn btn-sm btn-primary">View File</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Registrar Review Section -->
            @if($application->hod_status === 'approved' && $application->registrar_status === 'pending')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Registrar Review</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('registrar.applications.approve', $application->id) }}" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label for="approve_comments" class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="approve_comments" name="comments" rows="3" placeholder="Add any comments for approval..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Approve Application</button>
                        </form>

                        <form method="POST" action="{{ route('registrar.applications.reject', $application->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="reject_comments" class="form-label">Rejection Comments (Required)</label>
                                <textarea class="form-control" id="reject_comments" name="comments" rows="3" placeholder="Please provide reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">Reject Application</button>
                        </form>
                    </div>
                </div>
            @elseif($application->hod_status !== 'approved')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Review Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Cannot Review:</strong> This application has not been approved by HOD yet. 
                            You can only review applications that have been approved by the Head of Department.
                        </div>
                    </div>
                </div>
            @else
                <!-- Show Previous Review -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Previous Review</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    @if($application->registrar_status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($application->registrar_status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </p>
                                <p><strong>Reviewed At:</strong> {{ $application->registrar_reviewed_at ? $application->registrar_reviewed_at->format('M d, Y H:i') : '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($application->registrar_comments)
                                    <p><strong>Comments:</strong></p>
                                    <p>{{ $application->registrar_comments }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection