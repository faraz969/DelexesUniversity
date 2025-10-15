@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  window.prefillExamSections = @json($prefill['exam_sections'] ?? []);
  window.prefillData = @json($prefill ?? []);
  window.isSubmittedView = @json(!empty($submitted));
  // Note: in submitted view we don't render the editable form
</script>
<style>
    :root { --gap: 14px; }
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif;  line-height: 1.4; }
    h1 { text-align: center; margin: 0 0 16px; }
    fieldset { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 18px; }
    legend { padding: 0 6px; font-weight: bold; }
    label { display: block; margin: 10px 0 4px; font-weight: 600; }
    input, select, textarea, button { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font: inherit; }
    .row { display: grid; grid-template-columns: 1fr; gap: var(--gap); }
    @media (min-width: 720px) { .row.two { grid-template-columns: repeat(2, 1fr); } .row.three { grid-template-columns: repeat(3, 1fr); } }
  @media (min-width: 960px) { .row.four { grid-template-columns: repeat(4, 1fr); } }
    .inline-options { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; }
    .inline-options label { margin: 0; font-weight: 500; }
    .hint { font-size: 12px; color: #666; margin-top: 6px; }
    .actions { display: flex; gap: 10px; }
    .btn-primary { background: #1a73e8; color: #fff; border: none; cursor: pointer; }
    .btn-secondary { background: #f5f5f5; cursor: pointer; }
    .btn-link { background: transparent; border: 1px solid #ddd; padding: 8px 10px; border-radius: 8px; cursor: pointer; }
    .preview-box { margin-top: 8px; border: 1px dashed #ccc; border-radius: 8px; padding: 10px; display: none; }
    .preview-box.active { display: block; }
    .preview-box img { max-width: 100%; height: auto; border-radius: 6px; }
    .file-row { display: grid; gap: 10px; }
    @media (min-width: 720px) { .file-row.two { grid-template-columns: 1fr auto; align-items: end; } .file-row.three { grid-template-columns: 1fr 1fr auto; align-items: end; } }
    .file-names { font-size: 13px; color: #444; margin-top: 6px; }
    .inst-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .inst-table th, .inst-table td { border: 1px solid #e5e5e5; padding: 8px; vertical-align: top; }
    .inst-table th { background: #fafafa; text-align: left; font-weight: 600; }
    .inst-row-actions { display: flex; gap: 8px; }

    /* Tab Styles */
    .tab-container { margin-bottom: 20px; }
    /* Sidebar layout */
    .app-form-layout { display: flex; gap: 16px; }
    .side-nav { width: 400px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; height: fit-content; }
    .side-nav-header { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; font-weight: 600; }
    .side-nav-list { list-style: none; margin: 0; padding: 8px; }
    .side-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 8px; cursor: pointer; }
    .side-item:hover { background: #f5f7ff; }
    .side-item.active { background: #eef4ff; border: 1px solid #c7d2fe; }
    .side-item .left { display:flex; align-items:center; gap:10px; }
    .status-badge { font-size: 12px; padding: 4px 8px; border-radius: 999px; }
    .status-completed { background:#d1fae5; color:#065f46; }
    .status-pending { background:#ffedd5; color:#9a3412; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .tab-navigation-buttons { 
        display: flex; 
        justify-content: space-between; 
        gap: 10px;
        margin-top: 20px; 
        padding: 12px 0; 
        border-top: 1px solid #e0e0e0; 
        position: sticky; 
        bottom: 0; 
        background: #fff; 
        z-index: 5; 
    }
    .tab-nav-btn { 
        padding: 10px 20px; 
        border: 1px solid #ddd; 
        border-radius: 6px; 
        background: white; 
        cursor: pointer; 
        transition: all 0.3s ease;
    }
    .tab-nav-btn:hover { background-color: #f5f5f5; }
    .tab-nav-btn:disabled { 
        opacity: 0.5; 
        cursor: not-allowed; 
        background-color: #f9f9f9; 
    }
    .tab-nav-btn.btn-primary { 
        background: #1a73e8; 
        color: white; 
        border-color: #1a73e8; 
    }
    .tab-nav-btn.btn-primary:hover { 
        background: #1557b0; 
        border-color: #1557b0; 
    }
    .tab-nav-btn.btn-success { 
        background: #28a745; 
        color: white; 
        border-color: #28a745; 
    }
    .tab-nav-btn.btn-success:hover { 
        background: #218838; 
        border-color: #218838; 
    }
    .progress-indicator { 
        text-align: center; 
        margin-bottom: 20px; 
        color: #666; 
        font-size: 14px; 
    }
</style>

<div class="container py-2">
  <h1>Delexes University College <br/>Undergraduate Admission Form</h1>

  @if(!empty($submitted))
    <div class="alert alert-success">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span>Application submitted. Below is your application summary.</span>
        <button type="button" class="btn btn-primary" onclick="printApplication()" style="background: #1a73e8; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; width:auto;">
          <i class="fas fa-print"></i> Print Application
        </button>
      </div>
    </div>
  @endif

  @if(empty($submitted))
  <form action="{{ $action ?? '#' }}" method="post" enctype="multipart/form-data" novalidate id="applicationForm">
    @csrf
    
    <!-- Progress Indicator -->
    <div class="progress-indicator">
      Step <span id="currentStep">1</span> of <span id="totalSteps">5</span>
      <span id="draftStatus" style="margin-left:10px; color:#16a34a; display:none;">Saved</span>
    </div>

    <div class="app-form-layout">
      <!-- Left Side Navigation -->
      <div class="side-nav">
        <div class="side-nav-header">Application Information</div>
        <ul class="side-nav-list" id="sideNavList">
          <li class="side-item active" data-tab="personal"><div class="left"><i class="fas fa-user me-2"></i><span>Personal Data</span></div><span class="status-badge status-pending">Pending</span></li>
          <li class="side-item" data-tab="education"><div class="left"><i class="fas fa-graduation-cap me-2"></i><span>Education</span></div><span class="status-badge status-pending">Pending</span></li>
          <li class="side-item" data-tab="programs"><div class="left"><i class="fas fa-book me-2"></i><span>Programs</span></div><span class="status-badge status-pending">Pending</span></li>
          <li class="side-item" data-tab="employment"><div class="left"><i class="fas fa-briefcase me-2"></i><span>Employment</span></div><span class="status-badge status-pending">Pending</span></li>
          <li class="side-item" data-tab="documents"><div class="left"><i class="fas fa-file-alt me-2"></i><span>Documents</span></div><span class="status-badge status-pending">Pending</span></li>
        </ul>
      </div>

      <!-- Right Content -->
      <div class="tab-container" style="flex:1;">

      <!-- Tab 1: Personal Data -->
      <div class="tab-content active" id="personal">
    <fieldset>
      <legend>Personal Data</legend>
      <div class="row two">
        <script>
          // Sync hidden full_name from split fields
          document.addEventListener('DOMContentLoaded', function(){
            const surname = document.getElementById('surname');
            const middle = document.getElementById('middle_name');
            const other = document.getElementById('other_name');
            const full = document.getElementById('full_name');
            function syncFull(){
              if (!full) return;
              const parts = [other && other.value ? other.value.trim() : '', middle && middle.value ? middle.value.trim() : '', surname && surname.value ? surname.value.trim() : ''].filter(Boolean);
              full.value = parts.join(' ').trim();
            }
            if (surname) surname.addEventListener('input', () => { syncFull(); autosaveDraft(); });
            if (middle) middle.addEventListener('input', () => { syncFull(); autosaveDraft(); });
            if (other) other.addEventListener('input', () => { syncFull(); autosaveDraft(); });
            syncFull();
          });
        </script>
        @php
          $prefillFullName = trim($prefill['full_name'] ?? '');
          $prefillSurname = '';
          $prefillMiddle = '';
          $prefillOther = '';
          if ($prefillFullName !== '') {
            $parts = preg_split('/\s+/', $prefillFullName);
            if (count($parts) === 1) {
              $prefillOther = $parts[0];
            } elseif (count($parts) === 2) {
              $prefillOther = $parts[0];
              $prefillSurname = $parts[1];
            } else {
              $prefillOther = array_shift($parts);
              $prefillSurname = array_pop($parts);
              $prefillMiddle = implode(' ', $parts);
            }
          }
        @endphp
        <div>
          <label for="surname">Surname <span style="color:red">*</span></label>
          <input id="surname" name="surname" type="text" value="{{ $prefillSurname }}" required />
        </div>
        <div>
          <label for="middle_name">Middle Name</label>
          <input id="middle_name" name="middle_name" type="text" value="{{ $prefillMiddle }}" />
        </div>
        <div>
          <label for="other_name">Other Name <span style="color:red">*</span></label>
          <input id="other_name" name="other_name" type="text" value="{{ $prefillOther }}" required />
        </div>
        <input id="full_name" name="full_name" type="hidden" value="{{ $prefill['full_name'] ?? '' }}" />
        <div>
          <label for="dob">Date of Birth <span style="color:red">*</span></label>
          <input id="dob" name="dob" type="date" value="{{ $prefill['dob'] ?? '' }}" required />
        </div>
        <div>
          <label for="age">Age <span style="color:red">*</span></label>
          <input id="age" name="age" type="number" min="0" value="{{ $prefill['age'] ?? '' }}" required />
        </div>
        <div>
          <label for="gender">Gender <span style="color:red">*</span></label>
          <select id="gender" name="gender" required>
            <option value="">-- Select --</option>
            <option {{ ($prefill['gender'] ?? '')==='Male' ? 'selected' : '' }}>Male</option>
            <option {{ ($prefill['gender'] ?? '')==='Female' ? 'selected' : '' }}>Female</option>
            <option {{ ($prefill['gender'] ?? '')==='Other / Prefer not to say' ? 'selected' : '' }}>Other / Prefer not to say</option>
          </select>
        </div>
        <div>
          <label for="birth_place">Place of Birth (Country) <span style="color:red">*</span></label>
          <input id="birth_place" name="birth_place" type="text" value="{{ $prefill['birth_place'] ?? '' }}" required />
        </div>
        <div>
          <label for="marital_status">Marital Status <span style="color:red">*</span></label>
          <select id="marital_status" name="marital_status" required>
            <option value="">-- Select --</option>
            @php $ms = $prefill['marital_status'] ?? '' @endphp
            <option {{ $ms==='Single' ? 'selected' : '' }}>Single</option>
            <option {{ $ms==='Married' ? 'selected' : '' }}>Married</option>
            <option {{ $ms==='Divorced' ? 'selected' : '' }}>Divorced</option>
            <option {{ $ms==='Widowed' ? 'selected' : '' }}>Widowed</option>
          </select>
        </div>
        <div>
          <label for="nationality">Nationality <span style="color:red">*</span></label>
          <input id="nationality" name="nationality" type="text" value="{{ $prefill['nationality'] ?? '' }}" required />
        </div>
        <div>
          <label for="passport_number">Passport Number (if International)</label>
          <input id="passport_number" name="passport_number" type="text" value="{{ $prefill['passport_number'] ?? '' }}" />
        </div>
      </div>

      <label for="mailing_address">Applicant’s Mailing Address <span style="color:red">*</span></label>
      <textarea id="mailing_address" name="mailing_address" rows="2" required>{{ $prefill['mailing_address'] ?? '' }}</textarea>

      <div class="row two">
        <div>
          <label for="emergency_contact">Emergency Contact (Name / Phone) <span style="color:red">*</span></label>
          <input id="emergency_contact" name="emergency_contact" type="text" value="{{ $prefill['emergency_contact'] ?? '' }}" required />
        </div>
        <div>
          <label for="telephone">Telephone <span style="color:red">*</span></label>
          <input id="telephone" name="telephone" type="tel" value="{{ $prefill['telephone'] ?? '' }}" required />
        </div>
        <div>
          <label for="email">E-mail <span style="color:red">*</span></label>
          <input id="email" name="email" type="email" value="{{ $prefill['email'] ?? '' }}" required />
        </div>
        <div>
          <label>Hostel Required? <span style="color:red">*</span></label>
          <div class="inline-options">
            <label><input type="radio" name="hostel_required" value="Yes" {{ ($prefill['hostel_required'] ?? '')==='Yes' ? 'checked' : '' }} required /> Yes</label>
            <label><input type="radio" name="hostel_required" value="No" {{ ($prefill['hostel_required'] ?? '')==='No' ? 'checked' : '' }} required /> No</label>
          </div>
        </div>
      </div>

      <label>Any disability that requires special attention? <span style="color:red">*</span></label>
      <div class="inline-options">
        <label><input type="radio" name="has_disability" value="Yes" {{ ($prefill['has_disability'] ?? '')==='Yes' ? 'checked' : '' }} required /> Yes</label>
        <label><input type="radio" name="has_disability" value="No" {{ ($prefill['has_disability'] ?? '')==='No' ? 'checked' : '' }} required /> No</label>
      </div>
      <textarea id="disability_details" name="disability_details" rows="2" placeholder="If Yes, please explain">{{ $prefill['disability_details'] ?? '' }}</textarea>
    </fieldset>

    <fieldset>
      <legend>Guardian Details</legend>
      <div class="row two">
        <div>
          <label for="guardian_name">Guardian Name</label>
          <input id="guardian_name" name="guardian_name" type="text" value="{{ $prefill['guardian_name'] ?? '' }}" />
        </div>
        <div>
          <label for="guardian_email">Guardian Email</label>
          <input id="guardian_email" name="guardian_email" type="email" value="{{ $prefill['guardian_email'] ?? '' }}" />
        </div>
        <div>
          <label for="guardian_phone">Guardian Phone Number</label>
          <input id="guardian_phone" name="guardian_phone" type="tel" value="{{ $prefill['guardian_phone'] ?? '' }}" />
        </div>
        <div>
          <label for="guardian_alternate_phone">Guardian Alternate Number</label>
          <input id="guardian_alternate_phone" name="guardian_alternate_phone" type="tel" value="{{ $prefill['guardian_alternate_phone'] ?? '' }}" />
        </div>
        <div>
          <label for="guardian_education">Guardian Education</label>
          <input id="guardian_education" name="guardian_education" type="text" value="{{ $prefill['guardian_education'] ?? '' }}" placeholder="e.g., Bachelor's Degree" />
        </div>
        <div>
          <label for="guardian_occupation">Guardian Occupation</label>
          <input id="guardian_occupation" name="guardian_occupation" type="text" value="{{ $prefill['guardian_occupation'] ?? '' }}" placeholder="e.g., Teacher, Doctor" />
        </div>
        <div>
          <label for="guardian_designation">Guardian Designation</label>
          <input id="guardian_designation" name="guardian_designation" type="text" value="{{ $prefill['guardian_designation'] ?? '' }}" placeholder="e.g., Senior Manager" />
        </div>
      </div>

      <label for="guardian_work_address">Guardian Work Address</label>
      <textarea id="guardian_work_address" name="guardian_work_address" rows="2" placeholder="Enter guardian's work address">{{ $prefill['guardian_work_address'] ?? '' }}</textarea>
    </fieldset>
      </div>

      <!-- Tab 2: Education -->
      <div class="tab-content" id="education">
    <fieldset>
      <legend>Institutions Attended / Qualifications</legend>
      <div class="hint">Add each institution separately. Include the qualification and the date.</div>

      <table class="inst-table" id="institutionsTable" aria-describedby="instHelp">
        <thead>
          <tr>
            <th style="width:45%">Institution Name</th>
            <th style="width:35%">Qualification</th>
            <th style="width:15%">Date</th>
            <th style="width:5%">Actions</th>
          </tr>
        </thead>
        <tbody id="institutionsBody">
          @php $institutions = isset($prefill['institutions']) && is_array($prefill['institutions']) ? $prefill['institutions'] : []; @endphp
          @if(count($institutions))
            @foreach($institutions as $i => $inst)
              <tr>
                <td><input type="text" name="institutions[{{ $i }}][name]" value="{{ $inst['name'] ?? '' }}" placeholder="e.g., Accra High School" required></td>
                <td><input type="text" name="institutions[{{ $i }}][qualification]" value="{{ $inst['qualification'] ?? '' }}" placeholder="e.g., WASSCE / Diploma / HND" required></td>
                <td><input type="date" name="institutions[{{ $i }}][date]" value="{{ $inst['date'] ?? '' }}" required></td>
                <td class="inst-row-actions">
                  <button type="button" class="btn-link" onclick="removeInstitutionRow(this)" aria-label="Remove row">Remove</button>
                </td>
              </tr>
            @endforeach
          @else
              <tr>
                <td><input type="text" name="institutions[0][name]" placeholder="e.g., Accra High School" required></td>
                <td><input type="text" name="institutions[0][qualification]" placeholder="e.g., WASSCE / Diploma / HND" required></td>
                <td><input type="date" name="institutions[0][date]" required></td>
                <td class="inst-row-actions">
                  <button type="button" class="btn-link" onclick="removeInstitutionRow(this)" aria-label="Remove row">Remove</button>
                </td>
              </tr>
          @endif
        </tbody>
      </table>

      <div style="margin-top:10px;">
        <button type="button" class="btn-secondary" onclick="addInstitutionRow()">Add another</button>
      </div>

      <div id="instHelp" class="hint">
          If you are awaiting results, you can add another row with the same institution and put "Awaiting (Index: XXXXXXXX)" in the Qualification field.
      </div>
    </fieldset>

    <fieldset>
      <legend>Enrollment Options</legend>
      <div class="row two">
        <div>
          <label>Applicant Type (tick all that apply)</label>
          <div class="inline-options">
            <label><input type="checkbox" name="entry_wassce" {{ !empty($prefill['entry_wassce']) ? 'checked' : '' }} /> WASSCE</label>
            <label><input type="checkbox" name="entry_sssce" {{ !empty($prefill['entry_sssce']) ? 'checked' : '' }} /> SSSCE</label>        
            <label><input type="checkbox" name="entry_ib" {{ !empty($prefill['entry_ib']) ? 'checked' : '' }} /> International Baccalaureate</label>
            <label><input type="checkbox" name="entry_transfer" {{ !empty($prefill['entry_transfer']) ? 'checked' : '' }} /> Transfer</label>
            <label><input type="checkbox" name="entry_other" {{ !empty($prefill['entry_other']) ? 'checked' : '' }} /> Other</label>
          </div>
        </div>
        <div>
          <label for="other_entry_detail" class="hint">If "Other", please specify</label>
          <input id="other_entry_detail" name="other_entry_detail" value="{{ $prefill['other_entry_detail'] ?? '' }}" />
        </div>
      </div>

      
        
        <!-- Dynamic Exam Sections (shown when any applicant type is checked) -->
        <div id="examSectionsWrapper" style="display:none; margin-top:16px;">
          <div class="row">
            <div class="inline-options" style="justify-content:space-between; width:100%;">
              <h4 style="margin:0;">Examination Details</h4>
              <button type="button" class="btn-secondary" id="addExamSectionBtn">+ Add New Section</button>
            </div>
          </div>

          <div class="alert" style="margin-top:10px; background:#0f4c5c; color:#fff; border-radius:8px; padding:12px;">
            <strong>Note:</strong> Please Note Enrollment Options (ie. Index No., Year of exam, etc.) will be used to pick RESULTS directly from your examination body to CHECK Validity
          </div>

          <div id="examSectionsContainer" style="margin-top:12px; display:grid; gap:16px;"></div>

          <template id="examSectionTemplate">
            <div class="exam-section" style="border:1px solid #cfd8dc; border-radius:8px; padding:14px;">
              <div class="row four">
        <div>
                  <label>Exam Type</label>
                  <input type="text" class="exam_type" placeholder="Eg. WASSCE" />
        </div>
        <div>
                  <label>Sitting Exam</label>
                  <input type="text" class="sitting_exam" placeholder="e.g. May/June (School)" />
        </div>
        <div>
                  <label>Year</label>
                  <input type="number" class="exam_year" min="1900" max="2100" placeholder="2021" />
        </div>
                <div>
                  <label>Index Number</label>
                  <input type="text" class="index_number" placeholder="0010408006" />
      </div>
              </div>

              <div style="margin-top:12px;">
                <div class="inline-options" style="justify-content:space-between; width:100%;">
                  <h5 style="margin:0;">Subjects & Grades</h5>
                  <button type="button" class="btn-link addSubjectBtn">+ Add More Fields</button>
        </div>
                <div class="hint">Tick up to 6 subjects as your Best 6.</div>
                <table class="inst-table" style="margin-top:8px;">
                  <thead>
                    <tr>
                      <th style="width:55%">Subject</th>
                      <th style="width:20%">Grade (Letter)</th>
                      <th style="width:15%">Grade (Number)</th>
                      <th style="width:10%">Best 6</th>
                    </tr>
                  </thead>
                  <tbody class="subjectsBody"></tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3" style="text-align:right; font-weight:600;">Total Best 6</td>
                      <td style="text-align:center; font-weight:700;">
                        <span class="best6TotalValue">0</span>
                      </td>
                    </tr>
                  </tfoot>
                </table>
        </div>

              <div class="inline-options" style="justify-content:flex-end; margin-top:10px;">
                <button type="button" class="btn-link removeExamSectionBtn">Remove Section</button>
        </div>
            </div>
          </template>

          <template id="subjectRowTemplate">
            <tr>
              <td><input type="text" class="subject_input" placeholder="e.g., Core Mathematics" /></td>
              <td><input type="text" class="grade_letter_input" placeholder="e.g., A1, B2" /></td>
              <td><input type="number" class="grade_number_input" min="1" max="9" placeholder="1" /></td>
              <td style="text-align:center;">
                <input type="checkbox" class="best_six_chk" />
              </td>
            </tr>
          </template>
      </div>
    </fieldset>
      </div>

      <!-- Tab 3: Programs -->
      <div class="tab-content" id="programs">
    <fieldset>
      <legend>Departments</legend>
      <div class="row two">
        @if(isset($departments) && $departments->count() > 0)
          @foreach($departments as $index => $department)
            <div>
              <label for="prog_{{ $department->id }}">{{ $department->name }}</label>
              <select id="prog_{{ $department->id }}" name="prog_{{ $department->id }}">
                <option value="">-- Select Programme --</option>
                @php $selectedProgram = $prefill['prog_' . $department->id] ?? '' @endphp
                @foreach($department->activePrograms as $program)
                  <option value="{{ $program->name }}" {{ $selectedProgram == $program->name ? 'selected' : '' }}>
                    {{ $program->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="prog_{{ $department->id }}_mode">Mode</label>
              <select id="prog_{{ $department->id }}_mode" name="prog_{{ $department->id }}_mode">
                <option value="">-- Select --</option>
                @php $selectedMode = $prefill['prog_' . $department->id . '_mode'] ?? '' @endphp
                <option value="Regular (4yrs)" {{ $selectedMode == 'Regular (4yrs)' ? 'selected' : '' }}>Regular (4yrs)</option>
                <option value="Top-up" {{ $selectedMode == 'Top-up' ? 'selected' : '' }}>Top-up</option>
              </select>
            </div>
          @endforeach
        @else
          <div class="col-12">
            <p class="text-muted">No departments available at the moment. Please contact the administration.</p>
          </div>
        @endif
      </div>
      <div class="hint">Tip: If you want applicants to choose multiple programmes, keep these dropdowns and also collect "Order of Preference" below.</div>

      <hr style="margin:16px 0;">
      <legend style="font-size:1rem;">Preferences</legend>
      <div class="row three">
        <div>
          <label for="preferred_session">Preferred Session</label>
          <select id="preferred_session" name="preferred_session">
            <option value="">-- Select --</option>
            @php $ps = $prefill['preferred_session'] ?? '' @endphp
            <option {{ $ps==='Morning' ? 'selected' : '' }}>Morning</option>
            <option {{ $ps==='Evening' ? 'selected' : '' }}>Evening</option>
            <option {{ $ps==='Weekend' ? 'selected' : '' }}>Weekend</option>
          </select>
        </div>
        <div>
          <label for="preferred_campus">Preferred Campus</label>
          <select id="preferred_campus" name="preferred_campus">
            <option value="">-- Select --</option>
            @php $pc = $prefill['preferred_campus'] ?? '' @endphp
            <option {{ $pc==='Delexes (Dawhenya)' ? 'selected' : '' }}>Delexes (Dawhenya)</option>
          </select>
        </div>
        <div>
          <label for="intake_option">Intake</label>
          <select id="intake_option" name="intake_option">
            <option value="">-- Select --</option>
            @php $io = $prefill['intake_option'] ?? '' @endphp
            <option {{ $io==='January' ? 'selected' : '' }}>January</option>
            <option {{ $io==='May' ? 'selected' : '' }}>May</option>
            <option {{ $io==='September' ? 'selected' : '' }}>September</option>
          </select>
        </div>
      </div>

      

      <hr style="margin:16px 0;">
      <legend style="font-size:1rem;">Order of Preference (Repeat Selected Programmes)</legend>
      <div class="row three">
        @php 
          $prefOptions = [
            'BSc Nursing',
            'BSc Midwifery',
            'BSc Marketing',
            'BSc Information Communication Technology',
            'BSc Human Resource Management',
            'BSc Risk Management and Insurance',
            'BSc Cybersecurity and Digital Forensics',
            'BSc Banking and Finance',
            'BSc Computer Science',
            'BSc Accounting',
          ];
          $p1 = trim($prefill['pref1'] ?? '');
          $p2 = trim($prefill['pref2'] ?? '');
          $p3 = trim($prefill['pref3'] ?? '');
        @endphp
        <div>
          <label for="pref1">1st Preference</label>
          <select id="pref1" name="pref1" onchange="autosaveDraft()">
            <option value="">-- Select --</option>
            @foreach($prefOptions as $opt)
              <option value="{{ $opt }}" {{ $p1 === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="pref2">2nd Preference</label>
          <select id="pref2" name="pref2" onchange="autosaveDraft()">
            <option value="">-- Select --</option>
            @foreach($prefOptions as $opt)
              <option value="{{ $opt }}" {{ $p2 === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="pref3">3rd Preference</label>
          <select id="pref3" name="pref3" onchange="autosaveDraft()">
            <option value="">-- Select --</option>
            @foreach($prefOptions as $opt)
              <option value="{{ $opt }}" {{ $p3 === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </fieldset>
      </div>

      <!-- Tab 4: Employment -->
      <div class="tab-content" id="employment">
    <fieldset>
      <legend>Record of Employment (if applicable)</legend>
      <div class="hint">Add each employment record separately. If you enter a company or duration, you must upload the appointment letter for that row.</div>

      <table class="inst-table" id="employmentTable">
        <thead>
          <tr>
            <th style="width:35%">Company / Organisation</th>
            <th style="width:25%">Duration</th>
            <th style="width:25%">Appointment Letter</th>
            <th style="width:15%">Actions</th>
          </tr>
        </thead>
        <tbody id="employmentBody">
          @php $employment = isset($prefill['employment']) && is_array($prefill['employment']) ? $prefill['employment'] : []; @endphp
          @if(count($employment))
            @foreach($employment as $i => $emp)
              <tr>
                <td><input type="text" name="employment[{{ $i }}][company]" value="{{ $emp['company'] ?? '' }}" placeholder="e.g., ABC Ltd"></td>
                <td><input type="text" name="employment[{{ $i }}][duration]" value="{{ $emp['duration'] ?? '' }}" placeholder="e.g., Jan 2020 – Dec 2022"></td>
                <td>
                  <input type="file" id="employment_file_{{ $i }}" name="employment[{{ $i }}][file]" accept="application/pdf,image/*" class="file-upload" data-max-size="1048576">
                  <small class="hint" style="display:block;margin-top:4px;">Max: 1MB</small>
                  <button type="button" class="btn-link" onclick="previewEmploymentFile({{ $i }})">Preview</button>
                  <div id="employment_preview_{{ $i }}" class="preview-box"></div>
                </td>
                <td class="inst-row-actions">
                  <button type="button" class="btn-link" onclick="removeEmploymentRow(this)">Remove</button>
                </td>
              </tr>
            @endforeach
          @else
              <tr>
                <td><input type="text" name="employment[0][company]" placeholder="e.g., ABC Ltd"></td>
                <td><input type="text" name="employment[0][duration]" placeholder="e.g., Jan 2020 – Dec 2022"></td>
                <td>
                  <input type="file" id="employment_file_0" name="employment[0][file]" accept="application/pdf,image/*" class="file-upload" data-max-size="1048576">
                  <small class="hint" style="display:block;margin-top:4px;">Max: 1MB</small>
                  <button type="button" class="btn-link" onclick="previewEmploymentFile(0)">Preview</button>
                  <div id="employment_preview_0" class="preview-box"></div>
                </td>
                <td class="inst-row-actions">
                  <button type="button" class="btn-link" onclick="removeEmploymentRow(this)">Remove</button>
                </td>
              </tr>
          @endif
        </tbody>
      </table>

      <div style="margin-top:10px;">
        <button type="button" class="btn-secondary" onclick="addEmploymentRow()">Add another</button>
      </div>
        
        <!-- Dynamic Exam Sections (shown when any applicant type is checked) -->
        <div id="examSectionsWrapper" style="display:none; margin-top:16px;">
          <div class="row">
            <div class="inline-options" style="justify-content:space-between; width:100%;">
              <h4 style="margin:0;">Examination Details</h4>
              <button type="button" class="btn-secondary" id="addExamSectionBtn">+ Add New Section</button>
            </div>
          </div>

          <div id="examSectionsContainer" style="margin-top:12px; display:grid; gap:16px;"></div>

          <template id="examSectionTemplate">
            <div class="exam-section" style="border:1px solid #cfd8dc; border-radius:8px; padding:14px;">
              <div class="row four">
                <div>
                  <label>Exam Type</label>
                  <input type="text" class="exam_type" placeholder="Eg. WASSCE" />
                </div>
                <div>
                  <label>Sitting Exam</label>
                  <input type="text" class="sitting_exam" placeholder="e.g. May/June (School)" />
                </div>
                <div>
                  <label>Year</label>
                  <input type="number" class="exam_year" min="1900" max="2100" placeholder="2021" />
                </div>
                <div>
                  <label>Index Number</label>
                  <input type="text" class="index_number" placeholder="0010408006" />
                </div>
              </div>

              <div style="margin-top:12px;">
                <div class="inline-options" style="justify-content:space-between; width:100%;">
                  <h5 style="margin:0;">Subjects & Grades</h5>
                  <button type="button" class="btn-link addSubjectBtn">+ Add More Fields</button>
                </div>
                <div class="hint">Tick up to 6 subjects as your Best 6.</div>
                <table class="inst-table" style="margin-top:8px;">
                  <thead>
                    <tr>
                      <th style="width:55%">Subject</th>
                      <th style="width:20%">Grade (Letter)</th>
                      <th style="width:15%">Grade (Number)</th>
                      <th style="width:10%">Best 6</th>
                    </tr>
                  </thead>
                  <tbody class="subjectsBody"></tbody>
                </table>
              </div>

              <div class="inline-options" style="justify-content:flex-end; margin-top:10px;">
                <button type="button" class="btn-link removeExamSectionBtn">Remove Section</button>
              </div>
            </div>
          </template>

          <template id="subjectRowTemplate">
            <tr>
              <td><input type="text" class="subject_input" placeholder="e.g., Core Mathematics" /></td>
              <td><input type="text" class="grade_letter_input" placeholder="e.g., A1, B2" /></td>
              <td><input type="number" class="grade_number_input" min="1" max="9" placeholder="1" /></td>
              <td style="text-align:center;">
                <input type="checkbox" class="best_six_chk" />
              </td>
            </tr>
          </template>
      </div>
    </fieldset>
      </div>

      <!-- Tab 5: Documents -->
      <div class="tab-content" id="documents">
    <fieldset>
      <legend>Checklist</legend>

      <div class="file-row three">
        <div>
          <label for="ghana_card_front">Ghana Card (Front) <span style="color:red">*</span></label>
          @if(!empty($submitted) && !empty($uploadedFiles['ghana_card_front']))
            <div class="mb-2">
              <a href="{{ asset('storage/'.$uploadedFiles['ghana_card_front']) }}" target="_blank">View uploaded file</a>
              @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['ghana_card_front']))
                <img src="{{ asset('storage/'.$uploadedFiles['ghana_card_front']) }}" alt="Ghana Card Front" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
            </div>
          @endif
          @if(empty($submitted))
            <input id="ghana_card_front" name="ghana_card_front" type="file" accept="image/*,application/pdf" required class="file-upload" data-max-size="1048576" />
            <small class="hint">Max file size: 1MB</small>
          @endif
        </div>
        <div>
          <label for="ghana_card_back">Ghana Card (Back) <span style="color:red">*</span></label>
          @if(!empty($submitted) && !empty($uploadedFiles['ghana_card_back']))
            <div class="mb-2">
              <a href="{{ asset('storage/'.$uploadedFiles['ghana_card_back']) }}" target="_blank">View uploaded file</a>
              @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['ghana_card_back']))
                <img src="{{ asset('storage/'.$uploadedFiles['ghana_card_back']) }}" alt="Ghana Card Back" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
            </div>
          @endif
          @if(empty($submitted))
            <input id="ghana_card_back" name="ghana_card_back" type="file" accept="image/*,application/pdf" required class="file-upload" data-max-size="1048576" />
            <small class="hint">Max file size: 1MB</small>
          @endif
        </div>
        @if(empty($submitted))
        <div class="inline-options" style="justify-content:flex-end;">
          <button type="button" class="btn-link" onclick="previewFiles(['ghana_card_front','ghana_card_back'],'preview_ghana_card')">Preview</button>
          <button type="button" class="btn-link" onclick="clearUploads(['ghana_card_front','ghana_card_back'], 'preview_ghana_card')">Remove</button>
        </div>
        @endif
      </div>
      <div id="preview_ghana_card" class="preview-box" aria-live="polite"></div>

      <div class="file-row two" style="margin-top:12px;">
        <div>
          <label for="official_transcript">Official Transcript (PDF or Image)</label>
          @if(!empty($submitted) && !empty($uploadedFiles['official_transcript']))
            <div class="mb-2">
              <a href="{{ asset('storage/'.$uploadedFiles['official_transcript']) }}" target="_blank">View uploaded file</a>
              @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['official_transcript']))
                <img src="{{ asset('storage/'.$uploadedFiles['official_transcript']) }}" alt="Official Transcript" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
            </div>
          @endif
          @if(empty($submitted))
            <input id="official_transcript" name="official_transcript" type="file" accept="application/pdf,image/*" class="file-upload" data-max-size="1048576" />
            <small class="hint">Max file size: 1MB</small>
          @endif
        </div>
        @if(empty($submitted))
        <div class="inline-options" style="justify-content:flex-end;">
          <button type="button" class="btn-link" onclick="previewFiles(['official_transcript'],'preview_transcript')">Preview</button>
          <button type="button" class="btn-link" onclick="clearUploads(['official_transcript'],'preview_transcript')">Remove</button>
        </div>
        @endif
      </div>
      <div id="preview_transcript" class="preview-box" aria-live="polite"></div>

      <div class="file-row two" style="margin-top:12px;">
        <div>
          <label for="passport_picture">Passport Picture (Image) <span style="color:red">*</span></label>
          @if(!empty($submitted) && !empty($uploadedFiles['passport_picture']))
            <div class="mb-2">
              <a href="{{ asset('storage/'.$uploadedFiles['passport_picture']) }}" target="_blank">View uploaded file</a>
              @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['passport_picture']))
                <img src="{{ asset('storage/'.$uploadedFiles['passport_picture']) }}" alt="Passport Picture" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
            </div>
          @endif
          @if(empty($submitted))
            <input id="passport_picture" name="passport_picture" type="file" accept="image/*" required class="file-upload" data-max-size="1048576" />
            <small class="hint">Max file size: 1MB</small>
          @endif
        </div>
        @if(empty($submitted))
        <div class="inline-options" style="justify-content:flex-end;">
          <button type="button" class="btn-link" onclick="previewFiles(['passport_picture'],'preview_passport')">Preview</button>
          <button type="button" class="btn-link" onclick="clearUploads(['passport_picture'],'preview_passport')">Remove</button>
        </div>
        @endif
      </div>
      <div id="preview_passport" class="preview-box" aria-live="polite"></div>

      <div class="file-row two" style="margin-top:12px;">
        <div>
          <label for="other_academic_records">Other Academic Records (PDFs or Images)</label>
          @if(!empty($submitted) && !empty($uploadedFiles['other_academic_records']) && is_array($uploadedFiles['other_academic_records']))
            <div class="mb-2">
              @foreach($uploadedFiles['other_academic_records'] as $path)
                <div style="margin-bottom: 15px;">
                  <a href="{{ asset('storage/'.$path) }}" target="_blank">View file</a>
                  @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $path))
                    <img src="{{ asset('storage/'.$path) }}" alt="Academic Record" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
                  @endif
                </div>
              @endforeach
            </div>
          @endif
          @if(empty($submitted))
            <input id="other_academic_records" name="other_academic_records" type="file" accept="application/pdf,image/*" multiple class="file-upload" data-max-size="1048576" />
            <small class="hint">Max file size: 1MB per file</small>
          @endif
          <div id="other_files_list" class="file-names"></div>
        </div>
        @if(empty($submitted))
        <div class="inline-options" style="justify-content:flex-end;">
          <button type="button" class="btn-link" onclick="previewFiles(['other_academic_records'],'preview_other')">Preview</button>
          <button type="button" class="btn-link" onclick="clearUploads(['other_academic_records'],'preview_other', true)">Clear all</button>
        </div>
        @endif
      </div>
      <div id="preview_other" class="preview-box" aria-live="polite"></div>

      <div class="hint" style="margin-top:10px;">
        <span style="color:red">*</span> Ghana Card (Front & Back), and Passport Picture, are required.  
      </div>
    </fieldset>
      </div>
      <!-- Navigation Buttons -->
      <div class="tab-navigation-buttons">
        <button type="button" class="tab-nav-btn" id="prevBtn" onclick="changeTab(-1)" disabled>
          ← Previous
        </button>
        <button type="button" class="tab-nav-btn" id="nextBtn" onclick="changeTab(1)">
          Save &Next →
        </button>
        <button type="submit" class="tab-nav-btn btn-success" id="submitBtn" style="display: none;">
          Submit Application
        </button>
      </div>
    </div>
  </form>
  @endif

  @if(!empty($submitted))
    <!-- Read-only Summary -->
    <fieldset>
      <legend>Application Summary</legend>
      <div class="row two">
        <div>
          <label>Application Number</label>
          <input type="text" value="{{ $application->application_number ?? '' }}" readonly>
        </div>
        <div>
          <label>Academic Year</label>
          <input type="text" value="{{ $application->academic_year ?? '' }}" readonly>
        </div>
      </div>

      <div class="row three" style="margin-top:12px;">
        <div>
          <label>Preferred Session</label>
          <input type="text" value="{{ $application->data['preferred_session'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Preferred Campus</label>
          <input type="text" value="{{ $application->data['preferred_campus'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Intake</label>
          <input type="text" value="{{ $application->data['intake_option'] ?? '' }}" readonly>
        </div>
      </div>

      <div style="margin-top:16px;">
        <h4>Examination Details</h4>
        @forelse(($examRecords ?? []) as $rec)
          <div style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:12px;">
            <div class="row four">
              <div>
                <label>Exam Type</label>
                <input type="text" value="{{ $rec->exam_type }}" readonly>
              </div>
              <div>
                <label>Sitting Exam</label>
                <input type="text" value="{{ $rec->sitting_exam }}" readonly>
              </div>
              <div>
                <label>Year</label>
                <input type="text" value="{{ $rec->year }}" readonly>
              </div>
              <div>
                <label>Index Number</label>
                <input type="text" value="{{ $rec->index_number }}" readonly>
              </div>
            </div>
            <table class="inst-table" style="margin-top:10px;">
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
        @empty
          <div class="hint">No exam records captured.</div>
        @endforelse
      </div>
    </fieldset>

    <fieldset>
      <legend>Personal Data</legend>
      <div class="row two">
        <div>
          <label>Full Name</label>
          <input type="text" value="{{ $application->data['full_name'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Date of Birth</label>
          <input type="text" value="{{ $application->data['dob'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Age</label>
          <input type="text" value="{{ $application->data['age'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Gender</label>
          <input type="text" value="{{ $application->data['gender'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Place of Birth</label>
          <input type="text" value="{{ $application->data['birth_place'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Marital Status</label>
          <input type="text" value="{{ $application->data['marital_status'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Nationality</label>
          <input type="text" value="{{ $application->data['nationality'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Passport Number</label>
          <input type="text" value="{{ $application->data['passport_number'] ?? '' }}" readonly>
        </div>
      </div>
      <div class="row two">
        <div>
          <label>Mailing Address</label>
          <textarea rows="2" readonly>{{ $application->data['mailing_address'] ?? '' }}</textarea>
        </div>
        <div>
          <label>Emergency Contact</label>
          <input type="text" value="{{ $application->data['emergency_contact'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Telephone</label>
          <input type="text" value="{{ $application->data['telephone'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Email</label>
          <input type="text" value="{{ $application->data['email'] ?? '' }}" readonly>
        </div>
      </div>
      <div class="row two">
        <div>
          <label>Hostel Required</label>
          <input type="text" value="{{ !empty($application->data['hostel_required']) ? 'Yes' : 'No' }}" readonly>
        </div>
        <div>
          <label>Disability</label>
          <input type="text" value="{{ !empty($application->data['has_disability']) ? 'Yes' : 'No' }}" readonly>
        </div>
      </div>
      @if(!empty($application->data['disability_details']))
      <div>
        <label>Disability Details</label>
        <textarea rows="2" readonly>{{ $application->data['disability_details'] }}</textarea>
      </div>
      @endif
    </fieldset>

    <fieldset>
      <legend>Guardian Details</legend>
      <div class="row two">
        <div>
          <label>Guardian Name</label>
          <input type="text" value="{{ $application->data['guardian_name'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Email</label>
          <input type="text" value="{{ $application->data['guardian_email'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Phone Number</label>
          <input type="text" value="{{ $application->data['guardian_phone'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Alternate Number</label>
          <input type="text" value="{{ $application->data['guardian_alternate_phone'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Education</label>
          <input type="text" value="{{ $application->data['guardian_education'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Occupation</label>
          <input type="text" value="{{ $application->data['guardian_occupation'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Guardian Designation</label>
          <input type="text" value="{{ $application->data['guardian_designation'] ?? '' }}" readonly>
        </div>
      </div>
      @if(!empty($application->data['guardian_work_address']))
      <div>
        <label>Guardian Work Address</label>
        <textarea rows="2" readonly>{{ $application->data['guardian_work_address'] }}</textarea>
      </div>
      @endif
    </fieldset>

    <fieldset>
      <legend>Education</legend>
      <table class="inst-table">
        <thead>
          <tr>
            <th>Institution</th>
            <th>Qualification</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($application->data['institutions'] ?? []) as $inst)
            <tr>
              <td>{{ $inst['name'] ?? '' }}</td>
              <td>{{ $inst['qualification'] ?? '' }}</td>
              <td>{{ $inst['date'] ?? '' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="hint">No institutions provided.</td></tr>
          @endforelse
        </tbody>
      </table>
    </fieldset>

    <fieldset>
      <legend>Programme Preferences</legend>
      <div class="row three">
        <div>
          <label>1st Preference</label>
          <input type="text" value="{{ $application->data['pref1'] ?? '' }}" readonly>
        </div>
        <div>
          <label>2nd Preference</label>
          <input type="text" value="{{ $application->data['pref2'] ?? '' }}" readonly>
        </div>
        <div>
          <label>3rd Preference</label>
          <input type="text" value="{{ $application->data['pref3'] ?? '' }}" readonly>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Languages</legend>
      <div class="row three">
        <div>
          <label>English Proficiency</label>
          <input type="text" value="{{ $application->data['english_level'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Mother Tongue</label>
          <input type="text" value="{{ $application->data['mother_tongue'] ?? '' }}" readonly>
        </div>
        <div>
          <label>Other Languages</label>
          <input type="text" value="{{ $application->data['other_languages'] ?? '' }}" readonly>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Employment</legend>
      <table class="inst-table">
        <thead>
          <tr>
            <th>Company</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($application->data['employment'] ?? []) as $emp)
            <tr>
              <td>{{ $emp['company'] ?? '' }}</td>
              <td>{{ $emp['duration'] ?? '' }}</td>
            </tr>
          @empty
            <tr><td colspan="2" class="hint">No employment records provided.</td></tr>
          @endforelse
        </tbody>
      </table>
    </fieldset>

    <fieldset>
      <legend>Uploads</legend>
      <div class="row two">
        <div>
          <label>Ghana Card (Front)</label>
          @if(!empty($uploadedFiles['ghana_card_front']))
            
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['ghana_card_front']))
                <img src="{{ asset('storage/'.$uploadedFiles['ghana_card_front']) }}" alt="Ghana Card Front" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
      @else
            <span class="hint">Not uploaded</span>
      @endif
    </div>
        <div>
          <label>Ghana Card (Back)</label>
          @if(!empty($uploadedFiles['ghana_card_back']))
           
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['ghana_card_back']))
                <img src="{{ asset('storage/'.$uploadedFiles['ghana_card_back']) }}" alt="Ghana Card Back" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
          @else
            <span class="hint">Not uploaded</span>
          @endif
        </div>
        <div>
          <label>Official Transcript</label>
          @if(!empty($uploadedFiles['official_transcript']))
            <a target="_blank" href="{{ asset('storage/'.$uploadedFiles['official_transcript']) }}">View</a>
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['official_transcript']))
                <img src="{{ asset('storage/'.$uploadedFiles['official_transcript']) }}" alt="Official Transcript" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
          @else
            <span class="hint">Not uploaded</span>
          @endif
        </div>
        <div>
          <label>Passport Picture</label>
          @if(!empty($uploadedFiles['passport_picture']))
           
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $uploadedFiles['passport_picture']))
                <img src="{{ asset('storage/'.$uploadedFiles['passport_picture']) }}" alt="Passport Picture" style="max-width: 100%; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;">
              @endif
          @else
            <span class="hint">Not uploaded</span>
          @endif
        </div>
      </div>
      @if(!empty($uploadedFiles['other_academic_records']) && is_array($uploadedFiles['other_academic_records']))
        <div style="margin-top:8px;">
          <label>Other Academic Records</label>
          <ul>
            @foreach($uploadedFiles['other_academic_records'] as $path)
              <li><a target="_blank" href="{{ asset('storage/'.$path) }}">View file</a></li>
            @endforeach
          </ul>
        </div>
      @endif
    </fieldset>
  @endif
</div>

<script>
// Tab Management
let currentTab = 0;
const totalTabs = 5;

function showTab(n) {
  const tabs = document.querySelectorAll('.tab-content');
  const buttons = document.querySelectorAll('.tab-button');
  
  // Hide all tabs
  tabs.forEach(tab => tab.classList.remove('active'));
  // Update side nav active
  document.querySelectorAll('.side-item').forEach(it => it.classList.remove('active'));
  
  // Show current tab
  if (tabs[n]) tabs[n].classList.add('active');
  const sideItems = document.querySelectorAll('.side-item');
  if (sideItems[n]) sideItems[n].classList.add('active');
  
  // Update progress indicator
  document.getElementById('currentStep').textContent = n + 1;
  
  // Update navigation buttons
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  
  prevBtn.disabled = n === 0;
  
  if (n === totalTabs - 1) {
    nextBtn.style.display = 'none';
    submitBtn.style.display = 'inline-block';
  } else {
    nextBtn.style.display = 'inline-block';
    submitBtn.style.display = 'none';
  }
  
  // Check completion status
  updateTabCompletionStatus();
}

function changeTab(direction) {
  const newTab = currentTab + direction;
  
  if (newTab >= 0 && newTab < totalTabs) {
    // Validate current tab before moving forward
    if (direction > 0 && !validateCurrentTab()) {
      return;
    }

    // Auto-save draft when moving to next tab
    if (direction > 0) {
      autosaveDraft()
        .finally(() => {
          currentTab = newTab;
          showTab(currentTab);
        });
      return; // navigation continues in finally
    }

    currentTab = newTab;
    showTab(currentTab);
  }
}

function validateCurrentTab() {
  const currentTabContent = document.querySelector('.tab-content.active');
  const requiredFields = currentTabContent.querySelectorAll('input[required], select[required], textarea[required]');
  
  for (let field of requiredFields) {
    if (!field.value.trim()) {
      field.focus();
      alert(`Please fill in all required fields in the ${getTabName(currentTab)} section.`);
      return false;
    }
  }
  
  return true;
}

function getTabName(tabIndex) {
  const tabNames = [
    'Personal Data', 'Education', 'Programs', 
    'Employment', 'Documents'
  ];
  return tabNames[tabIndex] || 'Unknown';
}

function updateTabCompletionStatus() {
  const tabButtons = document.querySelectorAll('.side-item');
  tabButtons.forEach((button, index) => {
    const tabContent = document.getElementById(button.dataset.tab);
    const requiredFields = tabContent.querySelectorAll('input[required], select[required], textarea[required]');

    let completed = true;
    if (requiredFields.length > 0) {
      for (let field of requiredFields) {
        if (!String(field.value || '').trim()) {
          completed = false;
          break;
        }
      }
    } else {
      // If no required fields, treat completion per-tab with custom rules
      const tabId = button.dataset.tab;
      if (tabId === 'programs') {
        // Programs considered completed only if at least one program dropdown has a non-empty value
        const selects = tabContent.querySelectorAll('select');
        completed = Array.from(selects).some(s => String(s.value || '').trim().length > 0);
      } else {
        const anyFields = tabContent.querySelectorAll('input, select, textarea');
        completed = Array.from(anyFields).some(f => {
          if (f.type === 'checkbox' || f.type === 'radio') return f.checked;
          return String(f.value || '').trim().length > 0;
        });
      }
    }

    const badge = button.querySelector('.status-badge');
    if (completed) {
      badge.textContent = 'Completed';
      badge.classList.remove('status-pending');
      badge.classList.add('status-completed');
    } else {
      badge.textContent = 'Pending';
      badge.classList.remove('status-completed');
      badge.classList.add('status-pending');
    }
  });
}

// Tab click handlers
// Side navigation click handlers
document.getElementById('sideNavList').addEventListener('click', function(e){
  const item = e.target.closest('.side-item');
  if (!item) return;
  const tabs = ['personal','education','programs','employment','documents'];
  const idx = tabs.indexOf(item.dataset.tab);
  if (idx === -1) return;
  if (idx < currentTab || validateCurrentTab()) {
    currentTab = idx;
    showTab(currentTab);
  }
});

// Field change handlers for completion status
document.addEventListener('input', updateTabCompletionStatus);
document.addEventListener('change', updateTabCompletionStatus);

// Initialize tabs
showTab(0);

// Auto-save draft on Next
function autosaveDraft() {
  const form = document.getElementById('applicationForm');
  if (!form) return Promise.resolve();

  const formData = new FormData(form);
  const json = {};

  // Helper: set nested value using bracket path e.g., institutions[0][name]
  function setNested(target, path, value) {
    const parts = [];
    path.replace(/\]/g, '').split('[').forEach((segment) => {
      segment.split('.').forEach(s => { if (s !== '') parts.push(s); });
    });
    const first = parts.shift();
    let obj = target;
    const all = [first, ...parts];
    for (let i = 0; i < all.length; i++) {
      const key = all[i];
      const nextKey = all[i+1];
      const isLast = i === all.length - 1;
      if (isLast) {
        if (key === '') {
          if (!Array.isArray(obj)) obj = []; // shouldn't occur
          obj.push(value);
        } else {
          obj[key] = value;
        }
      } else {
        const shouldBeArray = nextKey !== undefined && /^\d+$/.test(nextKey);
        if (obj[key] === undefined) {
          obj[key] = shouldBeArray ? [] : {};
        }
        obj = obj[key];
      }
    }
    return target;
  }

  for (const [rawName, rawVal] of formData.entries()) {
    const name = rawName;
    const value = rawVal;
    if (name.endsWith('[]')) {
      const base = name.slice(0, -2);
      if (!json[base]) json[base] = [];
      json[base].push(value);
      continue;
    }
    if (name.includes('[')) {
      setNested(json, name, value);
    } else {
      json[name] = value;
    }
  }

  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  return fetch('{{ route('portal.application.save') }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    body: JSON.stringify(json)
  })
  .then(() => {
    const s = document.getElementById('draftStatus');
    if (s) { s.style.display = 'inline'; setTimeout(() => s.style.display = 'none', 1500); }
  })
  .catch(() => { /* ignore draft errors */ });
}

