<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;

class GradeController extends Controller {
    public function index() {
        return response()->json(Grade::orderBy('level')->get());
    }
}