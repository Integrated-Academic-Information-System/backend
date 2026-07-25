<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['student_id', 'title', 'body', 'read'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}