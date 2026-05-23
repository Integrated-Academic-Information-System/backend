<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model {
    protected $fillable = ['user_id', 'index_no', 'grade_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function marks() {
        return $this->hasMany(Mark::class);
    }
}