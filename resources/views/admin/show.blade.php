@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Application Details</h3>
  <div class="card mb-3">
    <div class="card-body">
      <p><strong>Applicant:</strong> {{ $application->user->name }} ({{ $application->user->email }})</p>
      <p><strong>Application #:</strong> {{ $application->application_number }}</p>
      <p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$application->status)) }}</p>
      <p><strong>Academic Year:</strong> {{ $application->academic_year }}</p>
      <p><strong>Form Type:</strong> {{ ucfirst($application->form_type) }}</p>
    </div>
  </div>

  @if($form)
  <div class="card mb-3">
    <div class="card-header">Personal Data</div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6"><strong>Full Name:</strong> {{ $form->full_name }}</div>
        <div class="col-md-6"><strong>DOB:</strong> {{ optional($form->dob)->toDateString() }}</div>
        <div class="col-md-6"><strong>Age:</strong> {{ $form->age }}</div>
        <div class="col-md-6"><strong>Gender:</strong> {{ $form->gender }}</div>
        <div class="col-md-6"><strong>Birth Place:</strong> {{ $form->birth_place }}</div>
        <div class="col-md-6"><strong>Marital Status:</strong> {{ $form->marital_status }}</div>
        <div class="col-md-6"><strong>Nationality:</strong> {{ $form->nationality }}</div>
        <div class="col-md-6"><strong>Passport #:</strong> {{ $form->passport_number }}</div>
        <div class="col-md-12"><strong>Address:</strong> {{ $form->mailing_address }}</div>
        <div class="col-md-6"><strong>Emergency Contact:</strong> {{ $form->emergency_contact }}</div>
        <div class="col-md-6"><strong>Telephone:</strong> {{ $form->telephone }}</div>
        <div class="col-md-6"><strong>Email:</strong> {{ $form->email }}</div>
        <div class="col-md-6"><strong>Hostel Required:</strong> {{ $form->hostel_required ? 'Yes' : 'No' }}</div>
        <div class="col-md-12"><strong>Disability:</strong> {{ $form->has_disability ? 'Yes' : 'No' }} - {{ $form->disability_details }}</div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Institutions Attended / Qualifications</div>
    <div class="card-body">
      @if(is_array($form->institutions))
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>Institution</th><th>Qualification</th><th>Date</th></tr></thead>
            <tbody>
              @foreach($form->institutions as $inst)
                <tr>
                  <td>{{ $inst['name'] ?? '' }}</td>
                  <td>{{ $inst['qualification'] ?? '' }}</td>
                  <td>{{ $inst['date'] ?? '' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <em>No institutions provided.</em>
      @endif
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Employment</div>
    <div class="card-body">
      @if(is_array($form->employment))
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>Company</th><th>Duration</th></tr></thead>
            <tbody>
              @foreach($form->employment as $emp)
                <tr>
                  <td>{{ $emp['company'] ?? '' }}</td>
                  <td>{{ $emp['duration'] ?? '' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <em>No employment records.</em>
      @endif
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Uploads</div>
    <div class="card-body">
      @php $u = is_array($form->uploads) ? $form->uploads : [] @endphp
      @foreach(['ghana_card_front','ghana_card_back','official_transcript','passport_picture'] as $key)
        @if(!empty($u[$key]))
          <div><strong>{{ ucwords(str_replace('_',' ', $key)) }}:</strong> <a href="{{ asset('storage/'.$u[$key]) }}" target="_blank">View</a></div>
        @endif
      @endforeach
      @if(!empty($u['other_academic_records']) && is_array($u['other_academic_records']))
        <div class="mt-2"><strong>Other Academic Records:</strong>
          <ul>
            @foreach($u['other_academic_records'] as $path)
              <li><a href="{{ asset('storage/'.$path) }}" target="_blank">View file</a></li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>
  @endif

  @if(isset($examRecords) && $examRecords->count())
  <div class="card mb-3">
    <div class="card-header">Examination Details</div>
    <div class="card-body">
      @foreach($examRecords as $rec)
        <div class="mb-3" style="border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
          <div class="row">
            <div class="col-md-3"><strong>Exam Type:</strong> {{ $rec->exam_type }}</div>
            <div class="col-md-3"><strong>Sitting Exam:</strong> {{ $rec->sitting_exam }}</div>
            <div class="col-md-2"><strong>Year:</strong> {{ $rec->year }}</div>
            <div class="col-md-4">
              <strong>Index Number:</strong> {{ $rec->index_number }}
              @php
                $best6Subjects = $rec->subjects->where('is_best_six', true)->take(6);
                $best6Total = $best6Subjects->sum('grade_number');
              @endphp
              @if($best6Total > 0)
                <span class="badge bg-success ms-2">Best 6: {{ $best6Total }}</span>
              @endif
            </div>
          </div>
          <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered">
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

  <div class="card">
    <div class="card-header">Update Status</div>
    <div class="card-body">
      <form method="post" action="{{ route('admin.applications.updateStatus', $application->id) }}">
        @csrf
        <div class="mb-3">
          <select class="form-select" name="status" required>
            <option value="successful" {{ $application->status==='successful' ? 'selected' : '' }}>Accept</option>
            <option value="not_successful" {{ $application->status==='not_successful' ? 'selected' : '' }}>Reject</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back</a>
      </form>
    </div>
  </div>
</div>
@endsection

