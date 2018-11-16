<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectGroups;

class ProjectGroupsController extends Controller
{
    public function index() {
        return ProjectGroups::all();
    }
}
