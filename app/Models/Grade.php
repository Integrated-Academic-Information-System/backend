<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model {
    protected $fillable = ['name', 'level'];

    public function students() {
        return $this->hasMany(Student::class);
    }

    public function subjects() {
        return $this->hasMany(Subject::class);
    }

    public function teachers() {
        return $this->hasMany(Teacher::class);
    }
}