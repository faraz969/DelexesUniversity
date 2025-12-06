<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\AdmissionForm;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Application::with(['user', 'examRecords.subjects']);
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('application_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('academic_year', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', '%' . $searchTerm . '%')
                                 ->orWhere('email', 'like', '%' . $searchTerm . '%')
                                 ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                                 ->orWhere('serial_number', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        $applications = $query->latest()->paginate(20)->withQueryString();
        
        return view('admin.dashboard', compact('applications'));
    }

    public function show($id)
    {
        $application = Application::with(['user'])->findOrFail($id);
        $form = AdmissionForm::where('application_id', $application->id)->first();
        $examRecords = \App\Models\ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        return view('admin.show', compact('application', 'form', 'examRecords'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:successful,not_successful'
        ]);
        $application = Application::findOrFail($id);
        $application->status = $request->input('status') === 'successful' ? 'successful' : 'not_successful';
        $application->save();
        return Redirect::route('admin.dashboard')->with('status', 'Application status updated');
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        
        // Delete related records
        // Get exam records first
        $examRecords = \App\Models\ExamRecord::where('application_id', $application->id)->get();
        
        // Delete exam subject grades for each exam record
        foreach ($examRecords as $examRecord) {
            \App\Models\ExamSubjectGrade::where('exam_record_id', $examRecord->id)->delete();
        }
        
        // Delete exam records
        \App\Models\ExamRecord::where('application_id', $application->id)->delete();
        
        // Delete admission form
        AdmissionForm::where('application_id', $application->id)->delete();
        
        // Delete the application
        $application->delete();
        
        return Redirect::route('admin.dashboard')
            ->with('status', 'Application deleted successfully.');
    }
}

