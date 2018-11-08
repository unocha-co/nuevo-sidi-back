<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\ProjectBG;
use DB;

class ProjectBGController extends Controller{

    public function index() {
        return ProjectBG::select("id", "name")->get();
    }

    public function store(Request $request){
        //
    }

    public function show($id){
        //
    }

    public function update(Request $request, $id){
        //
    }

    public function destroy($id){
        //
    }
}