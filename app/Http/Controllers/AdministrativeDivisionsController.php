<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AdministrativeDivisions;

class AdministrativeDivisionsController extends Controller
{
    public function index() {
        return AdministrativeDivisions::with('parent')->get();
    }

    public function indexMap() {
        return AdministrativeDivisions::with('parent')->get();
    }

    public function store(Request $request){
        $data = new AdministrativeDivisions();
        $data->name = $request->name;
        $data->code = 0;
        $data->pcode = 0;
        $data->country_id = 'COL';
        $data->parent_id = isset($request->parent_id) ? $request->parent_id : null;
        $data->level = isset($request->parent_id) ? 2 : 1;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    }

    public function show($id){
        $data = AdministrativeDivisions::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = AdministrativeDivisions::where('id', $id)->first();
        if($data){
          $data->name = $request->name;
          $data->code = 0;
          $data->pcode = 0;
          $data->parent_id = isset($request->parent_id) ? $request->parent_id : null;
          $data->level = isset($request->parent_id) ? 2 : 1;
          if($data->save())
            return ['status' => true, 'data' => $data];
          else
            return ['status' => false];
        }else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];  
    }

    public function destroy($id) {
        $data = AdministrativeDivisions::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination() {
       if($_GET['search']['value']){
           $data = AdministrativeDivisions::with('parent')
               ->where('name', 'like', '%'.$_GET['search']['value'].'%')
               ->offset ($_GET['start'] )
               ->limit($_GET['length'])
               ->get();
       }else {
           $data = AdministrativeDivisions::with('parent')
           ->offset ($_GET['start'])
           ->limit($_GET['length'])
           ->get();
       }
        $count = AdministrativeDivisions::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }

    public function getAllRegions(){
        return AdministrativeDivisions::with('childrens')
        ->where('level', '1')
        ->where('name', '!=', 'Nacional')
        ->get();
    }

}
