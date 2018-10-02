<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hrp;

class HrpController extends Controller{

    public function index() {
        return Hrp::all();
    }

    public function store(Request $request){
    } 

    public function show($id){
    }

    public function update(Request $request, $id){
    }

    public function destroy($id){
    }
}