// ============ Enrollment -> Dynamic Exam Sections ============
(function initExamSections(){
  const wrapper = document.getElementById('examSectionsWrapper');
  const container = document.getElementById('examSectionsContainer');
  const addExamBtn = document.getElementById('addExamSectionBtn');
  const applicantTypeChecks = [
    document.querySelector('input[name="entry_wassce"]'),
    document.querySelector('input[name="entry_sssce"]'),
    document.querySelector('input[name="entry_ib"]'),
    document.querySelector('input[name="entry_transfer"]'),
    document.querySelector('input[name="entry_other"]'),
  ].filter(Boolean);

  function anyApplicantTypeSelected(){
    return applicantTypeChecks.some(chk => chk && chk.checked);
  }

  function toggleWrapper(){
    wrapper.style.display = anyApplicantTypeSelected() ? 'block' : 'none';
  }

  applicantTypeChecks.forEach(chk => chk.addEventListener('change', toggleWrapper));
  toggleWrapper();

  function addSubjectRow(sectionEl){
    const tmpl = document.getElementById('subjectRowTemplate');
    const tbody = sectionEl.querySelector('.subjectsBody');
    const clone = tmpl.content.cloneNode(true);
    tbody.appendChild(clone);

    // Assign names for newly added row
    const sectionIdx = parseInt(sectionEl.dataset.sectionIndex, 10);
    const rowIdx = tbody.querySelectorAll('tr').length - 1;
    const lastRow = tbody.querySelectorAll('tr')[rowIdx];
    if (lastRow) {
      const subjectInput = lastRow.querySelector('.subject_input');
      const letterInput = lastRow.querySelector('.grade_letter_input');
      const numberInput = lastRow.querySelector('.grade_number_input');
      const bestChk = lastRow.querySelector('.best_six_chk');

      if (subjectInput) { subjectInput.name = `exam_sections[${sectionIdx}][subjects][${rowIdx}][subject]`; subjectInput.addEventListener('change', () => autosaveDraft()); }
      if (letterInput) { letterInput.name = `exam_sections[${sectionIdx}][subjects][${rowIdx}][grade_letter]`; letterInput.addEventListener('change', () => autosaveDraft()); }
      if (numberInput) { numberInput.name = `exam_sections[${sectionIdx}][subjects][${rowIdx}][grade_number]`; numberInput.addEventListener('input', () => { computeBest6Total(sectionEl); autosaveDraft(); }); }
      if (bestChk) { bestChk.name = `exam_sections[${sectionIdx}][subjects][${rowIdx}][is_best_six]`; bestChk.addEventListener('change', () => { computeBest6Total(sectionEl); autosaveDraft(); }); }
    }

    enforceBestSix(sectionEl);
  }

  function enforceBestSix(sectionEl){
    const checks = sectionEl.querySelectorAll('.best_six_chk');
    const checked = Array.from(checks).filter(c => c.checked);
    checks.forEach(chk => {
      chk.addEventListener('change', () => {
        const selected = Array.from(checks).filter(c => c.checked);
        if (selected.length > 6) {
          chk.checked = false;
          alert('You can select only up to 6 subjects as Best 6.');
        }
        computeBest6Total(sectionEl);
      });
    });
    if (checked.length > 6) {
      // Uncheck extras if prepopulated somehow
      checked.slice(6).forEach(c => c.checked = false);
    }
    computeBest6Total(sectionEl);
  }

  function addExamSection(){
    const tmpl = document.getElementById('examSectionTemplate');
    const section = tmpl.content.cloneNode(true);
    const sectionEl = section.querySelector('.exam-section');

    // Add an initial subject row
    const sectionIdx = document.querySelectorAll('#examSectionsContainer .exam-section').length;
    sectionEl.dataset.sectionIndex = sectionIdx;

    // Set names for the section header inputs
    const typeInput = sectionEl.querySelector('.exam_type');
    const sittingInput = sectionEl.querySelector('.sitting_exam');
    const yearInput = sectionEl.querySelector('.exam_year');
    const indexInput = sectionEl.querySelector('.index_number');
    if (typeInput) { typeInput.name = `exam_sections[${sectionIdx}][exam_type]`; typeInput.addEventListener('change', () => autosaveDraft()); }
    if (sittingInput) { sittingInput.name = `exam_sections[${sectionIdx}][sitting_exam]`; sittingInput.addEventListener('change', () => autosaveDraft()); }
    if (yearInput) { yearInput.name = `exam_sections[${sectionIdx}][year]`; yearInput.addEventListener('change', () => autosaveDraft()); }
    if (indexInput) { indexInput.name = `exam_sections[${sectionIdx}][index_number]`; indexInput.addEventListener('change', () => autosaveDraft()); }

    setTimeout(() => addSubjectRow(sectionEl));

    // Recompute total if number value changes
    sectionEl.addEventListener('input', (e) => {
      if (e.target && e.target.classList.contains('grade_number_input')) {
        computeBest6Total(sectionEl);
      }
    });

    // Bind add subject button
    sectionEl.querySelector('.addSubjectBtn').addEventListener('click', () => addSubjectRow(sectionEl));

    // Bind remove section button
    sectionEl.querySelector('.removeExamSectionBtn').addEventListener('click', () => {
      if (confirm('Remove this exam section?')) {
        sectionEl.remove();
      }
    });

    container.appendChild(sectionEl);
  }

  function computeBest6Total(sectionEl){
    const rows = sectionEl.querySelectorAll('.subjectsBody tr');
    let selected = [];
    rows.forEach(tr => {
      const chk = tr.querySelector('.best_six_chk');
      const num = tr.querySelector('.grade_number_input');
      const val = num && num.value !== '' ? parseInt(num.value, 10) : null;
      if (chk && chk.checked && val !== null && !isNaN(val)) {
        selected.push(val);
      }
    });
    // Consider only top 6 selections (if user checks more we'll clamp earlier, but be safe)
    selected = selected.slice(0,6);
    const total = selected.reduce((a,b) => a + b, 0);
    const out = sectionEl.querySelector('.best6TotalValue');
    if (out) out.textContent = total;
  }

  if (addExamBtn) addExamBtn.addEventListener('click', addExamSection);
  // If we have prefilled exam sections in draft, render them
  if (Array.isArray(window.prefillExamSections) && window.prefillExamSections.length && !window.isSubmittedView) {
    window.prefillExamSections.forEach((sec, idx) => {
      addExamSection();
      const sectionEl = container.querySelectorAll('.exam-section')[idx];
      if (!sectionEl) return;
      sectionEl.querySelector('.exam_type').value = sec.exam_type || '';
      sectionEl.querySelector('.sitting_exam').value = sec.sitting_exam || '';
      sectionEl.querySelector('.exam_year').value = sec.year || '';
      sectionEl.querySelector('.index_number').value = sec.index_number || '';
      const subjects = Array.isArray(sec.subjects) ? sec.subjects : [];
      subjects.forEach((row, r) => {
        addSubjectRow(sectionEl);
        const lastRow = sectionEl.querySelectorAll('.subjectsBody tr')[r];
        if (!lastRow) return;
        const subjectInput = lastRow.querySelector('.subject_input');
        const letterInput = lastRow.querySelector('.grade_letter_input');
        const numberInput = lastRow.querySelector('.grade_number_input');
        const bestChk = lastRow.querySelector('.best_six_chk');
        if (subjectInput) subjectInput.value = row.subject || '';
        if (letterInput) letterInput.value = row.grade_letter || '';
        if (numberInput) numberInput.value = row.grade_number || '';
        if (bestChk) bestChk.checked = !!row.is_best_six;
      });
      computeBest6Total(sectionEl);
    });
  }
})();

