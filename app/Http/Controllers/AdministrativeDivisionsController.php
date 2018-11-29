<?php

namespace App\Http\Controllers;

use App\Organizations;
use DateTime;
use Illuminate\Http\Request;
use App\AdministrativeDivisions;
use App\ProjectAdmin;

use App\Project;
use App\ProjectTags;
use App\ProjectShortTags;


//NUEVAS RELACIONES PARA PROJECTS
use App\OrganizationProjectRelation;
use App\ProjectOrganization;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class AdministrativeDivisionsController extends Controller
{
    public function index()
    {
        return AdministrativeDivisions::select('id', 'name', 'level',
            DB::raw('ST_X(geom_center) AS x, ST_Y(geom_center) AS y'))->get();
    }

    public function indexMap()
    {
        //$with = ['admins:admin_id,project_id','beneficiaries'

        DB::connection()->disableQueryLog();
        $with = [
            'beneficiaries' => function ($query) {
                $query->select('project_id', DB::raw('SUM(number) as total'))->whereNull('group_id')->whereNull('age')
                    ->whereNull('gender')->groupBy('project_id');
            },
            'budget:project_id,budget_id,budget'];
        $columns = array('id', 'date_start', 'date_end', 'span');
        if (Input::get('pi') && Input::get('pi') == 1) {
            array_push($columns, 'name', 'description');
        } else {
            $with['admins'] = function ($query) {
                $query->with([
                    'adminDivision' => function ($query2) {
                        $query2->select('id', 'name', 'parent_id', DB::raw('ST_X(geom_center) AS x, ST_Y(geom_center) AS y'));
                    }
                ]);
            };
        }
        $pa = Project::select($columns)->with($with);
        $d = Input::get('date');
        $fd = Input::get('f_date');
        if ($fd && $d && $d != 0) {
            if ($fd == 'vigencia') {
                $pa->whereRaw('YEAR(date_start) <=' . $d)
                    ->whereRaw('YEAR(date_end) >=' . $d);
            } else if ($fd == 'final') {
                $pa->whereRaw('YEAR(date_end) =' . $d);
            } else if ($fd == 'inicio') {
                $pa->whereRaw('YEAR(date_start) =' . $d);
            }
        }
        if (Input::get('loc')) {
            $f = Input::get('loc');
            $pa->whereHas('location', function ($query) use ($f) {
                if (strpos($f, ',') !== false) {
                    $ar = explode(',', $f);
                    $query->whereIn('admin_id', $ar);
                } else {
                    $query->where('admin_id', $f);
                }
            });
        }

        if (Input::get('org')) {
            $f = Input::get('org');
            if (strpos($f, '-') !== false) {
                $arr_and = explode('-', $f);
                foreach ($arr_and as $and) {
                    $pa->whereHas('org', function ($query) use ($and) {
                        if (strpos($and, ',') !== false) {
                            $ar = explode(',', $and);
                            $query->whereIn('organization_id', $ar);
                        } else {
                            $query->where('organization_id', $and);
                        }
                    });
                }
            } else {
                $pa->whereHas('org', function ($query) use ($f) {
                    if (strpos($f, ',') !== false) {
                        $ar = explode(',', $f);
                        $query->whereIn('organization_id', $ar);
                    } else {
                        $query->where('organization_id', $f);
                    }
                });
            }
        }

        if (Input::get('tags')) {
            $f = Input::get('tags');
            if (strpos($f, '-') !== false) {
                $arr_and = explode('-', $f);
                foreach ($arr_and as $and) {
                    $pa->whereHas('tags', function ($query) use ($and) {
                        if (strpos($and, ',') !== false) {
                            $ar = explode(',', $and);
                            $query->whereIn('tag_id', $ar);
                        } else {
                            $query->where('tag_id', $and);
                        }
                    });
                }
            } else {
                $pa->whereHas('tags', function ($query) use ($f) {
                    if (strpos($f, ',') !== false) {
                        $ar = explode(',', $f);
                        $query->whereIn('tag_id', $ar);
                    } else {
                        $query->where('tag_id', $f);
                    }
                });
            }
        }

        if (Input::get('s_tags')) {
            $f = Input::get('s_tags');
            if (strpos($f, '-') !== false) {
                $arr_and = explode('-', $f);
                foreach ($arr_and as $and) {
                    $pa->whereHas('shorttags', function ($query) use ($and) {
                        if (strpos($and, ',') !== false) {
                            $ar = explode(',', $and);
                            $query->whereIn('tag_id', $ar);
                        } else {
                            $query->where('tag_id', $and);
                        }
                    });
                }
            } else {
                $pa->whereHas('shorttags', function ($query) use ($f) {
                    if (strpos($f, ',') !== false) {
                        $ar = explode(',', $f);
                        $query->whereIn('tag_id', $ar);
                    } else {
                        $query->where('tag_id', $f);
                    }
                });
            }
        }

        if (Input::get('id')) {
            $f = Input::get('id');
            if (strpos($f, ',') !== false) {
                $ar = explode(',', $f);
                $pa->whereIn('id', $ar);
            } else {
                $pa->where('id', $f);
            };
        }

        if (Input::get('end')) {
            $pa->offset(Input::get('start'))->limit(Input::get('end') - Input::get('start'));
        }

        /*$actual_link = "$_SERVER[REQUEST_URI]";
        return Cache::remember($actual_link, 60 * 24 * 9, function () use ($pa, $fd, $d) {

        });*/

        $pa_c = $pa->get();

        $pa_c->each(function ($item, $key) use ($fd, $d) {
            $bud = $item['budget'];
            unset($item->budget);
            if (count($bud) > 0) {
                if ($fd == 'vigencia') {
                    $type = 'm'; //m:middle - s:start - e:end
                    $d_s = $item['date_start'];
                    $d_e = $item['date_end'];
                    if (substr($d_s, 0, 4) == $d) {
                        $type = 's';
                    } else if (substr($d_e, 0, 4) == $d) {
                        $type = 'e';
                    }
                    $item['presu'] = $this->get_month_budget($d, $d_s, $d_e, $type, $bud, $item['span']);
                } else {
                    $item['presu'] = $bud[0]['budget'];
                }
            } else {
                $item['presu'] = 0;
            }

        });


        $col = $pa_c->implode('id', ',');
        $proj = explode(',', $col);
        if (Input::get('pi') && Input::get('pi') == 1) {
            return ['pa' => $pa_c];
        } else {
            //Calcula el total de beneficiarios del proyecto
            $benef = $pa_c->sum(function ($p) {
                return (count($p['beneficiaries']) > 0) ? intval($p['beneficiaries'][0]->total) : 0;
            });
            //Carga de filtros
            $fil = [];
            $fil['tags'] = ProjectTags::select('id', 'name', 'code', 'parent_id')
                ->withCount(['projectprojecttags' => function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                }])->whereHas('projectprojecttags', function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                })->whereNotNull('parent_id')->get();
            $fil['s_tags'] = ProjectShortTags::select('id', 'name', 'code', 'parent_id')
                ->withCount(['projectprojecttags' => function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                }])->whereHas('projectprojecttags', function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                })->get();
            $fil['loc'] = AdministrativeDivisions::select('id', 'name', 'parent_id', DB::raw('ST_X(geom_center) AS x, ST_Y(geom_center) AS y'))
                ->withCount(['projects' => function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                }])->whereHas('projects', function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                })->get();
            $fil['org'] = ProjectOrganization::select('organization_id', 'relation_id')->with(['org:id,name'])
                ->distinct('project_id, relation_id')->whereIn('project_id', $proj)
                ->withCount(['p_org' => function ($query) use ($proj) {
                    $query->distinct('project_id')->whereIn('project_id', $proj);
                }])->get();
            $fil['tags']->each(function ($item, $key) {
                $item['parent_id'] = $this->getLatestParentPT($item);
            });
            $fil['s_tags']->each(function ($item, $key) {
                $item['parent_id'] = $this->getLatestParentPST($item);
            });

            return ['pa' => $pa_c, 'filtros' => $fil, 'benef' => $benef];
        }


    }

    public function getAllprojectsOfAdmin($idadmin)
    {

        /* $adminsdiv = Project::whereHas(
           'admins', function ($query) { $query->where('admin_id',$idadmin);
         })->get();*/

        $adminsdiv = ProjectAdmin::with(
            ['project' => function ($query) {
                $query->select('id', 'name');
            }])->where('admin_id', $idadmin)->get();


        return $adminsdiv;

    }

    public function filtroProjectsByAdmin($adminid)
    {
        $data = AdministrativeDivisions::select('id', 'name', DB::raw('ST_X(geom_center) AS x, ST_Y(geom_center) AS y'))->with([
            'projects' => function ($query2) {
                $query2->with(['project']);/*select('code','name','description','date_start','date_budget','cost','contact_id','system','documents','span','created_at','updated_at','deleted_at');*/
            }])->where('id', $adminid)->first();

        return $data;
    }

    public function ProjectsByFilterMap($info, $filter)
    {


        if ($filter == 'dateFrom') {

            $fecha = date("Y-m-d H:i:s", strtotime('-30 day'));

            //$fecha = date("Y-m-d H:i:s",$info);
            // $fecha = $info;

            $data = Project::with([
                'admins' => function ($query) {
                    $query->with(['adminDivision']);
                }])->where('created_at', '>=', $fecha)->get();

        }


        return $data;


    }


    public function store(Request $request)
    {
        $data = new AdministrativeDivisions();
        $data->name = $request->name;
        $data->code = 0;
        $data->pcode = 0;
        $data->country_id = 'COL';
        $data->parent_id = isset($request->parent_id) ? $request->parent_id : null;
        $data->level = isset($request->parent_id) ? 2 : 1;
        if ($data->save())
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false];
    }

    public function show($id)
    {
        $data = AdministrativeDivisions::where('id', $id)->first();
        if ($data)
            return ['status' => true, 'data' => $data];
        else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function update(Request $request, $id)
    {
        $data = AdministrativeDivisions::where('id', $id)->first();
        if ($data) {
            $data->name = $request->name;
            $data->code = 0;
            $data->pcode = 0;
            $data->parent_id = isset($request->parent_id) ? $request->parent_id : null;
            $data->level = isset($request->parent_id) ? 2 : 1;
            if ($data->save())
                return ['status' => true, 'data' => $data];
            else
                return ['status' => false];
        } else
            return ['status' => false, 'message' => 'El registro no fue encontrado'];
    }

    public function destroy($id)
    {
        $data = AdministrativeDivisions::where('id', $id)->delete();
        return ['status' => $data];
    }

    public function getByPagination()
    {
        if ($_GET['search']['value']) {
            $data = AdministrativeDivisions::with('parent')
                ->where('name', 'like', '%' . $_GET['search']['value'] . '%')
                ->offset($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        } else {
            $data = AdministrativeDivisions::with('parent')
                ->offset($_GET['start'])
                ->limit($_GET['length'])
                ->get();
        }
        $count = AdministrativeDivisions::count();
        return ['draw' => $_GET['draw'], 'recordsTotal' => $count, 'recordsFiltered' => $count, 'data' => $data, 'buscar' => $_GET['search']['value'] ? true : false];
    }

    public function getAllRegions()
    {
        return AdministrativeDivisions::with('childrens')
            ->where('level', '1')
            ->where('name', '!=', 'Nacional')
            ->get();
    }

    private function get_month_budget($date, $start, $end, $type, $budget, $dur)
    {
        $bud = 0;
        switch ($type) {
            case 's':
                $start = new DateTime("$start");
                $end = new DateTime("$date-12");
                $diff = $start->diff($end);
                $m = 1 + $diff->format('%y') * 12 + $diff->format('%m');
                if (isset($budget[1]) && isset($budget[1]['budget'])) {
                    $bud = $budget[1]['budget'];
                } else {
                    $bud = ($budget[0]['budget'] / 12) * $m;
                }
                break;
            case 'm':
                $st_t = new DateTime("$start");
                $e_t = new DateTime("$date-12");
                $diff_t = $st_t->diff($e_t);
                $y = $diff_t->format('%y') + ($diff_t->format('%m') > 0) ? 1 : 0;
                if (isset($budget[$y + 1]) && isset($budget[$y + 1]['budget'])) {
                    $bud = $budget[$y + 1]['budget'];
                } else {
                    $st = new DateTime("$start");
                    $e = new DateTime("$end");
                    $diff = $st->diff($e);
                    $tm = $diff->format('%y') * 12 + $diff->format('%m');
                    $bud = ($budget[0]['budget'] / $tm) * 12;
                }
                break;
            case 'e':
                $st_t = new DateTime("$start");
                $e_t = new DateTime("$date-12");
                $diff_t = $st_t->diff($e_t);
                $y = $diff_t->format('%y') + ($diff_t->format('%m') > 0) ? 1 : 0;
                if (isset($budget[$y + 1]) && isset($budget[$y + 1]['budget'])) {
                    $bud = $budget[$y + 1]['budget'];
                } else {
                    if ($dur) {
                        $st_t = new DateTime("$start");
                        $e_t = new DateTime("$date-00");
                        $diff = $st_t->diff($e_t);
                        $m = intval($dur) - $diff->format('%y') * 12 - $diff->format('%m');
                        $tm = intval($dur);
                    } else {
                        $st = new DateTime("$date-00");
                        $e = new DateTime("$end");
                        $diff = $st->diff($e);
                        $m = $diff->format('%y') * 12 + $diff->format('%m');
                        $stT = new DateTime("$start");
                        $eT = new DateTime("$end");
                        $diffT = $stT->diff($eT);
                        $tm = $diffT->format('%y') * 12 + $diffT->format('%m');
                    }
                    $bud = ($m <= 0) ? 0 : ($budget[0]['budget'] / $tm) * $m;
                }
                break;
        }
        return $bud;
    }

    private function getLatestParentPT($data)
    {
        $parent = ProjectTags::select('id', 'parent_id')->where('id', $data->parent_id)->first();
        if ($parent->parent_id)
            return $this->getLatestParentPT($parent);

        return $parent->id;
    }

    private function getLatestParentPST($data)
    {
        $parent = ProjectShortTags::select('id', 'parent_id')->where('id', $data->parent_id)->first();
        if ($parent->parent_id)
            return $this->getLatestParentPST($parent);

        return $parent->id;
    }

    /*private function tags_child($item)
    {
        if (count($item->childrens) > 0) {
            foreach ($item->childrens as $c) {
                $data = ProjectTags::where('parent_id', $c->id)->get();
                foreach ($data as $d)
                    $d = $this->tags_child($d);
                $c->childrens = $data;
            }
        }
        return $item;
    }

    public function short_tags_child($item)
    {
        if (count($item->childrens) > 0) {
            foreach ($item->childrens as $c) {
                $data = ProjectShortTags::where('parent_id', $c->id)->get();
                foreach ($data as $d)
                    $d = $this->short_tags_child($d);
                $c->childrens = $data;
            }
        }
        return $item;
    }*/

}
