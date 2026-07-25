<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    protected $table = 'marks';

    protected $fillable = [
        'student_id',
        'subject_id',
        'term_id',
        'grade_id',
        'marks_obtained',
        'total_marks',
        'grade_letter',
    ];

    protected $casts = [
        'marks_obtained' => 'float',
        'total_marks'    => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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