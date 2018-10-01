<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Organizations;

class OrganizationsController extends Controller
{
     public function index() {
            return Organizations::with('parent')->get();
    }

    public function store(Request $request){
        $data = new Organizations();
        $data->name = $request->name;
        $data->acronym = $request->acronym;
        $data->url = $request->url;
        $data->organization_type_id = $request->organization_type_id;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    }

    public function show($id){
        $data =Organizations::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = Organizations::where('id', $id)->first();
        if($data){
           $data->name = $request->name;
           $data->acronym = $request->acronym;
           $data->url = $request->url;
           $data->organization_type_id = $request->organization_type_id;
            if($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        }else
        return ['status' => false, 'message' => 'El registro no fue encontrado'];  
    }

    public function destroy($id){
        $data = Organizations::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = Organizations::with('parent')
                ->where('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = Organizations::with('parent')
                ->offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = Organizations::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
    
}

