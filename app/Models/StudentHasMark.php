<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentHasMark extends Model
{
    protected $table = 'student_has_marks';

    protected $fillable = [
        'student_id',
        'student_reg_no',
        'marks_id',
        'grade_id',
        'subject_id',
        'term_id',
        'exam_year_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function mark(): BelongsTo
    {
        return $this->belongsTo(Mark::class, 'marks_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}