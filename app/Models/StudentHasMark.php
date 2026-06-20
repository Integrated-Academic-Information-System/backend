<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHasMark extends Model
{
    protected $table = 'student_has_marks';

    protected $fillable = [
        'student_id',
        'student_reg_no',
        'marks_id',
        'grade_has_sub_grade_id',
        'subject_id',
        'term_id',
        'exam_year_id'
    ];
}