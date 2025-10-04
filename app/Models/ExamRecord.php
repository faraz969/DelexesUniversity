<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'exam_type',
        'sitting_exam',
        'year',
        'index_number',
    ];

    public function subjects()
    {
        return $this->hasMany(ExamSubjectGrade::class);
    }
}

