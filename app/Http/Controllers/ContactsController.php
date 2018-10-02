<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contacts;
use DB;

class ContactsController extends Controller{

    public function index() {
        return Contacts::select("id", DB::raw("CONCAT(first_name,' ',last_name) as name"))->get();
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