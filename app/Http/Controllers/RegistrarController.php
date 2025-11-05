<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Department;

class RegistrarController extends Controller
{
    public function dashboard()
    {
        // Get all applications that can be reviewed by registrar (hod_status = approved, exclude drafts)
        $pendingApplications = Application::with(['user', 'department', 'examRecords.subjects'])
            ->where('hod_status', 'approved')
            ->where('registrar_status', 'pending')
            ->where('status', '!=', 'draft')
            ->orderBy('hod_reviewed_at', 'desc')
            ->get();

        // Get all applications that have been reviewed by registrar (exclude drafts)
        $reviewedApplications = Application::with(['user', 'department', 'examRecords.subjects'])
            ->whereIn('registrar_status', ['approved', 'rejected'])
            ->where('status', '!=', 'draft')
            ->orderBy('registrar_reviewed_at', 'desc')
            ->get();

        // Get all applications for overview (registrar can see all, but exclude drafts)
        $allApplications = Application::with(['user', 'department', 'examRecords.subjects'])
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics (exclude drafts)
        $stats = [
            'total_pending' => $pendingApplications->count(),
            'total_reviewed' => $reviewedApplications->count(),
            'total_applications' => $allApplications->count(),
            'approved_today' => Application::where('registrar_status', 'approved')
                ->where('status', '!=', 'draft')
                ->whereDate('registrar_reviewed_at', today())
                ->count(),
            'rejected_today' => Application::where('registrar_status', 'rejected')
                ->where('status', '!=', 'draft')
                ->whereDate('registrar_reviewed_at', today())
                ->count(),
        ];

        return view('registrar.dashboard', compact('pendingApplications', 'reviewedApplications', 'allApplications', 'stats'));
    }

    public function showApplication(Application $application)
    {
        // Prevent registrar from viewing draft applications
        if ($application->status === 'draft') {
            abort(403, 'You cannot view draft applications.');
        }
        
        $application->load(['user', 'department', 'admissionForm']);
        $examRecords = \App\Models\ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        
        return view('registrar.application.show', compact('application', 'examRecords'));
    }

    public function approveApplication(Request $request, Application $application)
    {
        // Prevent registrar from approving draft applications
        if ($application->status === 'draft') {
            abort(403, 'You cannot approve draft applications.');
        }
        
        // Ensure registrar can only approve applications that have been approved by HOD
        if ($application->hod_status !== 'approved') {
            return redirect()->route('registrar.dashboard')
                ->with('error', 'You can only approve applications that have been approved by HOD.');
        }

        $request->validate([
            'comments' => 'nullable|string|max:1000'
        ]);

        $application->update([
            'registrar_status' => 'approved',
            'registrar_comments' => $request->comments,
            'registrar_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('registrar.dashboard')
            ->with('success', 'Application approved successfully.');
    }

    public function rejectApplication(Request $request, Application $application)
    {
        // Prevent registrar from rejecting draft applications
        if ($application->status === 'draft') {
            abort(403, 'You cannot reject draft applications.');
        }
        
        // Ensure registrar can only reject applications that have been approved by HOD
        if ($application->hod_status !== 'approved') {
            return redirect()->route('registrar.dashboard')
                ->with('error', 'You can only reject applications that have been approved by HOD.');
        }

        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $application->update([
            'registrar_status' => 'rejected',
            'registrar_comments' => $request->comments,
            'registrar_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('registrar.dashboard')
            ->with('success', 'Application rejected.');
    }
}
