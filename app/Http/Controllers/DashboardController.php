<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\AdmissionForm;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\UploadedFile;
use App\Models\ExamRecord;
use App\Models\ExamSubjectGrade;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->load('formType');
        $application = $user->applications()->latest()->first();
        return view('portal.dashboard', compact('user', 'application'));
    }

    public function applicationForm()
    {
        $user = Auth::user();
        $application = $user->applications()->latest()->first();
        $submitted = $application && in_array($application->status, ['submitted','successful','not_successful']);
        $action = $submitted ? null : route('portal.application.submit');

        $prefill = [];
        $uploadedFiles = [];
        if ($application && is_array($application->data)) {
            $prefill = $application->data;
            $uploadedFiles = $application->data['_files'] ?? [];
            unset($prefill['_files']);
        }

        // Fetch departments with their active programs
        $departments = Department::where('is_active', true)
            ->with(['activePrograms' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        // Load exam records for submitted view
        $examRecords = [];
        if ($submitted && $application) {
            $examRecords = \App\Models\ExamRecord::with('subjects')
                ->where('application_id', $application->id)
                ->get();
        }

        return view('admission.form', compact('action', 'prefill', 'submitted', 'uploadedFiles', 'departments', 'application', 'examRecords'));
    }

    public function applicationSave(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $application = $user->applications()->latest()->first();
        if (! $application) {
            $application = new Application();
            $application->user_id = $user->id;
            $application->application_number = (string) random_int(1000000000, 1999999999);
            $application->academic_year = '2025/2026, September';
            $application->form_type = $request->input('form_type', 'undergraduate');
        }
        // Merge incoming draft data with any existing saved data to avoid losing previously filled fields
        $existing = is_array($application->data) ? $application->data : [];
        $application->data = array_replace_recursive($existing, $data);
        $application->status = 'draft';
        $application->save();
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Saved as draft');
    }

    public function applicationSubmit(Request $request)
    {
        $user = Auth::user();
        $application = $user->applications()->latest()->first();
        if (! $application) {
            $application = new Application();
            $application->user_id = $user->id;
            $application->application_number = (string) random_int(1000000000, 1999999999);
            $application->academic_year = '2025/2026, September';
            $application->form_type = 'undergraduate';
        }

        $payload = $request->except(['_token']);
        $files = $this->storeUploadedFilesRecursively($request->allFiles(), $user->id);
        $payload['_files'] = $files;

        // Determine departments based on selected programs
        $departmentIds = $this->determineDepartmentsFromApplication($payload);
        $primaryDepartmentId = !empty($departmentIds) ? $departmentIds[0] : null;

        $application->data = $payload;
        $application->status = 'submitted';
        $application->department_id = $primaryDepartmentId; // Keep for backward compatibility
        $application->department_ids = $departmentIds; // Store all departments
        $application->hod_status = 'pending';
        $application->president_status = 'pending';
        $application->registrar_status = 'pending';
        $application->save();

        // Save to structured AdmissionForm
        $form = AdmissionForm::updateOrCreate(
            ['user_id' => $user->id, 'application_id' => $application->id],
            [
                'full_name' => $request->input('full_name', $user->name),
                'dob' => $request->input('dob'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender'),
                'birth_place' => $request->input('birth_place'),
                'marital_status' => $request->input('marital_status'),
                'nationality' => $request->input('nationality'),
                'passport_number' => $request->input('passport_number'),
                'mailing_address' => $request->input('mailing_address'),
                'emergency_contact' => $request->input('emergency_contact'),
                'telephone' => $request->input('telephone'),
                'email' => $request->input('email', $user->email),
                'hostel_required' => $request->input('hostel_required') === 'Yes',
                'has_disability' => $request->input('has_disability') === 'Yes',
                'disability_details' => $request->input('disability_details'),
                'prog_eng' => $request->input('prog_eng'),
                'prog_eng_mode' => $request->input('prog_eng_mode'),
                'prog_focis' => $request->input('prog_focis'),
                'prog_focis_mode' => $request->input('prog_focis_mode'),
                'prog_business' => $request->input('prog_business'),
                'prog_business_mode' => $request->input('prog_business_mode'),
                'pref1' => $request->input('pref1'),
                'pref2' => $request->input('pref2'),
                'pref3' => $request->input('pref3'),
                'entry_wassce' => (bool) $request->input('entry_wassce'),
                'entry_sssce' => (bool) $request->input('entry_sssce'),
                'entry_ib' => (bool) $request->input('entry_ib'),
                'entry_transfer' => (bool) $request->input('entry_transfer'),
                'entry_other' => (bool) $request->input('entry_other'),
                'other_entry_detail' => $request->input('other_entry_detail'),
                'preferred_session' => $request->input('preferred_session'),
                'preferred_campus' => $request->input('preferred_campus'),
                'intake_option' => $request->input('intake_option'),
                'english_level' => $request->input('english_level'),
                'mother_tongue' => $request->input('mother_tongue'),
                'other_languages' => $request->input('other_languages'),
                'institutions' => $request->input('institutions'),
                'employment' => $request->input('employment'),
                'uploads' => $files,
            ]
        );

        // Persist Exam Sections (if provided)
        $examSections = $request->input('exam_sections', []);
        if (is_array($examSections)) {
            // Remove previous records for this application to keep data in sync
            ExamRecord::where('application_id', $application->id)->delete();
            foreach ($examSections as $section) {
                if (!is_array($section)) { continue; }
                $examRecord = ExamRecord::create([
                    'application_id' => $application->id,
                    'exam_type' => $section['exam_type'] ?? null,
                    'sitting_exam' => $section['sitting_exam'] ?? null,
                    'year' => $section['year'] ?? null,
                    'index_number' => $section['index_number'] ?? null,
                ]);

                $subjects = $section['subjects'] ?? [];
                if (is_array($subjects)) {
                    $rows = [];
                    foreach ($subjects as $row) {
                        if (!is_array($row)) { continue; }
                        $rows[] = new ExamSubjectGrade([
                            'subject' => $row['subject'] ?? null,
                            'grade_letter' => $row['grade_letter'] ?? null,
                            'grade_number' => isset($row['grade_number']) && $row['grade_number'] !== '' ? (int)$row['grade_number'] : null,
                            'is_best_six' => !empty($row['is_best_six']),
                        ]);
                    }
                    if (!empty($rows)) {
                        $examRecord->subjects()->saveMany($rows);
                    }
                }
            }
        }
        return redirect()->route('portal.dashboard')->with('status', 'Application submitted');
    }

    /**
     * Determine all departments based on selected programs in the application
     */
    private function determineDepartmentsFromApplication($data)
    {
        $departmentIds = [];
        
        // Get all departments with their programs
        $departments = Department::with('programs')->get();
        
        // Check each department's programs against the application data
        foreach ($departments as $department) {
            foreach ($department->programs as $program) {
                // Check if this program is selected in any of the program fields
                foreach ($data as $key => $value) {
                    if (strpos($key, 'prog_') === 0 && $value === $program->name) {
                        $departmentIds[] = $department->id;
                        break 2; // Break out of both inner loops for this department
                    }
                }
            }
        }
        
        // Remove duplicates
        $departmentIds = array_unique($departmentIds);
        
        // If no specific programs found, return the first department as default
        if (empty($departmentIds)) {
            $firstDepartment = Department::first();
            if ($firstDepartment) {
                $departmentIds = [$firstDepartment->id];
            }
        }
        
        return $departmentIds;
    }

    /**
     * Recursively store uploaded files preserving nested array structure.
     *
     * @param mixed $node
     * @param int $userId
     * @return mixed
     */
    private function storeUploadedFilesRecursively($node, int $userId)
    {
        if ($node instanceof UploadedFile) {
            return $node->store('applications/'.$userId, 'public');
        }

        if (is_array($node)) {
            $result = [];
            foreach ($node as $key => $child) {
                $stored = $this->storeUploadedFilesRecursively($child, $userId);
                if ($stored !== null && $stored !== []) {
                    $result[$key] = $stored;
                }
            }
            return $result;
        }

        return null;
    }

    public function results()
    {
        $user = Auth::user();
        $application = $user->applications()->latest()->first();
        return view('portal.results', compact('application'));
    }

    public function applicationPrint()
    {
        $user = Auth::user();
        $application = $user->applications()->latest()->first();
        $submitted = true;
        $action = null;
        $prefill = [];
        $uploadedFiles = [];
        if ($application && is_array($application->data)) {
            $prefill = $application->data;
            $uploadedFiles = $application->data['_files'] ?? [];
            unset($prefill['_files']);
        }
        return view('admission.form', compact('action', 'prefill', 'submitted', 'uploadedFiles'));
    }
}
