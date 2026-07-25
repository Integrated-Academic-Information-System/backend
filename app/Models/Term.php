<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    protected $fillable = [
        'name',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}