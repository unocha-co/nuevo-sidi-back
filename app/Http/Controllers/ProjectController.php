<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\ProjectAdmin;
use App\ProjectOrganization;
use App\OrganizationProjectRelation;
use App\Budget;
use App\ProjectBeneficiaries;
use App\ProjectGroups;

class ProjectController extends Controller
{
    public function index() {
        return Project::all();
    }

    public function store(Request $request){
        $data = new Project();
        $data->name = $request->name;
        $data->code = $request->code;
        $data->cost = $request->cost;
        $data->description = $request->description;
        $data->date_start = date('Y-m-d H:i:s', strtotime($request->date_start));
        $data->date_end = date('Y-m-d H:i:s', strtotime($request->date_end));
        $data->interagency = $request->interagency == true ? 1 : 0;
        $data->contact_id = $request->contact;
        $data->hrp_id = $request->hrp;
        if($data->save()){
          if(isset($request->organization)){
            $po = new ProjectOrganization();
            $po->project_id = $data->id;
            $po->organization_id = $request->organization;
            $po->organization_project_relation_id = OrganizationProjectRelation::where('name', 'Ejecutor')->first()->id;
            $po->save();
          }
            $implementers_id = OrganizationProjectRelation::where('name', 'Implementadores')->first()->id;
            $donors_id = OrganizationProjectRelation::where('name', 'Donante')->first()->id;
            foreach ($request->implementers as $i) {
                $po = new ProjectOrganization();
                $po->project_id = $data->id;
                $po->organization_id = $i;
                $po->organization_project_relation_id = $implementers_id;
                $po->save();
            }
            for($b=0; $b<count($request->budget); $b++){
              if($request->budget[$b]['value']){
                $po = new Budget();
                $po->project_id = $data->id;
                $po->budget = $request->budget[$b]['value'];
                $po->budget_id = $b;
                $po->save();
              }
            }
            foreach ($request->donors as $d) {
              if($d['id']){
                $po = new ProjectOrganization();
                $po->project_id = $data->id;
                $po->organization_id = $d['id'];
                $po->organization_project_relation_id = $donors_id;
                $po->value = $d['value'];
                $po->save();
              }
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

    public function step3($id, Request $request)
    {
        $p = $request->poblacionales;
        $i = $request->indirectos;
        $o = $request->organizations;

        ProjectBeneficiaries::where('project_id', $id)->delete();
        ProjectOrganization::where('project_id', $id)->where('organization_project_relation_id', 5)->delete();

        if(isset($p['groups'])){
         foreach ($p['groups'] as $key => $val) {
            if($val){
               $item = new ProjectBeneficiaries();
               $item->project_id = $id;
               $item->group_id = $val['id'];
               $item->number = $val['value'];
               $item->type = 1;
               $item->save();
            }
          }
        }

       foreach ($o as $org) {
          if($org){
             $po = new ProjectOrganization();
              $po->project_id = $id;
              $po->organization_id = $org;
              $po->organization_project_relation_id = 5;
              $po->save();
          }
        }

        //Poblacionales
        if(isset($p['total'])){
            if($p['total']){
              $item = new ProjectBeneficiaries();
              $item->project_id = $id;
              $item->number = $p['total'];
              $item->type = 1;
              $item->save();
            }
        }
        if(isset($p['gender']['m'])){
            foreach($p['gender']['m'] as $key => $val) {
              if($val){
                $item = new ProjectBeneficiaries();              
                $item->project_id = $id;
                $item->gender = 'm';
                $item->number = $val;
                $item->type = 1;
                if($key != "total"){
                    switch ($key) {
                       case "age1":
                          $item->age = 1;
                          break;
                       case "age2":
                          $item->age = 2;
                          break;
                       case "age3":
                          $item->age = 3;
                          break;
                       case "age4":
                          $item->age = 4;
                          break;
                       default: null;
                        
                    } 
                }
                $item->save();
              }
            }
                   
        }
        
        if(isset($p['gender']['h'])){
            foreach($p['gender']['h'] as $key => $val) {
              if($val){
                $item = new ProjectBeneficiaries();
                $item->project_id = $id;
                $item->gender = 'h';
                $item->number = $val;
                $item->type = 1;
                if($key != "total"){
                    switch ($key) {
                       case "age1":
                          $item->age = 1;
                          break;
                       case "age2":
                          $item->age = 2;
                          break;
                       case "age3":
                          $item->age = 3;
                          break;
                       case "age4":
                          $item->age = 4;
                          break;
                       default: null;
                        
                    } 
                }
                $item->save();
              }
            }
                   
        }

         //Indirectos
        if(isset($i['total'])){
          if($i['total']){
            $item = new ProjectBeneficiaries();
            $item->project_id = $id;
            $item->number = $i['total'];
            $item->type = 2;
            $item->save();
          }
        }
        if(isset($i['gender']['m'])){
            foreach($i['gender']['m'] as $key => $val) {
              if($val){
                $item = new ProjectBeneficiaries();              
                $item->project_id = $id;
                $item->gender = 'm';
                $item->number = $val;
                $item->type = 2;
                if($key != "total"){
                    switch ($key) {
                       case "age1":
                          $item->age = 1;
                          break;
                       case "age2":
                          $item->age = 2;
                          break;
                       case "age3":
                          $item->age = 3;
                          break;
                       case "age4":
                          $item->age = 4;
                          break;
                       default: null;
                        
                    } 
                }
                $item->save();
              }
            }
                   
        }

        if(isset($i['gender']['h'])){
            foreach($i['gender']['h'] as $key => $val) {
              if($val){
                $item = new ProjectBeneficiaries();
                $item->project_id = $id;
                $item->gender = 'h';
                $item->number = $val;
                $item->type = 2;
                if($key != "total"){
                    switch ($key) {
                       case "age1":
                          $item->age = 1;
                          break;
                       case "age2":
                          $item->age = 2;
                          break;
                       case "age3":
                          $item->age = 3;
                          break;
                       case "age4":
                          $item->age = 4;
                          break;
                       default: null;
                        
                    } 
                }
                $item->save();
              }
            }
                   
        }

        return ['status' => true];
    }
 
    public function show($id){
        $data = Project::with(['budget', 'location', 'tags', 'beneficiaries'])
        ->where('id', $id)
        ->select('name', 'hrp_id as hrp', 'code', 'contact_id as contact', 'interagency', 'description', 'id', 'date_start', 'date_end', 'cost')
        ->first();
        if($data){
          $implementers_id = OrganizationProjectRelation::where('name', 'Implementadores')->first()->id;
          $donors_id = OrganizationProjectRelation::where('name', 'Donante')->first()->id;
          $ejecutor_id = OrganizationProjectRelation::where('name', 'Ejecutor')->first()->id;
          $data->implementers = ProjectOrganization::where('project_id', $id)
          ->where('organization_project_relation_id', $implementers_id)
          ->select(['organization_id'])
          ->get();  
          $data->donors = ProjectOrganization::where('project_id', $id)
          ->where('organization_project_relation_id', $donors_id)
          ->select(['organization_id', 'value'])
          ->get();
          $data->beneficiaries_organizations = ProjectOrganization::where('project_id', $id)
          ->where('organization_project_relation_id', 5)
          ->select(['organization_id'])
          ->get();
          $e = ProjectOrganization::where('project_id', $id)
          ->where('organization_project_relation_id', $ejecutor_id)
          ->select(['organization_id'])
          ->first();
          $data->organization = $e ? $e->organization_id : null;
          $data->date_start = substr($data->date_start, 0, 10);
          $data->date_end = substr($data->date_end, 0, 10);
          return ['status' => true, 'data' => $data];
        }else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id){
        $data = Project::where('id', $id)->first();
        if($data){
          $data->name = $request->name;
          $data->code = $request->code;
          $data->cost = $request->cost;
          $data->description = $request->description;
          $data->date_start = date('Y-m-d H:i:s', strtotime($request->date_start));
          $data->date_end = date('Y-m-d H:i:s', strtotime($request->date_end));
          $data->interagency = $request->interagency == true ? 1 : 0;
          $data->contact_id = $request->contact;
          $data->hrp_id = $request->hrp;
          if($data->save()){
              ProjectOrganization::where('project_id', $id)->delete();
              Budget::where('project_id', $id)->delete();
              ProjectAdmin::where('project_id', $id)->delete();
              if(isset($request->organization)){
                $po = new ProjectOrganization();
                $po->project_id = $data->id;
                $po->organization_id = $request->organization;
                $po->organization_project_relation_id = OrganizationProjectRelation::where('name', 'Ejecutor')->first()->id;
                $po->save();
              }
                $implementers_id = OrganizationProjectRelation::where('name', 'Implementadores')->first()->id;
                $donors_id = OrganizationProjectRelation::where('name', 'Donante')->first()->id;
                foreach ($request->implementers as $i) {
                    $po = new ProjectOrganization();
                    $po->project_id = $data->id;
                    $po->organization_id = $i;
                    $po->organization_project_relation_id = $implementers_id;
                    $po->save();
                }
                for($b=0; $b<count($request->budget); $b++){
                  if($request->budget[$b]['value']){
                    $po = new Budget();
                    $po->project_id = $data->id;
                    $po->budget = $request->budget[$b]['value'];
                    $po->budget_id = $b;
                    $po->save();
                  }
                }
                foreach ($request->donors as $d) {
                  if($d['id']){
                    $po = new ProjectOrganization();
                    $po->project_id = $data->id;
                    $po->organization_id = $d['id'];
                    $po->organization_project_relation_id = $donors_id;
                    $po->value = $d['value'];
                    $po->save();
                  }
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
