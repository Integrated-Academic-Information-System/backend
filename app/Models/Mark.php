<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mark extends Model {
    protected $fillable = [
        'student_id', 'subject_id', 'term_id',
        'academic_year_id', 'marks', 'status', 'submitted_by'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function term() {
        return $this->belongsTo(Term::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function submittedBy() {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}