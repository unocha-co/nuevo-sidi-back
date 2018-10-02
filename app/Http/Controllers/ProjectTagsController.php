<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectTags;

class ProjectTagsController extends Controller{

    public function index() {
        $data = ProjectTags::with('childrens')
        ->where('parent_id', 0)
        ->get();
        foreach ($data as $d)
            $d = $this->recursive_childrens($d);
        return $data;
    }

    public function store(Request $request){
        
    } 

    public function show($id){
    }

    public function update(Request $request, $id){
    }

    public function destroy($id){
    }

    public function recursive_childrens($item){
      if(count($item->childrens) > 0){
        foreach($item->childrens as $c){
          $data = ProjectTags::where('parent_id', $c->id)->get();
          foreach ($data as $d) 
            $d = $this->recursive_childrens($d);
          $c->childrens = $data;
        }
      }
      return $item;
    }
}