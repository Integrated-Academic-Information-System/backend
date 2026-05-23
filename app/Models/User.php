<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'profile_photo'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function student() {
        return $this->hasOne(Student::class);
    }

    public function teacher() {
        return $this->hasOne(Teacher::class);
    }

    public function notifications() {
        return $this->hasMany(Notification::class);
    }
}