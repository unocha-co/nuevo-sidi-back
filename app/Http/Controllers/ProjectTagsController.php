<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectTags;

class ProjectTagsController extends Controller
{

    public function index($id = false)
    {
        if ($id) {
            $data = ProjectTags::with([
                'childrens' => function ($query) use ($id) {
                    $query->with([
                        'projectprojecttags' => function ($query2) use ($id) {
                            $query2->where('project_id', $id);
                        }
                    ]);
                }
            ])->where('parent_id', null)->get();
            foreach ($data as $d)
                $d = $this->recursive_childrens($d);
            return $data;
        } else {
            $data = ProjectTags::with(['childrens'])
                ->where('parent_id', null)
                ->get();
            foreach ($data as $d)
                $d = $this->recursive_childrens($d);
            return $data;
        }
    }

    public function pt_parents()
    {
        $data = ProjectTags::select('id', 'name')->where('parent_id', null)->get();
        return $data;
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        if ($id) {
            $data = ProjectTags::with([
                'childrens' => function ($query) use ($id) {
                    $query->with([
                        'childrens' => function ($query2) use ($id) {
                            $query2->with([
                                'childrens' => function ($query3) use ($id) {
                                    $query3->with([
                                        'childrens' => function ($query4) use ($id) {
                                            $query4->with([
                                                'projectprojecttags' => function ($query5) use ($id) {
                                                    $query5->where('project_id', $id);
                                                }
                                            ]);
                                        },
                                        'projectprojecttags' => function ($query6) use ($id) {
                                            $query6->where('project_id', $id);
                                        }
                                    ]);
                                },
                                'projectprojecttags' => function ($query7) use ($id) {
                                    $query7->where('project_id', $id);
                                }
                            ]);
                        },
                        'projectprojecttags' => function ($query6) use ($id) {
                            $query6->where('project_id', $id);
                        }
                    ]);
                }
            ])->where('parent_id', null)->get();
        } else {
            $data = ProjectTags::with(['childrens'])
                ->where('parent_id', null)
                ->get();
        }
        foreach ($data as $d)
            $d = $this->recursive_childrens($d);
        return $data;
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
                $data = ProjectTags::where('parent_id', $c->id)->get();
                foreach ($data as $d)
                    $d = $this->recursive_childrens($d);
                $c->childrens = $data;
            }
        }
        return $item;
    }
}