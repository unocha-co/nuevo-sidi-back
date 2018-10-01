<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectClass;

class ProjectClassController extends Controller{

    public function index() {
            return ProjectClass::all();
    }

    public function store(Request $request){
        $data = new ProjectClass();
        $data->name = $request->name;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    } 

    public function show($id){
        $data = ProjectClass::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = ProjectClass::where('id', $id)->first();
        if($data){
            $data->name = $request->name;
            if($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        }else
        return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function destroy($id){
        $data = ProjectClass::where('id', $id)->delete();
        return [ 'status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = ProjectClass::where ('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = ProjectClass::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = ProjectClass::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
}