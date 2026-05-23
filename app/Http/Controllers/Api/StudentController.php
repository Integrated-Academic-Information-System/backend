<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller {

    public function index(Request $request) {
        $query = Student::with(['user', 'grade']);

        if ($request->grade_id) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('index_no', 'like', "%$search%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$search%"));
            });
        }

        return response()->json($query->get()->map(fn($s) => [
            'id'       => $s->id,
            'name'     => $s->user->name,
            'email'    => $s->user->email,
            'index_no' => $s->index_no,
            'grade'    => $s->grade->name,
            'grade_id' => $s->grade_id,
        ]));
    }

    public function show($id) {
        $student = Student::with(['user', 'grade'])->findOrFail($id);
        return response()->json([
            'id'       => $student->id,
            'name'     => $student->user->name,
            'email'    => $student->user->email,
            'index_no' => $student->index_no,
            'grade'    => $student->grade->name,
            'grade_id' => $student->grade_id,
        ]);
    }
}