// Existing functions
function addInstitutionRow() {
  const tbody = document.getElementById('institutionsBody');
  const idx = tbody.querySelectorAll('tr').length;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="text" name="institutions[${idx}][name]" placeholder="e.g., Accra High School" required></td>
    <td><input type="text" name="institutions[${idx}][qualification]" placeholder="e.g., WASSCE " required></td>
    <td><input type="date" name="institutions[${idx}][date]" required></td>
    <td class="inst-row-actions">
      <button type="button" class="btn-link" onclick="removeInstitutionRow(this)" aria-label="Remove row">Remove</button>
    </td>
  `;
  tbody.appendChild(tr);
}

function removeInstitutionRow(btn) {
  const tbody = document.getElementById('institutionsBody');
  tbody.removeChild(btn.closest('tr'));
  Array.from(tbody.querySelectorAll('tr')).forEach((tr, i) => {
    tr.querySelectorAll('input').forEach(input => {
      input.name = input.name.replace(/\[\d+\]/, '[' + i + ']');
    });
  });
}

function previewFiles(inputIds, previewContainerId) {
  const container = document.getElementById(previewContainerId);
  container.innerHTML = '';
  let files = [];
  inputIds.forEach(id => {
    const input = document.getElementById(id);
    if (!input) return;
    if (input.multiple) {
      files = files.concat(Array.from(input.files || []));
    } else if (input.files && input.files[0]) {
      files.push(input.files[0]);
    }
  });

  if (!files.length) {
    container.classList.add('active');
    container.innerHTML = '<div class="hint">No file selected.</div>';
    return;
  }

  const first = files[0];
  renderSinglePreview(first, container);

  if (files.length > 1) {
    const names = files.map(f => f.name).join(', ');
    const list = document.createElement('div');
    list.className = 'file-names';
    list.textContent = 'Files: ' + names;
    container.appendChild(list);
  }

  container.classList.add('active');
}

function renderSinglePreview(file, container) {
  const url = URL.createObjectURL(file);
  const mime = file.type || '';
  container.innerHTML = '';
  if (mime.startsWith('image/')) {
    const img = document.createElement('img');
    img.src = url;
    img.alt = 'Preview';
    container.appendChild(img);
  } else if (mime === 'application/pdf') {
    const embed = document.createElement('embed');
    embed.src = url;
    embed.type = 'application/pdf';
    embed.style.width = '100%';
    embed.style.height = '480px';
    embed.setAttribute('aria-label', 'PDF Preview');
    container.appendChild(embed);
  } else {
    const p = document.createElement('p');
    p.textContent = 'Selected file: ' + (file.name || 'unnamed') + ' (' + (mime || 'unknown type') + ')';
    container.appendChild(p);
  }
}

function clearAllPreviews() {
  document.querySelectorAll('.preview-box').forEach(box => {
    box.classList.remove('active');
    box.innerHTML = '';
  });
  const otherList = document.getElementById('other_files_list');
  if (otherList) otherList.textContent = '';
}

function addEmploymentRow() {
  const tbody = document.getElementById('employmentBody');
  const idx = tbody.querySelectorAll('tr').length;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="text" name="employment[${idx}][company]" placeholder="e.g., ABC Ltd"></td>
    <td><input type="text" name="employment[${idx}][duration]" placeholder="e.g., Jan 2020 – Dec 2022"></td>
    <td>
      <input type="file" id="employment_file_${idx}" name="employment[${idx}][file]" accept="application/pdf,image/*" class="file-upload" data-max-size="1048576">
      <small class="hint" style="display:block;margin-top:4px;">Max: 1MB</small>
      <button type="button" class="btn-link" onclick="previewEmploymentFile(${idx})">Preview</button>
      <div id="employment_preview_${idx}" class="preview-box"></div>
    </td>
    <td class="inst-row-actions">
      <button type="button" class="btn-link" onclick="removeEmploymentRow(this)">Remove</button>
    </td>
  `;
  tbody.appendChild(tr);
}

