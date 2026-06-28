<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $fillable = ['name'];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_has_bucket_subject', 'grade_id', 'subject_id');
    }
}
