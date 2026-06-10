<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{

    public $timestamps = false;

    protected $table = 'admins';

    protected $fillable = [
        'user_name',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Required by JWTSubject — the key used to identify the token subject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Required by JWTSubject — extra claims to embed in the token payload
    public function getJWTCustomClaims()
    {
        return [];
    }
}