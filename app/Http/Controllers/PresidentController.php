<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Department;

class PresidentController extends Controller
{
    public function dashboard()
    {
        // Show all applications to the President
        $applications = Application::with(['user', 'department'])
            ->latest()
            ->paginate(25);

        return view('president.dashboard', compact('applications'));
    }

    public function showApplication(Application $application)
    {
        $application->load(['user', 'department', 'admissionForm']);
        $examRecords = \App\Models\ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        
        return view('president.application.show', compact('application', 'examRecords'));
    }

    // Comment-only endpoint (no status change)
    public function commentApplication(Request $request, Application $application)
    {
        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $application->update([
            'president_comments' => $request->comments,
        ]);

        return redirect()->route('president.dashboard')
            ->with('success', 'Comment saved.');
    }
}
