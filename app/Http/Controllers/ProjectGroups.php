<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectGroups;

class ProjectGroups extends Controller
{
    public function index() {
        return ProjectGroups::all();
    }
}
