<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        
        // If application is submitted, also load data from AdmissionForm for the new address fields
        if ($submitted && $application) {
            $admissionForm = AdmissionForm::where('application_id', $application->id)->first();
            if ($admissionForm) {
                $prefill['street_address'] = $admissionForm->street_address ?? ($prefill['street_address'] ?? '');
                $prefill['post_code'] = $admissionForm->post_code ?? ($prefill['post_code'] ?? '');
                $prefill['city'] = $admissionForm->city ?? ($prefill['city'] ?? '');
                $prefill['country'] = $admissionForm->country ?? ($prefill['country'] ?? '');
            }
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
        
        // If application is already submitted, keep it as submitted but allow personal data updates
        if ($application->status !== 'submitted' && $application->status !== 'successful' && $application->status !== 'not_successful') {
            $application->status = 'draft';
        }
        
        $application->save();
        
        // Update AdmissionForm if it exists (for submitted applications)
        if ($application->id) {
            $form = AdmissionForm::where('application_id', $application->id)->first();
            if ($form) {
                $form->update([
                    'street_address' => $request->input('street_address'),
                    'post_code' => $request->input('post_code'),
                    'city' => $request->input('city'),
                    'country' => $request->input('country'),
                    'full_name' => $request->input('full_name', $form->full_name),
                    'dob' => $request->input('dob', $form->dob),
                    'age' => $request->input('age', $form->age),
                    'gender' => $request->input('gender', $form->gender),
                    'birth_place' => $request->input('birth_place', $form->birth_place),
                    'marital_status' => $request->input('marital_status', $form->marital_status),
                    'nationality' => $request->input('nationality', $form->nationality),
                    'passport_number' => $request->input('passport_number', $form->passport_number),
                    'mailing_address' => $request->input('mailing_address', $form->mailing_address),
                    'emergency_contact' => $request->input('emergency_contact', $form->emergency_contact),
                    'telephone' => $request->input('telephone', $form->telephone),
                    'email' => $request->input('email', $form->email),
                    'hostel_required' => $request->input('hostel_required') === 'Yes' ? true : ($request->input('hostel_required') === 'No' ? false : $form->hostel_required),
                    'has_disability' => $request->input('has_disability') === 'Yes' ? true : ($request->input('has_disability') === 'No' ? false : $form->has_disability),
                    'disability_details' => $request->input('disability_details', $form->disability_details),
                ]);
            }
        }
        
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Personal data updated successfully');
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
                'street_address' => $request->input('street_address'),
                'post_code' => $request->input('post_code'),
                'city' => $request->input('city'),
                'country' => $request->input('country'),
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
        
        // Send SMS notification to user
        // Use telephone from application data if available, otherwise use user's phone
        $phoneNumber = $request->input('telephone') ?? $user->phone;
        if ($phoneNumber) {
            $username = $request->input('full_name', $user->name);
            $this->sendApplicationSMS($phoneNumber, $username);
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
        
        if (!$application) {
            return redirect()->route('portal.dashboard')->with('error', 'No application found.');
        }
        
        $prefill = [];
        $uploadedFiles = [];
        if ($application && is_array($application->data)) {
            $prefill = $application->data;
            $uploadedFiles = $application->data['_files'] ?? [];
            unset($prefill['_files']);
        }
        
        // Load exam records for the declaration
        $examRecords = ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        
        // Load admission form data for additional fields
        $admissionForm = AdmissionForm::where('application_id', $application->id)->first();
        if ($admissionForm) {
            $prefill['street_address'] = $admissionForm->street_address ?? ($prefill['street_address'] ?? '');
            $prefill['post_code'] = $admissionForm->post_code ?? ($prefill['post_code'] ?? '');
            $prefill['city'] = $admissionForm->city ?? ($prefill['city'] ?? '');
            $prefill['country'] = $admissionForm->country ?? ($prefill['country'] ?? '');
        }
        
        return view('admission.declaration', compact('application', 'prefill', 'uploadedFiles', 'examRecords', 'user'));
    }

    /**
     * Send SMS notification when application is submitted
     */
    private function sendApplicationSMS($phone, $username)
    {
        $message = "Thank you {$username}, your application has been received by DUC. Your application will be processed and the decision will be communicated to you at the appropriate time.";
        
        // Clean phone number (remove any non-numeric characters except +)
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Convert to format without + for Nalo (e.g., +233249318768 -> 0249318768)
        $naloPhone = $cleanPhone;
        if (strpos($cleanPhone, '+233') === 0) {
            $naloPhone = '0' . substr($cleanPhone, 4); // Replace +233 with 0
        } elseif (strpos($cleanPhone, '233') === 0) {
            $naloPhone = '0' . substr($cleanPhone, 3); // Replace 233 with 0
        }
        
        try {
            // Primary: Try Nalo SMS API
            $naloKey = env('NALO_SMS_KEY', 'LNMKky07fqvxVO6IK33I7UvuWMVXDR_sZnf8bDRnG7qu2ErL3vTM1farB5UYw26L');
            $naloSenderId = env('NALO_SENDER_ID', 'DELEXESUC');
            
            Log::info('Attempting Application SMS via Nalo API', [
                'phone' => $naloPhone,
                'original_phone' => $cleanPhone,
                'username' => $username,
            ]);
            
            $naloResponse = Http::timeout(10)
                ->post('https://sms.nalosolutions.com/smsbackend/Resl_Nalo/send-message/', [
                    'key' => $naloKey,
                    'msisdn' => $naloPhone,
                    'message' => $message,
                    'sender_id' => $naloSenderId
                ]);

            // Log the response for debugging
            Log::info('Nalo SMS API Response', [
                'phone' => $naloPhone,
                'status' => $naloResponse->status(),
                'response' => $naloResponse->body(),
            ]);

            // Check if Nalo was successful
            if ($naloResponse->successful()) {
                $responseData = $naloResponse->json();
                // Nalo returns status codes like "1701" for success
                // Check if status exists and is not an error code (errors are usually 17xx range except 1701)
                if (isset($responseData['status']) && isset($responseData['job_id'])) {
                    // If job_id is present, SMS was queued/sent successfully
                    Log::info('Application SMS sent successfully via Nalo', [
                        'job_id' => $responseData['job_id'],
                        'status_code' => $responseData['status']
                    ]);
                    return;
                }
            }
            
            // If Nalo failed, log and fall through to backup
            Log::warning('Nalo SMS API failed or returned error, trying backup Arkesel API');

        } catch (\Exception $e) {
            Log::error('Nalo SMS API Exception', [
                'phone' => $naloPhone,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: Try Arkesel SMS API
        try {
            $arkeselApiKey = env('ARKESEL_SMS_KEY', 'Ok1GNWlYWFB0VHI1NHJZUUQ=');
            $arkeselSenderId = env('ARKESEL_SENDER_ID', 'UNIVERSITY');
            
            Log::info('Attempting Application SMS via Arkesel API (Backup)', [
                'phone' => $cleanPhone,
            ]);
            
            $arkeselResponse = Http::timeout(10)
                ->get('https://sms.arkesel.com/sms/api', [
                'action' => 'send-sms',
                    'api_key' => $arkeselApiKey,
                'to' => $cleanPhone,
                    'from' => $arkeselSenderId,
                'sms' => $message
            ]);

            // Log the response for debugging
            Log::info('Arkesel SMS API Response (Backup)', [
                'phone' => $cleanPhone,
                'response' => $arkeselResponse->body(),
                'status' => $arkeselResponse->status()
            ]);

        } catch (\Exception $e) {
            Log::error('Both SMS APIs failed for application submission', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }
}
