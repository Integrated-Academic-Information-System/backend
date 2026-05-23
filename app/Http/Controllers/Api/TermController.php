<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Term;

class TermController extends Controller {
    public function index() {
        return response()->json(Term::orderBy('order')->get());
    }
}