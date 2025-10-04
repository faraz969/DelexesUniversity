<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubjectGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_record_id',
        'subject',
        'grade_letter',
        'grade_number',
        'is_best_six',
    ];

    protected $casts = [
        'is_best_six' => 'boolean',
    ];

    public function examRecord()
    {
        return $this->belongsTo(ExamRecord::class);
    }
}

