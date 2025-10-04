<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'nationality',
        'form_type_id',
        'password',
        'pin',
        'serial_number',
        'role',
        'department_id',
        'pin_expires_at',
        'invoice_id',
        'payment'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'pin_expires_at' => 'datetime',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function formType()
    {
        return $this->belongsTo(FormType::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isHOD()
    {
        return $this->role === 'hod';
    }

    public function isRegistrar()
    {
        return $this->role === 'registrar';
    }

    public function isPresident()
    {
        return $this->role === 'president';
    }

    public function isStaff()
    {
        return in_array($this->role, ['admin', 'hod', 'registrar', 'president']);
    }

    public function getRoleDisplayAttribute()
    {
        switch($this->role) {
            case 'admin':
                return 'Administrator';
            case 'hod':
                return 'Head of Department';
            case 'registrar':
                return 'Registrar';
            case 'president':
                return 'President';
            case 'user':
                return 'Student';
            default:
                return ucfirst($this->role);
        }
    }
}
