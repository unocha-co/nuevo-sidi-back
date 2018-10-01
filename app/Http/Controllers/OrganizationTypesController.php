<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrganizationTypes;

class OrganizationTypesController extends Controller
{
    public function index() {
            return OrganizationTypes::all();
    }

    public function store(Request $request){
        $data = new OrganizationTypes();
        $data->type = $request->type;
        $data->type_es = 0;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    }

    public function show($id){
        $data =OrganizationTypes::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = OrganizationTypes::where('id', $id)->first();
        if($data){
            $data->type = $request->type;
            $data->type_es = 0;
            if($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        }else
        return ['status' => false, 'message' => 'El registro no fue encontrado'];  
    }

    public function destroy($id){
        $data = OrganizationTypes::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = OrganizationTypes::where ('type', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = OrganizationTypes::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = OrganizationTypes::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
    
}
