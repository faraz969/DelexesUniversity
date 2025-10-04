<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Department;

class HODController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $department = $user->department;
        
        if (!$department) {
            return redirect()->route('admin.dashboard')->with('error', 'No department assigned to your account.');
        }

        // Get applications for this department that are pending HOD review
        $pendingApplications = Application::with(['user', 'department'])
            ->where(function($query) use ($department) {
                $query->where('department_id', $department->id)
                      ->orWhereJsonContains('department_ids', $department->id);
            })
            ->where('hod_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get applications that have been reviewed by HOD
        $reviewedApplications = Application::with(['user', 'department'])
            ->where(function($query) use ($department) {
                $query->where('department_id', $department->id)
                      ->orWhereJsonContains('department_ids', $department->id);
            })
            ->whereIn('hod_status', ['approved', 'rejected'])
            ->orderBy('hod_reviewed_at', 'desc')
            ->get();

        return view('hod.dashboard', compact('department', 'pendingApplications', 'reviewedApplications'));
    }

    public function showApplication(Application $application)
    {
        $user = Auth::user();
        
        // Ensure HOD can only view applications from their department
        if (!$application->belongsToDepartment($user->department_id)) {
            abort(403, 'You can only view applications from your department.');
        }

        $application->load(['user', 'department', 'admissionForm']);
        $examRecords = \App\Models\ExamRecord::with('subjects')
            ->where('application_id', $application->id)
            ->get();
        
        return view('hod.application.show', compact('application', 'examRecords'));
    }

    public function approveApplication(Request $request, Application $application)
    {
        $user = Auth::user();
        
        // Ensure HOD can only approve applications from their department
        if (!$application->belongsToDepartment($user->department_id)) {
            abort(403, 'You can only approve applications from your department.');
        }

        $request->validate([
            'comments' => 'nullable|string|max:1000'
        ]);

        $application->update([
            'hod_status' => 'approved',
            'hod_comments' => $request->comments,
            'hod_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('hod.dashboard')
            ->with('success', 'Application approved successfully.');
    }

    public function rejectApplication(Request $request, Application $application)
    {
        $user = Auth::user();
        
        // Ensure HOD can only reject applications from their department
        if (!$application->belongsToDepartment($user->department_id)) {
            abort(403, 'You can only reject applications from your department.');
        }

        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $application->update([
            'hod_status' => 'rejected',
            'hod_comments' => $request->comments,
            'hod_reviewed_at' => now(),
        ]);

        // Update main status based on workflow
        $application->updateMainStatus();

        return redirect()->route('hod.dashboard')
            ->with('success', 'Application rejected.');
    }
}