function removeEmploymentRow(btn) {
  const tbody = document.getElementById('employmentBody');
  tbody.removeChild(btn.closest('tr'));
}

function previewEmploymentFile(idx) {
  const input = document.getElementById(`employment_file_${idx}`);
  const container = document.getElementById(`employment_preview_${idx}`);
  container.innerHTML = '';
  if (!input.files || !input.files[0]) {
    container.classList.add('active');
    container.innerHTML = '<div class="hint">No file selected.</div>';
    return;
  }
  const file = input.files[0];
  const url = URL.createObjectURL(file);
  if (file.type.startsWith('image/')) {
    const img = document.createElement('img');
    img.src = url;
    container.innerHTML = '';
    container.appendChild(img);
  } else if (file.type === 'application/pdf') {
    const embed = document.createElement('embed');
    embed.src = url;
    embed.type = 'application/pdf';
    embed.style.width = '100%';
    embed.style.height = '400px';
    container.innerHTML = '';
    container.appendChild(embed);
  } else {
    container.textContent = 'Selected file: ' + file.name;
  }
  container.classList.add('active');
}

// Form validation on submit
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('applicationForm');
  
  if (!form) {
    console.log('WARNING: Form not found!');
    return;
  }
  
  console.log('Form validation listener attached successfully');
  
  form.addEventListener('submit', function (e) {
    console.log('Form submit validation triggered');
    
    // FIRST: Validate all file uploads for size (1MB = 1048576 bytes)
    const allFileInputs = form.querySelectorAll('input[type="file"].file-upload');
    for (let fileInput of allFileInputs) {
      if (fileInput.files && fileInput.files.length > 0) {
        const maxSize = parseInt(fileInput.getAttribute('data-max-size')) || 1048576;
        for (let file of fileInput.files) {
          if (file.size > maxSize) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.style.outline = '2px solid #e53935';
            setTimeout(() => fileInput.focus(), 100);
            const sizeMB = (file.size / 1048576).toFixed(2);
            alert(`File "${file.name}" is too large (${sizeMB}MB). Maximum allowed size is 1MB. Please choose a smaller file or compress it.`);
            return false;
          }
        }
      }
    }
    
    // SECOND: Specifically validate Documents tab required files (Priority check)
    const ghanaCardFront = document.getElementById('ghana_card_front');
    const ghanaCardBack = document.getElementById('ghana_card_back');
    const passportPicture = document.getElementById('passport_picture');
    
    // Only validate if these elements exist (not in submitted/view mode)
    if (ghanaCardFront) {
      if (!ghanaCardFront.files || ghanaCardFront.files.length === 0) {
        e.preventDefault();
        e.stopPropagation();
        currentTab = 4; // Documents tab
        showTab(currentTab);
        ghanaCardFront.style.outline = '2px solid #e53935';
        setTimeout(() => ghanaCardFront.focus(), 100);
        alert('Please upload Ghana Card (Front). This is required.');
        return false;
      }
    }
    
    if (ghanaCardBack) {
      if (!ghanaCardBack.files || ghanaCardBack.files.length === 0) {
        e.preventDefault();
        e.stopPropagation();
        currentTab = 4; // Documents tab
        showTab(currentTab);
        ghanaCardBack.style.outline = '2px solid #e53935';
        setTimeout(() => ghanaCardBack.focus(), 100);
        alert('Please upload Ghana Card (Back). This is required.');
        return false;
      }
    }
    
    if (passportPicture) {
      if (!passportPicture.files || passportPicture.files.length === 0) {
        e.preventDefault();
        e.stopPropagation();
        currentTab = 4; // Documents tab
        showTab(currentTab);
        passportPicture.style.outline = '2px solid #e53935';
        setTimeout(() => passportPicture.focus(), 100);
        alert('Please upload Passport Picture. This is required.');
        return false;
      }
    }
    
    // THIRD: Validate all tabs before submission
    for (let i = 0; i < totalTabs; i++) {
      const tabContent = document.getElementById(['personal', 'education', 'programs', 'employment', 'documents'][i]);
      if (!tabContent) continue;
      
      const requiredFields = tabContent.querySelectorAll('input[required], select[required], textarea[required]');
      
      for (let field of requiredFields) {
        // Handle file inputs separately
        if (field.type === 'file') {
          if (!field.files || field.files.length === 0) {
            e.preventDefault();
            e.stopPropagation();
            // Switch to the tab with missing fields
            currentTab = i;
            showTab(currentTab);
            setTimeout(() => field.focus(), 100);
            field.style.outline = '2px solid #e53935';
            alert(`Please upload the required file: ${field.previousElementSibling?.textContent || 'Required file'}`);
            return false;
          }
        } else if (!field.value.trim()) {
          e.preventDefault();
          e.stopPropagation();
          // Switch to the tab with missing fields
          currentTab = i;
          showTab(currentTab);
          setTimeout(() => field.focus(), 100);
          alert(`Please fill in all required fields in the ${getTabName(i)} section.`);
          return false;
        }
      }
    }
    
    // Employment validation
    const tbody = document.getElementById('employmentBody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const company = row.querySelector('input[name^="employment"][name$="[company]"]');
      const duration = row.querySelector('input[name^="employment"][name$="[duration]"]');
      const fileInput = row.querySelector('input[type="file"]');
      const hasText = (company && company.value.trim()) || (duration && duration.value.trim());
      const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
      [company, duration, fileInput].forEach(el => { if (el) el.style.outline = ''; });
      if (hasText && !hasFile) {
        e.preventDefault();
        if (fileInput) fileInput.style.outline = '2px solid #e53935';
        alert('Please upload the Appointment Letter for each employment row that has Company/Duration filled.');
        return false;
      }
    }
  });
});

