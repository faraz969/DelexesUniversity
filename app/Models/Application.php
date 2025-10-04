<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_number',
        'academic_year',
        'form_type',
        'applicant_type',
        'status',
        'data',
        'department_id',
        'department_ids',
        'hod_status',
        'president_status',
        'registrar_status',
        'hod_comments',
        'president_comments',
        'registrar_comments',
        'hod_reviewed_at',
        'president_reviewed_at',
        'registrar_reviewed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'department_ids' => 'array',
        'hod_reviewed_at' => 'datetime',
        'president_reviewed_at' => 'datetime',
        'registrar_reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function admissionForm()
    {
        return $this->hasOne(AdmissionForm::class);
    }

    /**
     * Check if this application belongs to a specific department
     */
    public function belongsToDepartment($departmentId)
    {
        // Check the legacy department_id field first
        if ($this->department_id == $departmentId) {
            return true;
        }
        
        // Check the new department_ids array
        if ($this->department_ids && is_array($this->department_ids)) {
            return in_array($departmentId, $this->department_ids);
        }
        
        return false;
    }

    /**
     * Get all department IDs associated with this application
     */
    public function getAllDepartmentIds()
    {
        $ids = [];
        
        // Add legacy department_id if it exists
        if ($this->department_id) {
            $ids[] = $this->department_id;
        }
        
        // Add department_ids from the array
        if ($this->department_ids && is_array($this->department_ids)) {
            $ids = array_merge($ids, $this->department_ids);
        }
        
        // Remove duplicates and return
        return array_unique($ids);
    }

    // Workflow status methods
    public function isPendingHOD()
    {
        return $this->hod_status === 'pending';
    }

    public function isApprovedByHOD()
    {
        return $this->hod_status === 'approved';
    }

    public function isRejectedByHOD()
    {
        return $this->hod_status === 'rejected';
    }

    public function isPendingPresident()
    {
        return $this->president_status === 'pending' && $this->isApprovedByHOD();
    }

    public function isApprovedByPresident()
    {
        return $this->president_status === 'approved';
    }

    public function isRejectedByPresident()
    {
        return $this->president_status === 'rejected';
    }

    public function isPendingRegistrar()
    {
        return $this->registrar_status === 'pending' && $this->isApprovedByPresident();
    }

    public function isApprovedByRegistrar()
    {
        return $this->registrar_status === 'approved';
    }

    public function isRejectedByRegistrar()
    {
        return $this->registrar_status === 'rejected';
    }

    public function isFullyApproved()
    {
        return $this->isApprovedByHOD() && $this->isApprovedByPresident() && $this->isApprovedByRegistrar();
    }

    public function isRejected()
    {
        return $this->isRejectedByHOD() || $this->isRejectedByPresident() || $this->isRejectedByRegistrar();
    }

    public function getCurrentStageAttribute()
    {
        if ($this->isRejected()) {
            return 'rejected';
        }

        if ($this->isFullyApproved()) {
            return 'approved';
        }

        if ($this->isPendingHOD()) {
            return 'hod_pending';
        }

        if ($this->isPendingPresident()) {
            return 'president_pending';
        }

        if ($this->isPendingRegistrar()) {
            return 'registrar_pending';
        }

        return 'processing';
    }

    public function getStatusDisplayAttribute()
    {
        switch ($this->current_stage) {
            case 'hod_pending':
                return 'Pending HOD Review';
            case 'president_pending':
                return 'Pending President Review';
            case 'registrar_pending':
                return 'Pending Registrar Review';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * Update the main status based on workflow stages
     */
    public function updateMainStatus()
    {
        $newStatus = $this->current_stage;
        
        // Map workflow stages to main status values
        switch ($newStatus) {
            case 'approved':
                $this->status = 'successful';
                break;
            case 'rejected':
                $this->status = 'not_successful';
                break;
            case 'hod_pending':
            case 'president_pending':
            case 'registrar_pending':
            case 'processing':
                $this->status = 'submitted';
                break;
            default:
                $this->status = 'submitted';
        }
        
        $this->save();
    }
}
