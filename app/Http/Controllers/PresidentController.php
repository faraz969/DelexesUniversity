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
        // Get all applications that are pending president review (approved by HOD)
        $pendingApplications = Application::with(['user', 'department'])
            ->where('hod_status', 'approved')
            ->where('president_status', 'pending')
            ->orderBy('hod_reviewed_at', 'desc')
            ->get();

        // Get all applications that have been reviewed by president
        $reviewedApplications = Application::with(['user', 'department'])
            ->whereIn('president_status', ['approved', 'rejected'])
            ->orderBy('president_reviewed_at', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'total_pending' => $pendingApplications->count(),
            'total_reviewed' => $reviewedApplications->count(),
            'approved_today' => Application::where('president_status', 'approved')
                ->whereDate('president_reviewed_at', today())
                ->count(),
            'rejected_today' => Application::where('president_status', 'rejected')
                ->whereDate('president_reviewed_at', today())
                ->count(),
        ];

        return view('president.dashboard', compact('pendingApplications', 'reviewedApplications', 'stats'));
    }

    public function showApplication(Application $application)
    {
        $application->load(['user', 'department', 'admissionForm']);
        $examRecords = \App\Models\ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        
        return view('president.application.show', compact('application', 'examRecords'));
    }

    public function approveApplication(Request $request, Application $application)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000'
        ]);

        $application->update([
            'president_status' => 'approved',
            'president_comments' => $request->comments,
            'president_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('president.dashboard')
            ->with('success', 'Application approved successfully.');
    }

    public function rejectApplication(Request $request, Application $application)
    {
        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $application->update([
            'president_status' => 'rejected',
            'president_comments' => $request->comments,
            'president_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('president.dashboard')
            ->with('success', 'Application rejected.');
    }
}
