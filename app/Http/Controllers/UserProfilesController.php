<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserProfiles;

class UserProfilesController extends Controller
{
	 public function index() {
            return UserProfiles::all();
    }

    public function store(Request $request){
        $data = new UserProfiles();
        $data->name = $request->name;
        if($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    } 

    public function show($id){
        $data = UserProfiles::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = UserProfiles::where('id', $id)->first();
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
        $data = UserProfiles::where('id', $id)->delete();
        return [ 'status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = UserProfiles::where ('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = UserProfiles::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = UserProfiles::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }
}
