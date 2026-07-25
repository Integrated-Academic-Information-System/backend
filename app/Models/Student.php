<?php

namespace App\Models;

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
        'grade_has_sub_grade_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_has_sub_grade_id', 'id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_has_subject')->withTimestamps();
    }

    // Required for JWT if students can also login
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
