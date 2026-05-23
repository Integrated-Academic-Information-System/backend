<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller {
    public function index(Request $request) {
        $query = Subject::with('grade');
        if ($request->grade_id) {
            $query->where('grade_id', $request->grade_id);
        }
        return response()->json($query->get());
    }
}