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
        'reg_date',
        'leave_date',
        'mobile_number',
        'email',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

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