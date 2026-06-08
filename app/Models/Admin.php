<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens;

    public $timestamps = false;

    protected $table = 'admins';

    protected $fillable = [
        'user_name',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}