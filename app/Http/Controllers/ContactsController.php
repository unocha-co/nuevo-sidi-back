<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\ContactsContactGroup;
use App\Contacts;
use DB;

class ContactsController extends Controller{

    public function index() {

       // return Contacts::all();
      return Contacts::select("id", DB::raw("CONCAT(first_name,' ',last_name) as name"))->get();
    }

    public function store(Request $request){

        $data = new Contacts();
        $data->first_name = $request->first_name;
        $data->last_name = $request->last_name;
        $data->email = $request->email;
        $data->twitter = $request->twitter;
   



        if($data->save()){

           $group = new ContactsContactGroup();
            $group->contact_id = $data->id;
            $group->group_id = $request->contact_group_id;
            $group->save();
            if($group->save()){
              return ['status' => true, 'data' => $data];
              }else{
                return ['status' => false];

              }
              
            
        }
        else{
            return ['status' => false];
        }



    } 

    public function show($id){
    }

    public function update(Request $request, $id){
    }

    public function destroy($id){
    }
}