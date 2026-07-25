<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Teacher extends Authenticatable implements JWTSubject
{
    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'user_name',
        'password',
        'mobile_number',
        'email',
        'access_status',
        'role_status',
        'is_class_teacher',
        'is_subject_teacher',
    ];

    protected $hidden = [
        'password',
    ];

    // Required for JWT students can also login
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
