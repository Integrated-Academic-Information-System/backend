<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function dashboardStats()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalSubjects = DB::table('subjects')->count();
        $totalUsers    = $totalStudents + $totalTeachers;

        return response()->json([
            'success' => true,
            'data'    => [
                'students'       => $totalStudents,
                'teachers'       => $totalTeachers,
                'subjects'       => $totalSubjects,
                'users'          => $totalUsers,
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_subjects' => $totalSubjects,
                'total_users'    => $totalUsers,
            ],
        ]);
    }

    public function grades()
    {
        return response()->json(['success' => true, 'data' => DB::table('grades')->select('id', 'name')->orderBy('name')->get()]);
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $search = $request->query('search');
        $users = collect();

        if (!$type || $type === 'student') {
            $students = Student::query()->with('grade:id,name')
                ->when($search, fn ($q) => $q->where(fn ($s) => $s->where('name', 'like', "%{$search}%")->orWhere('reg_no', 'like', "%{$search}%")))
                ->get()->map(fn ($student) => ['id' => "student/{$student->id}", 'type' => 'student', 'name' => $student->name, 'user_name' => $student->reg_no, 'class' => $student->grade?->name, 'status' => $student->status]);
            $users = $users->concat($students);
        }

        if (!$type || $type === 'teacher') {
            $teachers = Teacher::query()
                ->when($search, fn ($q) => $q->where(fn ($s) => $s->where('name', 'like', "%{$search}%")->orWhere('user_name', 'like', "%{$search}%")))
                ->get()->map(fn ($teacher) => ['id' => "teacher/{$teacher->id}", 'type' => 'teacher', 'name' => $teacher->name, 'user_name' => $teacher->user_name, 'class' => null, 'status' => $teacher->access_status]);
            $users = $users->concat($teachers);
        }

        return response()->json(['success' => true, 'data' => $users->values()]);
    }

    public function formData()
    {
        return response()->json(['success' => true, 'data' => [
            'grades' => DB::table('grades')->select('id', 'name')->orderBy('name')->get(),
            'subjects' => DB::table('subjects')->select('id', 'name', 'subject_code')->orderBy('name')->get(),
        ]]);
    }

    public function gradeSubjects(int $grade)
    {
        $subjects = DB::table('subjects')->join('grade_has_subject', 'subjects.id', '=', 'grade_has_subject.subject_id')
            ->where('grade_has_subject.grade_id', $grade)->select('subjects.id', 'subjects.name', 'subjects.subject_code')->distinct()->orderBy('subjects.name')->get();

        $gradeName = (string) DB::table('grades')->where('id', $grade)->value('name');
        $bucketQuery = DB::table('subjects')->join('subject_has_bucket_subject', 'subjects.id', '=', 'subject_has_bucket_subject.subject_id')
            ->join('bucket_subjects', 'bucket_subjects.id', '=', 'subject_has_bucket_subject.bucket_subject_id')
            ->select('subjects.id', 'subjects.name', 'subjects.subject_code')->distinct();
        if (preg_match('/Grade ([6-9])/', $gradeName)) $bucketQuery->where('bucket_subjects.name', 'Aesthetic (G6-9)');
        elseif (preg_match('/Grade (10|11)/', $gradeName)) $bucketQuery->whereIn('bucket_subjects.name', ['Category 01 (G10-11)', 'Category 02 (G10-11)', 'Category 03 (G10-11)']);
        elseif (str_contains($gradeName, 'Arts')) $bucketQuery->whereIn('bucket_subjects.name', ['Category 01 (G12-13 Arts)', 'Category 02 (G12-13 Arts)', 'Category 03 (G12-13 Arts)']);
        elseif (str_contains($gradeName, 'Commerce')) $bucketQuery->where('bucket_subjects.name', 'Commerce Core Electives');
        else $bucketQuery->whereRaw('1 = 0');
        $bucketSubjects = $bucketQuery->orderBy('subjects.name')->get();

        return response()->json(['success' => true, 'data' => $subjects->concat($bucketSubjects)->unique('id')->values()]);
    }

    public function storeStudent(Request $request)
    {
        $data = $this->validateStudent($request);
        return DB::transaction(function () use ($data) {
            $student = Student::create($this->studentAttributes($data));
            $student->subjects()->sync($data['subject_ids'] ?? []);
            $this->syncStudentBucketSubjects($student->id, (int) $data['grade_id'], $data['subject_ids'] ?? []);
            return response()->json(['success' => true, 'message' => 'Student created.', 'data' => $this->studentDetails($student->fresh())], 201);
        });
    }

    public function showUser(string $type, string $id)
    {
        $numericId = (int) preg_replace('/[^0-9]/', '', $id);
        if ($type === 'student') {
            $student = Student::findOrFail($numericId);
            return response()->json(['success' => true, 'data' => $this->studentDetails($student)]);
        } elseif ($type === 'teacher') {
            $teacher = Teacher::findOrFail($numericId);
            return response()->json(['success' => true, 'data' => $this->teacherDetails($teacher)]);
        }
        abort(404);
    }

    public function showStudent($student)
    {
        $studentModel = $student instanceof Student ? $student : Student::findOrFail((int) preg_replace('/[^0-9]/', '', (string) $student));
        return response()->json(['success' => true, 'data' => $this->studentDetails($studentModel)]);
    }

    public function updateStudent(Request $request, $student)
    {
        $studentModel = $student instanceof Student ? $student : Student::findOrFail((int) preg_replace('/[^0-9]/', '', (string) $student));
        $data = $this->validateStudent($request, $studentModel);
        return DB::transaction(function () use ($studentModel, $data) {
            $studentModel->update($this->studentAttributes($data, false));
            $studentModel->subjects()->sync($data['subject_ids'] ?? []);
            $this->syncStudentBucketSubjects($studentModel->id, (int) $data['grade_id'], $data['subject_ids'] ?? []);
            return response()->json(['success' => true, 'message' => 'Student updated.', 'data' => $this->studentDetails($studentModel->fresh())]);
        });
    }

    public function storeTeacher(Request $request)
    {
        $data = $this->validateTeacher($request);
        return DB::transaction(function () use ($data) {
            $teacher = Teacher::create($this->teacherAttributes($data));
            $this->syncTeacherAssignments($teacher, $data);
            return response()->json(['success' => true, 'message' => 'Teacher created.', 'data' => $this->teacherDetails($teacher->fresh())], 201);
        });
    }

    public function showTeacher($teacher)
    {
        $teacherModel = $teacher instanceof Teacher ? $teacher : Teacher::findOrFail((int) preg_replace('/[^0-9]/', '', (string) $teacher));
        return response()->json(['success' => true, 'data' => $this->teacherDetails($teacherModel)]);
    }

    public function updateTeacher(Request $request, $teacher)
    {
        $teacherModel = $teacher instanceof Teacher ? $teacher : Teacher::findOrFail((int) preg_replace('/[^0-9]/', '', (string) $teacher));
        $data = $this->validateTeacher($request, $teacherModel);
        return DB::transaction(function () use ($teacherModel, $data) {
            $teacherModel->update($this->teacherAttributes($data, false));
            $this->syncTeacherAssignments($teacherModel, $data);
            return response()->json(['success' => true, 'message' => 'Teacher updated.', 'data' => $this->teacherDetails($teacherModel->fresh())]);
        });
    }

    public function changePassword(Request $request, string $type, string $id)
    {
        $numericId = (int) preg_replace('/[^0-9]/', '', $id);
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $model = $type === 'student' ? Student::findOrFail($numericId) : ($type === 'teacher' ? Teacher::findOrFail($numericId) : abort(404));
        $model->update(['password' => Hash::make($data['password'])]);
        return response()->json(['success' => true, 'message' => 'Password changed.']);
    }

    public function destroy(string $type, string $id)
    {
        $numericId = (int) preg_replace('/[^0-9]/', '', $id);
        $model = $type === 'student' ? Student::findOrFail($numericId) : ($type === 'teacher' ? Teacher::findOrFail($numericId) : abort(404));
        $model->delete();
        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    public function destroyStudent($student)
    {
        $numericId = is_numeric($student)
            ? (int) $student
            : (int) preg_replace('/[^0-9]/', '', (string) $student);

        $model = Student::findOrFail($numericId);

        return DB::transaction(function () use ($model) {
            $model->delete();
            return response()->json(['success' => true, 'message' => 'Student deleted.']);
        });
    }

    public function destroyTeacher($teacher)
    {
        $numericId = is_numeric($teacher)
            ? (int) $teacher
            : (int) preg_replace('/[^0-9]/', '', (string) $teacher);

        $model = Teacher::findOrFail($numericId);

        return DB::transaction(function () use ($model) {
            $model->delete();
            return response()->json(['success' => true, 'message' => 'Teacher deleted.']);
        });
    }

    private function validateStudent(Request $request, ?Student $student = null): array
    {
        $emailRule = $student
            ? 'nullable|email|unique:students,email,' . $student->id
            : 'nullable|email|unique:students,email';

        $data = $request->validate(['name' => ['required', 'string', 'max:60'], 'reg_no' => ['required', 'string', 'max:15', Rule::unique('students', 'reg_no')->ignore($student)], 'password' => [$student ? 'nullable' : 'required', 'string', 'min:8'], 'grade_id' => ['required', 'exists:grades,id'], 'subject_ids' => ['nullable', 'array'], 'subject_ids.*' => ['integer', 'exists:subjects,id'], 'address' => ['nullable', 'string', 'max:45'], 'dob' => ['nullable', 'date'], 'reg_date' => ['nullable', 'date'], 'mobile_number' => ['nullable', 'string', 'max:11'], 'email' => $emailRule]);
        $available = collect($this->gradeSubjectsData((int) $data['grade_id']))->pluck('id')->map(fn ($id) => (int) $id);
        if (collect($data['subject_ids'] ?? [])->diff($available)->isNotEmpty()) abort(response()->json(['message' => 'Selected subjects must be available for the selected class.'], 422));
        return $data;
    }

    private function studentAttributes(array $data, bool $creating = true): array
    {
        $attributes = collect($data)->only(['name', 'reg_no', 'grade_id', 'address', 'dob', 'reg_date', 'mobile_number', 'email'])->all();
        $attributes['dob'] = $attributes['dob'] ?? now()->subYears(10)->toDateString();
        $attributes['reg_date'] = $attributes['reg_date'] ?? now()->toDateString();
        if (!empty($data['password'])) $attributes['password'] = Hash::make($data['password']);
        return $attributes;
    }

    private function validateTeacher(Request $request, ?Teacher $teacher = null): array
    {
        $payload = $request->all();

        if (($payload['is_subject_teacher'] ?? false) && empty($payload['subject_assignments']) && !empty($payload['subject_teacher_subject_id']) && !empty($payload['subject_teacher_class_ids'])) {
            $payload['subject_assignments'] = [[
                'subject_id' => $payload['subject_teacher_subject_id'],
                'grade_ids' => $payload['subject_teacher_class_ids'],
            ]];
        }

        $emailRule = $teacher
            ? 'required|email|unique:teachers,email,' . $teacher->id
            : 'required|email|unique:teachers,email';

        $data = validator($payload, ['name' => ['required', 'string', 'max:60'], 'user_name' => ['required', 'string', Rule::unique('teachers', 'user_name')->ignore($teacher)], 'password' => [$teacher ? 'nullable' : 'required', 'string', 'min:8'], 'is_class_teacher' => ['required', 'boolean'], 'is_subject_teacher' => ['required', 'boolean'], 'class_teacher_grade_id' => ['nullable', 'exists:grades,id'], 'subject_assignments' => ['nullable', 'array'], 'subject_assignments.*.subject_id' => ['required_with:subject_assignments', 'exists:subjects,id'], 'subject_assignments.*.grade_ids' => ['required_with:subject_assignments', 'array', 'min:1'], 'subject_assignments.*.grade_ids.*' => ['exists:grades,id'], 'email' => $emailRule, 'mobile_number' => ['nullable', 'string', 'max:12']])->validate();
        if (!$data['is_class_teacher'] && !$data['is_subject_teacher']) abort(response()->json(['message' => 'At least one teacher type must be selected.'], 422));
        if ($data['is_class_teacher'] && empty($data['class_teacher_grade_id'])) abort(response()->json(['message' => 'A class teacher must be assigned one class.'], 422));
        if ($data['is_subject_teacher'] && empty($data['subject_assignments'])) abort(response()->json(['message' => 'A subject teacher requires at least one subject and class assignment.'], 422));
        return $data;
    }

    private function teacherAttributes(array $data, bool $creating = true): array
    {
        $attributes = collect($data)->only(['name', 'user_name', 'email', 'mobile_number', 'is_class_teacher', 'is_subject_teacher'])->all();
        $attributes['role_status'] = $data['is_class_teacher'] ? 1 : 0; // compatibility with existing clients
        if (!empty($data['password'])) $attributes['password'] = Hash::make($data['password']);
        return $attributes;
    }

    private function syncTeacherAssignments(Teacher $teacher, array $data): void
    {
        DB::table('teacher_has_grade')->where('teacher_id', $teacher->id)->delete();
        DB::table('teacher_has_subject')->where('teacher_id', $teacher->id)->delete();
        DB::table('teacher_subject_grade')->where('teacher_id', $teacher->id)->delete();
        if ($data['is_class_teacher']) DB::table('teacher_has_grade')->insert(['teacher_id' => $teacher->id, 'grade_id' => $data['class_teacher_grade_id'], 'created_at' => now(), 'updated_at' => now()]);
        foreach ($data['subject_assignments'] ?? [] as $assignment) foreach ($assignment['grade_ids'] as $gradeId) {
            DB::table('teacher_subject_grade')->insert(['teacher_id' => $teacher->id, 'subject_id' => $assignment['subject_id'], 'grade_id' => $gradeId, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('teacher_has_subject')->updateOrInsert(['teacher_id' => $teacher->id, 'subject_id' => $assignment['subject_id']], ['updated_at' => now(), 'created_at' => now()]);
        }
    }

    private function studentDetails(Student $student): array
    {
        $student->load(['grade:id,name', 'subjects:id,name,subject_code']);
        return ['id' => "student/{$student->id}", 'name' => $student->name, 'reg_no' => $student->reg_no, 'grade_id' => $student->grade_id, 'grade' => $student->grade, 'subjects' => $student->subjects, 'address' => $student->address, 'dob' => $student->dob, 'reg_date' => $student->reg_date, 'mobile_number' => $student->mobile_number, 'email' => $student->email];
    }

    private function gradeSubjectsData(int $grade): array
    {
        return $this->gradeSubjects($grade)->getData(true)['data'];
    }

    private function teacherDetails(Teacher $teacher): array
    {
        $assignments = DB::table('teacher_subject_grade')->join('subjects', 'subjects.id', '=', 'teacher_subject_grade.subject_id')->join('grades', 'grades.id', '=', 'teacher_subject_grade.grade_id')->where('teacher_subject_grade.teacher_id', $teacher->id)->select('subjects.id as subject_id', 'subjects.name as subject_name', 'grades.id as grade_id', 'grades.name as grade_name')->get()->groupBy('subject_id')->map(fn ($items) => ['subject_id' => $items->first()->subject_id, 'subject_name' => $items->first()->subject_name, 'grade_ids' => $items->pluck('grade_id')->values(), 'grades' => $items->map(fn ($i) => ['id' => $i->grade_id, 'name' => $i->grade_name])->values()])->values();
        return ['id' => "teacher/{$teacher->id}", 'name' => $teacher->name, 'user_name' => $teacher->user_name, 'email' => $teacher->email, 'mobile_number' => $teacher->mobile_number, 'is_class_teacher' => (bool) $teacher->is_class_teacher, 'is_subject_teacher' => (bool) $teacher->is_subject_teacher, 'class_teacher_grade_id' => DB::table('teacher_has_grade')->where('teacher_id', $teacher->id)->value('grade_id'), 'subject_assignments' => $assignments];
    }

    private function syncStudentBucketSubjects(int $studentId, int $gradeId, array $subjectIds): void
    {
        $gradeName = (string) DB::table('grades')->where('id', $gradeId)->value('name');
        $bucketNames = [];
        if (preg_match('/Grade ([6-9])/', $gradeName)) {
            $bucketNames = ['Aesthetic (G6-9)'];
        } elseif (preg_match('/Grade (10|11)/', $gradeName)) {
            $bucketNames = ['Category 01 (G10-11)', 'Category 02 (G10-11)', 'Category 03 (G10-11)'];
        } elseif (str_contains($gradeName, 'Arts')) {
            $bucketNames = ['Category 01 (G12-13 Arts)', 'Category 02 (G12-13 Arts)', 'Category 03 (G12-13 Arts)'];
        } elseif (str_contains($gradeName, 'Commerce')) {
            $bucketNames = ['Commerce Core Electives'];
        }

        DB::table('student_has_bucket_subject')->where('student_id', $studentId)->delete();

        if (empty($bucketNames) || empty($subjectIds)) {
            return;
        }

        $shbsIds = DB::table('subject_has_bucket_subject')
            ->join('bucket_subjects', 'bucket_subjects.id', '=', 'subject_has_bucket_subject.bucket_subject_id')
            ->whereIn('bucket_subjects.name', $bucketNames)
            ->whereIn('subject_has_bucket_subject.subject_id', $subjectIds)
            ->pluck('subject_has_bucket_subject.id')
            ->toArray();

        foreach ($shbsIds as $shbsId) {
            DB::table('student_has_bucket_subject')->insert([
                'student_id'                    => $studentId,
                'subject_has_bucket_subject_id' => $shbsId,
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]);
        }
    }
}
