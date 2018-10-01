<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\ProjectAdmin;
use App\ProjectOrganization;
use App\OrganizationProjectRelation;

class ProjectController extends Controller
{
    public function index() {
        return Project::all();
    }

    public function store(Request $request){
        $data = new Project();
        $data->name = $request->name;
        $data->code = $request->code;
        $data->date_start = date('Y-m-d H:i:s', strtotime($request->date_start);
        $data->date_end = date('Y-m-d H:i:s', strtotime($request->date_end);
        $data->interagency = $request->interagency;
        $data->cost = $request->budget[0]->value;
        $data->contact_id = 0;
        if($data->save()){
            $po = new ProjectOrganization();
            $po->project_id = $data->id;
            $po->organization_id = $request->organization;
            $po->organization_project_relation_id = OrganizationProjectRelation::where('name', 'Ejecutor')->first()->id;
            $po->save();
            $implementers_id = OrganizationProjectRelation::where('name', 'Implementadores')->first()->id;
            $donors_id = OrganizationProjectRelation::where('name', 'Donantes')->first()->id;
            foreach ($i as $request->implementers) {
                $po = new ProjectOrganization();
                $po->project_id = $data->id;
                $po->organization_id = $i;
                $po->organization_project_relation_id = $implementers_id;
                $po->save();
            }
            foreach ($d as $request->donors) {
                $po = new ProjectOrganization();
                $po->project_id = $data->id;
                $po->organization_id = $d->id;
                $po->organization_project_relation_id = $donors_id;
                $po->value = $d->value;
                $po->save();
            }
            if($request->national){
                $padmin = new ProjectAdmin();
                $padmin->project_id = $data->id;
                $padmin->admin_id = 0;
                $padmin->save();
            }else{
                foreach ($request->location as $l) {
                    $padmin = new ProjectAdmin();
                    $padmin->project_id = $data->id;
                    $padmin->admin_id = $l;
                    $padmin->save();
                }
            }
            return ['status' => true, 'data' => $data];
        }
        else
            return ['status' => false];
    }

    public function show($id){
        $data = Project::where('id', $id)->first();
        if($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = Project::where('id', $id)->first();
        if($data){
            $data->name = $request->name;
            $data->date_start = $request->date_start;
            $data->date_end = $request->date_end;
            $data->cost = $request->cost;
            $data->contact_id = 0;
            if($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        }else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function destroy($id){
        $data = Project::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination() {
        if($_GET['search']['value']){
            $data = Project::where ('name', 'like', '%'.$_GET['search']['value'].'%')
                ->offset ($_GET['start'] )
                ->limit($_GET['length'])
                ->get();
        }else {
            $data = Project::offset ($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = Project::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' =>$count, 'data' => $data, 'buscar' =>$_GET['search']['value']? true : false];
    }

}
