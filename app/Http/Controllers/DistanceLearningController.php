<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DistanceLearningController extends Controller
{
    public function index() {
        return view('DistanceLearning.index');
    }
}
