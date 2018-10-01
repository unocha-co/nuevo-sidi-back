<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrganizationProjectRelation;

class OrganizationProjectRelationController extends Controller
{

	public function index() {
            return OrganizationProjectRelation::all();
	}

	public function store(Request $request){
		$data = new OrganizationProjectRelation();
		$data->name = $request->name;
		if($data->save())
			return ['status' => true, 'data' => $data];
		else
			return ['status' => false];
	} 

	public function show($id){
		$data = OrganizationProjectRelation::where('id', $id)->first(['id','name']);
		if($data)
			return ['status' => true, 'data' => $data];
		else
			return ['status' => false, 'message' => 'El registro no fue encontrado'];
	}

	public function update(Request $request, $id){
		$data = OrganizationProjectRelation::where('id', $id)->first();
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
		$data = OrganizationProjectRelation::where('id', $id)->delete();
		return ['status' => $data];
	}

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = OrganizationProjectRelation::where ('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = OrganizationProjectRelation::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = OrganizationProjectRelation::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
}



