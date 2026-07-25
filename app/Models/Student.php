<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Student extends Authenticatable implements JWTSubject
{
    protected $table = 'students';

    protected $fillable = [
        'reg_no',
        'password',
        'name',
        'address',
        'dob',
        'reg_date',
        'leave_date',
        'mobile_number',
        'email',
        'status',
        'grade_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentHasMark::class);
    }
}