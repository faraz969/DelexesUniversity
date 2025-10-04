<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'local_price',
        'international_price',
        'conversion_rate',
        'description',
        'is_active'
    ];

    protected $casts = [
        'local_price' => 'decimal:2',
        'international_price' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    // Scope for active form types
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
