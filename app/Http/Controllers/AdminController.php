<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\AdmissionForm;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    public function dashboard()
    {
        $applications = Application::with(['user', 'examRecords.subjects'])->latest()->paginate(20);
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
}

