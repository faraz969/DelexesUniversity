<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class)->orderBy('sort_order');
    }

    public function activePrograms()
    {
        return $this->hasMany(Program::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function staff()
    {
        return $this->hasMany(User::class)->whereIn('role', ['admin', 'hod', 'registrar', 'president']);
    }
}