// Print Application Function
function printApplication() {
  // Hide the print button and chat widget before printing
  const printBtn = event.target.closest('button');
  const chatWidget = document.getElementById('tawkchat-container') || document.querySelector('.tawk-chat-widget') || document.querySelector('[id^="tawk"]');
  
  if (printBtn) printBtn.style.display = 'none';
  if (chatWidget) chatWidget.style.display = 'none';
  
  // Print the page
  window.print();
  
  // Restore after print
  setTimeout(() => {
    if (printBtn) printBtn.style.display = 'flex';
    if (chatWidget) chatWidget.style.display = 'block';
  }, 100);
}

// Add print-specific styles
const style = document.createElement('style');
style.textContent = `
  @media print {
    /* Hide unnecessary elements */
    .alert, button, .btn-link, .inline-options, .tawk-chat-widget, [id^="tawk"] {
      display: none !important;
    }
    
    /* Remove margins and padding for print */
    body {
      margin: 0;
      padding: 20px;
      font-size: 12pt;
    }
    
    /* Ensure proper page breaks */
    fieldset {
      page-break-inside: avoid;
    }
    
    /* Make tables fit nicely */
    table {
      page-break-inside: auto;
      width: 100%;
    }
    
    tr {
      page-break-inside: avoid;
      page-break-after: auto;
    }
    
    /* Hide file input fields */
    input[type="file"] {
      display: none !important;
    }
    
    /* Show uploaded file previews */
    .mb-2 {
      display: block !important;
      margin: 10px 0;
    }
    
    .mb-2 a {
      display: none !important;
    }
    
    /* Show images in print */
    .mb-2 img {
      display: block !important;
      max-width: 300px;
      max-height: 400px;
      border: 1px solid #ddd;
      margin: 10px 0;
    }
    
    /* Make readonly inputs look cleaner */
    input[readonly], textarea[readonly] {
      border: 1px solid #ddd !important;
      background: white !important;
    }
    
    /* Better heading styles */
    h1 {
      font-size: 18pt;
      text-align: center;
      margin-bottom: 20px;
    }
    
    legend {
      font-size: 14pt;
      font-weight: bold;
      margin-bottom: 10px;
    }
    
    /* Adjust container for print */
    .container {
      max-width: 100% !important;
      width: 100% !important;
      padding: 0 !important;
    }
  }
`;
document.head.appendChild(style);
</script>

<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/68e59523a72e351952185ab1/1j70ct4nl';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->

@endsection

