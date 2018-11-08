<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectProjectShortTags;

class ProjectProjectShortTagsController extends Controller
{

    public function index()
    {
    }

    public function store(Request $request)
    {
        ProjectProjectShortTags::where('project_id', $request->project_id)->delete();
        foreach ($request['shorttags1'] as $t) {
            if (is_array($t)) {
                foreach ($t as $t2) {
                    $data = new ProjectProjectShortTags();
                    $data->project_id = $request->project_id;
                    $data->tag_id = $t2;
                    $data->save();
                }
            } elseif ($t) {
                $data = new ProjectProjectShortTags();
                $data->project_id = $request->project_id;
                $data->tag_id = $t;
                $data->save();
            }

        }
        foreach ($request['shorttags2'] as $t) {
            foreach ($t as $t2) {
                $data = new ProjectProjectShortTags();
                $data->project_id = $request->project_id;
                $data->tag_id = $t2;
                $data->save();
            }
        }
        foreach ($request['shorttags3'] as $t) {
            foreach ($t as $t2) {
                $data = new ProjectProjectShortTags();
                $data->project_id = $request->project_id;
                $data->tag_id = $t2;
                $data->save();
            }
        }
        foreach ($request['shorttags4'] as $t) {
            foreach ($t as $t2) {
                $data = new ProjectProjectShortTags();
                $data->project_id = $request->project_id;
                $data->tag_id = $t2;
                $data->save();
            }
        }
        return ['status' => true, 'data' => $data];

    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }

    public function recursive_childrens($item)
    {
        if (count($item->childrens) > 0) {
            foreach ($item->childrens as $c) {
                $data = ProjectShortTags::where('parent_id', $c->id)->get();
                foreach ($data as $d)
                    $d = $this->recursive_childrens($d);
                $c->childrens = $data;
            }
        }
        return $item;
    }
}