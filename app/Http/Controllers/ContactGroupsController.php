<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ContactGroups;

class ContactGroupsController extends Controller
{
    public function index() {
            return ContactGroups::all();
    }
   
    public function store(Request $request){
        $data = new ContactGroups();
        $data->name = $request->name;
        $data->parent_id = $request->parent_id;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    }

    public function show($id){
        $data = ContactGroups::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = ContactGroups::where('id', $id)->first();
        if($data){
            $data->name = $request->name;
            $data->parent_id = $request->parent_id;
            if($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        }else
        return ['status' => false, 'message' => 'El registro no fue encontrado'];  
    }

    public function destroy($id){
        $data = ContactGroups::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = ContactGroups::where ('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = ContactGroups::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = ContactGroups::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
    
}
