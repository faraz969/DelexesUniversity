<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'application_id',
        'full_name','dob','age','gender','birth_place','marital_status','nationality','passport_number','mailing_address','emergency_contact','telephone','email','hostel_required','has_disability','disability_details',
        'prog_eng','prog_eng_mode','prog_focis','prog_focis_mode','prog_business','prog_business_mode','pref1','pref2','pref3',
        'entry_wassce','entry_sssce','entry_ib','entry_transfer','entry_other','other_entry_detail','preferred_session','preferred_campus','intake_option',
        'english_level','mother_tongue','other_languages',
        'institutions','employment','uploads',
    ];

    protected $casts = [
        'dob' => 'date',
        'hostel_required' => 'boolean',
        'has_disability' => 'boolean',
        'entry_wassce' => 'boolean',
        'entry_sssce' => 'boolean',
        'entry_ib' => 'boolean',
        'entry_transfer' => 'boolean',
        'entry_other' => 'boolean',
        'institutions' => 'array',
        'employment' => 'array',
        'uploads' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
