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
use App\ProjectProjectTags;
use App\ProjectProjectShortTags;

use App\Contacts;
use App\Budget;

use App\ProjectBeneficiaries;


//NUEVAS RELACIONES PARA PROJECTS
use App\OrganizationProjectRelation;
use App\ProjectOrganization;

use App\OrganizationTypes;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

//use Maatwebsite\Excel\Excel;
//use Excel;
//use Maatwebsite\Excel\Facades\Excel;
//use Racklin\ExcelGenerator\ExcelGenerator;
use Rap2hpoutre\FastExcel\FastExcel;


use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;

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
        $columns = array('id', 'date_start', 'date_end', 'span','name','code');
        if (Input::get('pi') && Input::get('pi') == 1) {
            array_push($columns, 'name', 'description');

            
        } else if(Input::get('pi') && (Input::get('pi')==2)){
           // $implementers_id = OrganizationProjectRelation::where('name', 'Socio')->first()->id;

                array_push($columns, 'code','name','contact_id','description','date_budget','span','documents');

           
              $with['org'] = function ($query) {
                $query->with([
                    'org'=> function ($query2){
                        $query2->with(['parent'=> function($query3){
                            $query3->select('id');
                        }

                    ])->select('id','name','acronym','organization_type_id');
                   
               }]);

            };
             $with['admins'] = function ($query) {
                $query->with([
                    'adminDivision' => function ($query2) {
                        $query2->select('id', 'name', 'parent_id', DB::raw('ST_X(geom_center) AS x, ST_Y(geom_center) AS y'));
                    }
                ]);
            };

        }
            else{
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
        }else if(Input::get('pi') && Input::get('pi') == 2){


        
            $datalist = collect([]);
            $datalist[0] = [
            'Código 4w'=>'', 
            'Información Básica'=>'Código interno',
            ' '=>'Tipo de Proyecto*',
            '  '=>'Nombre del proyecto*',
            '   '=>'Descripción del proyecto*',
            'Organizacón ejecutora'=>'Sigla*',
            '    '=>'Nombre*', 
            '     '=>'Tipo*',
            'Implementadores'=>'Sigla (Sep. por guión)',
            '      '=>'Nombre (Sep. por guión)*',
            '       '=>'Tipo (Sep. por guión)*',
            'Sectores Humanitarios*'=>'Separados por guión(-)',
            'Resultados esperados UNDAF*'=>'Separados por guión(-)',
            'Tiempo de ejecución*'=>'Fecha de inicio*',
            '        '=>'Fecha de finalización*',
            '         '=>'Meses*',
            'Estado Proyecto*'=>'          ',
            'Presupuesto del proyecto'=>'Presupuesto Total(USD)*',
            '           '=>'Presupuesto Año 1 (USD)',
            '            '=>'Presupuesto Año 2 (USD)',
            '             '=>'Presupuesto Año 3 (USD)',
            '              '=>'Presupuesto Año 4 (USD)',
            '               '=>'Presupuesto Año 5 (USD)',
            'Donantes (Fuente de los recursos)'=>'Nombre (Sep. por guión)',
            '                '=>'Monto (USD)(Sep. por guión)',
            //
            'Adjudicación de recursos'=>'Fecha',
            'SRP*'=>'Hace parte del plan estratégico de respuesta? (0 o 1)',
            'Contacto en Terreno'=>'Nombre del responsable*',
            '                 '=>'Correo Electrónico',
            '                  '=>'Celular*',
            //BENEFICIARIOS
            'Beneficiarios poblacionales'=>'Total beneficiarios*',
            '                   '=>'Total Mujeres',
            '                    '=>'Mujeres 0-5 años',
            '                     '=>'Mujeres 6-18 años',
            '                      '=>'Mujeres 18-64 años',
            '                       '=>'Mujeres 65+ años',
            '                        '=>'Total hombres',
            '                         '=>'Hombres 0-5 años',
            '                          '=>'Hombres 6-18 años',
            '                           '=>'Hombres 18-64 años',
            '                            '=>'Hombres 65+ años',
            '                             '=>'Num. de víctimas de conflicto',
            '                              '=>'Num. afectados por desastres',
            '                               '=>'Num. desmovilizados/Reinsertados',
            '                                '=>'Num. afrocolombianos',
            '                                 '=>'Num. Indígenas',
            'Beneficiarios indirectos'=>'Total Beneficiarios indirectos',
            '                                  '=>'Total mujeres',
            '                                   '=>'Total hombres',
            'Beneficiarios No-poblacionales (Organizaciones)'=>'Sigla',
            '                                    '=>'Nombre',
            '                                     '=>'Tipo',
            'Cobertura Geográfica'=>'Código División Político-Administrativa (Sep. por coma)',
            '                                      '=>'Departamento* (Sep. por coma)' ,
            '                                       '=>'Municipio* (Sep. por coma)',
            '                                        '=>'Latitud(Sep. por coma)',
            '                                         '=>'Longitud(Sep. por coma)',
            'Interagencial*'=>'Cumple los requisitos de interagencialidad? (0 ó 1)',
            'Cash Based Transfer'=>'Modalidad de Asistencia',
            '                                          '=>'Mecanismo de entrega',
            '                                           '=>'Frecuencia de distribución',
            '                                            '=>'Valor por persona (USD)',
            'Soportes del proyecto'=>'                                             ',
            'Acuerdos de Paz'=>'Códigos de subtema(Sep. por guión)',
            'Clasificación CAD'=>'Códigos de clasificación CAD(Sep. por guión)',
            'Clasificación ODS'=>'Códigos de clasificación ODS',
            'Emergencias'=>'Nombre de la emergencia'

             ];


          for($i=0; $i<count($pa_c);$i++) {

              $implementadoresname = [];
              $implementadoresacronym = [];
              $implementadorestipo = [];

              $donantesname = [];
              $donantesmonto = [];

               foreach($pa_c[$i]['org'] as $organizacion){
                  if( $organizacion['relation_id'] == 1){
                    $pa_c[$i]['orgejecutoranombre'] = $organizacion['org']['name'];
                    $pa_c[$i]['orgejecutorasigla'] = $organizacion['org']['acronym'];
                    $pa_c[$i]['orgejecutoratype'] = OrganizationTypes::where('id',$organizacion['org']['organization_type_id'])->select('id','type')->first();

                    }else if($organizacion['relation_id'] == 2){
                     $donantesname[] = $organizacion['org']['name'];
                     $donantesmonto[] = $organizacion['value'];

                    }
                    else if($organizacion['relation_id'] == 3){
                       $implementadoresname[] =  $organizacion['org']['name'];
                       // if($organizacion['org']['acronym']){
                        $implementadoresacronym[] =  $organizacion['org']['acronym'];

                        $tipoorgimple = OrganizationTypes::where('id',$organizacion['org']['organization_type_id'])->select('id','type')->first();

                         $implementadorestipo[] = $tipoorgimple['type'];


                       // }
                     }


              }

             $pa_c[$i]['implementadoresname'] = implode(',',$implementadoresname);
             $pa_c[$i]['implementadoresacronym'] = implode(',',$implementadoresacronym);
             $pa_c[$i]['implementadorestipo'] = implode(',',$implementadorestipo);

             $pa_c[$i]['donantesname'] = implode('-',$donantesname);
             $pa_c[$i]['donantesmonto'] = implode('-',$donantesmonto);


            $pa_c[$i]['contactoenterreno'] =  Contacts::where('id',$pa_c[$i]['contact_id'])->select('id',DB::raw("CONCAT(first_name,' ',last_name) as name"),'email', 'cellphone')->first();





            //BENEFICIARIES

            $beneficiariesindirectos = ProjectBeneficiaries::where('project_id',$pa_c[$i]['id'])->select('project_id','group_id','gender','age','type','number')->get();

            

            $indirectosbene = []; //para beneficiarios indirectos
            $poblacionalesbene = []; //para beneficiarios poblacionales

            
            foreach ($beneficiariesindirectos as $ben){

                if($ben['type']==2){

                    $indirectosbene[] = $ben;

                }else if($ben['type']==1)
                {
                    $poblacionalesbene[] = $ben;

                }

            }

            //INDIRECTOS BENEFICIARIOS

            $totalindirectosben = 0;
            $totalindirectosmujeres = 0;
            $totalindirectoshombres = 0;


            foreach($indirectosbene as $inben){

                if($inben['gender'] == null){
                    $totalindirectosben = $inben['number'];
                }else if($inben['gender'] == 'm' && $inben['age']== null){

                    $totalindirectosmujeres = $totalindirectosmujeres + $inben['number'];

                }else if($inben['gender'] == 'h' && $inben['age']== null){
                    $totalindirectoshombres = $totalindirectoshombres + $inben['number'];
                }

            }

            $pa_c[$i]['totalbeneficiariosIndirectos'] = $totalindirectosben;
            $pa_c[$i]['totalbeneficiariosIndirectosMujeres'] = $totalindirectosmujeres;
            $pa_c[$i]['totalbeneficiariosIndirectosHombres'] = $totalindirectoshombres;


            //Grupos

            $victimasdelconflicto = 0; //grupo1
            $afectadospordesastres = 0; //grupo2
            $desmovilizadosreinsertados = 0; //grupo3
            $afrocolombianos = 0; //grupo4
            $indigenas = 0; //grupo5

             //BENEFICIARIOS POBLACIONALES directos

            $totalbeneficiariospoblacionales = 0;
            $totalbeneficiariospoblacionalesmujeres = 0;
            $totalbeneficiariospoblacionaleshombres = 0;


            $totalmujeresage1 = 0; //mujeres 0-5
            $totalmujeresage2 = 0; //mujeres 6-17
            $totalmujeresage3 = 0; //mujeres 18-64
            $totalmujeresage4 = 0; //mujeres 65+años

            $totalhombresage1 = 0; //hombres 0-5
            $totalhombresage2 = 0; //hombres 6-17
            $totalhombresage3 = 0; //hombres 18-64
            $totalhombresage4 = 0; //hombres 65+años


            foreach($poblacionalesbene as $poblben){

                if($poblben['group_id'] != null){ //para los grupos

                    if($poblben['group_id'] == '1'){

                        $victimasdelconflicto = $poblben['number'] ;

                    }else if($poblben['group_id'] == '2'){

                        $afectadospordesastres = $poblben['number'];

                    }else if($poblben['group_id'] == '3'){

                        $desmovilizadosreinsertados = $poblben['number'];

                    }else if($poblben['group_id'] == '4'){

                        $afrocolombianos = $poblben['number'];

                    }else if($poblben['group_id'] == '5'){

                        $indigenas = $poblben['number'];

                    }


                }else if($poblben['group_id'] == null && $poblben['gender'] == null && $poblben['age'] == null){

                   $pa_c[$i]['totalbeneficiariospoblacionales'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'm' && $poblben['age'] == null){

                    $pa_c[$i]['totalbeneficiariospoblacionalesmujeres'] =  $poblben['number'];

                }//mujeres
                else if($poblben['group_id'] == null && $poblben['gender'] == 'm' && $poblben['age'] == '1'){

                    $pa_c[$i]['totalmujeresage1'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'm' && $poblben['age'] == '2'){

                    $pa_c[$i]['totalmujeresage2'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'm' && $poblben['age'] == '3'){

                    $pa_c[$i]['totalmujeresage3'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'm' && $poblben['age'] == '4'){

                    $pa_c[$i]['totalmujeresage4'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == null){

                    $pa_c[$i]['totalbeneficiariospoblacionaleshombres'] =  $poblben['number'];

                }//hombres
                else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == '1'){

                    $pa_c[$i]['totalhombresage1'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == '2'){

                    $pa_c[$i]['totalhombresage2'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == '3'){

                    $pa_c[$i]['totalhombresage3'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == '4'){

                    $pa_c[$i]['totalhombresage4'] =  $poblben['number'];

                }else if($poblben['group_id'] == null && $poblben['gender'] == 'h' && $poblben['age'] == '5'){

                    $pa_c[$i]['totalhombresage5'] =  $poblben['number'];

                }

            }

            $pa_c[$i]['totalvictimasdelconflicto'] = $victimasdelconflicto;
             $pa_c[$i]['afectadospordesastres'] = $afectadospordesastres;
             $pa_c[$i]['desmovilizadosreinsertados'] = $desmovilizadosreinsertados;
              $pa_c[$i]['afrocolombianos'] = $afrocolombianos;
              $pa_c[$i]['indigenas'] = $indigenas;


            $pa_c[$i]['beneficiariesindirectos'] = implode(' - ',$indirectosbene);
            
            //BENEFICIARIOS NO POBLACIONALES
            $beneficiariosnopoblacionales = ProjectOrganization::with(['org:id,name,acronym,organization_type_id'])
                ->where('project_id',$pa_c[$i]['id'])
                ->where('relation_id', 5)
                ->select(['organization_id'])
                ->get();


            $benefinopoblacionalesacronym = [];

            $benefinopoblacionalesnombres = [];

            $benefinopoblacionalestipo = [];



              foreach($beneficiariosnopoblacionales as $benefnopobl){

                $benefinopoblacionalesacronym[] = $benefnopobl['org']['acronym'];
                $benefinopoblacionalesnombres[] =  $benefnopobl['org']['name'];

                $tipoorgnopoblacionales = OrganizationTypes::where('id',$benefnopobl['org']['organization_type_id'])->select('id','type')->first();

                $benefinopoblacionalestipo[] = $tipoorgnopoblacionales['type'];


              }
                $pa_c[$i]['benefinopoblacionalesacronym'] = implode('-',$benefinopoblacionalesacronym);

                $pa_c[$i]['benefinopoblacionalesnombres'] = implode('-',$benefinopoblacionalesnombres);

                $pa_c[$i]['benefinopoblacionalestipo'] = implode('-',$benefinopoblacionalestipo);


             
              //TAGS
             $pa_c[$i]['tags']= ProjectProjectTags::with(['tag'=> function($query){
                $query->select('id','name','parent_id','code');
             }])->distinct('project_id')->where('project_id',$pa_c[$i]['id'])->get();


             $arraytags = [];

             foreach($pa_c[$i]['tags'] as $tag){

                $arraytags[] = $tag['tag']['name'];

             }

             $arraytagsnames = implode(',',$arraytags);

             $pa_c[$i]['tags']->each(function ($item, $key) {
                $item['tag']['parent_id'] = $this->getLatestParentPTexcel($item);
            });

             $resultadosUNDAFM = [];

             foreach($pa_c[$i]['tags'] as $tag){

                if($tag['tag']['parent_id'] == 4){

                    $resultadosUNDAFM[] = $tag['tag']['name'];

                }
             }

             $pa_c[$i]['resultadosUNDAFM'] =implode('-',$resultadosUNDAFM);


             $resultadosCluster = [];
             foreach($pa_c[$i]['tags'] as $tag){

                if($tag['tag']['parent_id'] == 2){

                    $resultadosCluster[] = $tag['tag']['name'];

                }
             }

             $pa_c[$i]['sectoresHumanitarios'] =implode('-',$resultadosCluster);



            $resultadosAcuerdosDePaz = [];
             foreach($pa_c[$i]['tags'] as $tag){

                if($tag['tag']['parent_id'] == 5){

                    $resultadosAcuerdosDePaz[] = $tag['tag']['code'];

                }
             }
             $pa_c[$i]['acuerdosdepaz'] = implode('-',$resultadosAcuerdosDePaz);



             $resultadosODS = [];
             foreach($pa_c[$i]['tags'] as $tag){

                if($tag['tag']['parent_id'] == 7){

                    $resultadosODS[] = $tag['tag']['code'];

                }
             }
             $pa_c[$i]['clasificacionODS'] = implode(',',$resultadosODS);




              $resultadosDAC = [];
             foreach($pa_c[$i]['tags'] as $tag){

                if($tag['tag']['parent_id'] == 7){

                    $resultadosDAC[] = $tag['tag']['code'];

                }
             }
             $pa_c[$i]['clasificacionDAC'] = implode('-',$resultadosDAC);



             //SHORTTAGS

             $pa_c[$i]['shorttags']= ProjectProjectShortTags::with(['shorttag'=> function($query){
                $query->select('id','name','parent_id');
             }])->distinct('project_id')->where('project_id',$pa_c[$i]['id'])->get();

             $arrayshorttags = [];
              



             foreach($pa_c[$i]['shorttags'] as $tag){

                $arrayshorttags[] = $tag['shorttag']['name'];


                if($tag['shorttag']['parent_id'] == 10){ //Tipo proyecto

                     
                    $pa_c[$i]['tipoproyecto'] = $tag['shorttag']['name'];

                }

                


                if($tag['shorttag']['parent_id'] == 1){ //Estado proyecto

                    $pa_c[$i]['estadoproyecto'] = $tag['shorttag']['name'];

                }

               

               
                 if($tag['shorttag']['parent_id'] == 23){//MEcanismo de entrega

                    $pa_c[$i]['mecanismoentrega'] = $tag['shorttag']['name'];

                }
                

                if($tag['shorttag']['parent_id'] == 18){//Modalidad de asistencia

                    $pa_c[$i]['modalidadasistencia'] = $tag['shorttag']['name'];

                }
                



                if($tag['shorttag']['parent_id'] == 16){//Es interegencialidad

                    $pa_c[$i]['interegencialidad'] = 1;

                }



                if($tag['shorttag']['parent_id'] == 13){//plan estrategico de respuesta

                    $pa_c[$i]['haceparteplanestrategrespuesta'] = 1;

                }

                if($tag['shorttag']['parent_id'] == 7){//plan estrategico de respuesta

                    $pa_c[$i]['emergencia'] = $tag['shorttag']['name'];

                }



             }

             if($pa_c[$i]['interegencialidad'] != 1){
                $pa_c[$i]['interegencialidad'] = 0;
             }

             if($pa_c[$i]['haceparteplanestrategrespuesta'] != '1'){
                $pa_c[$i]['haceparteplanestrategrespuesta'] = 0;
             }

             $arrayshorttagsnames = implode(',',$arrayshorttags);


             //ADministrative Divisions - project

             $admdivcodigos = [];
             $admdivlatitudes = [];
             $admdivlongitudes = [];
             $admdivdepartamentos = [];
             $admdivmunicipios = [];

             foreach($pa_c[$i]['admins'] as $admdivp){

                $admdivcodigos[]= $admdivp['admin_id'];
                $admdivlatitudes[]= $admdivp['adminDivision']['y'];
                $admdivlongitudes[]= $admdivp['adminDivision']['x'];

                if($admdivp['adminDivision']['parent_id'] == null ){

                    $admdivdepartamentos[] = $admdivp['adminDivision']['name'];


                }else if($admdivp['adminDivision']['parent_id'] != null){

                    $admdivmunicipios[]  = $admdivp['adminDivision']['name'];

                }


             }

             $pa_c[$i]['departamentos'] = implode(',',$admdivdepartamentos);

             $pa_c[$i]['municipios'] = implode(',',$admdivmunicipios);

             $pa_c[$i]['admindivcodigos'] = implode(',',$admdivcodigos);

             $pa_c[$i]['admindivlatitudes'] = implode(',',$admdivlatitudes);

             $pa_c[$i]['admindivlongitudes'] = implode(',',$admdivlongitudes);


              //BUDGETS 
             $pa_c[$i]['budget']= Budget::distinct('project_id')->where('project_id',$pa_c[$i]['id'])->select('budget')->get();

             foreach($pa_c[$i]['budget'] as $budget){

                if($pa_c[$i]['budget'][0] != null){

                    $pa_c[$i]['budgettotal'] = $pa_c[$i]['budget'][0]['budget'];

                }

                if(!empty($pa_c[$i]['budget'][1])){

                    $pa_c[$i]['budgetaño1'] = $pa_c[$i]['budget'][1]['budget'];

                }else{
                    $pa_c[$i]['budgetaño1'] = 0;
                }
                
                if(!empty($pa_c[$i]['budget'][2])){

                    $pa_c[$i]['budgetaño2']= $pa_c[$i]['budget'][2]['budget'];

                }else{
                    $pa_c[$i]['budgetaño2'] = 0;

                }

                if(!empty($pa_c[$i]['budget'][3])){

                    $pa_c[$i]['budgetaño3'] = $pa_c[$i]['budget'][3]['budget'];

                }else{
                    $pa_c[$i]['budgetaño3'] = 0;
                }

                if(!empty($pa_c[$i]['budget'][4])){

                    $pa_c[$i]['budgetaño4'] = $pa_c[$i]['budget'][4]['budget'];

                }else{
                    $pa_c[$i]['budgetaño4'] = 0;
                }

                if(!empty($pa_c[$i]['budget'][5])){

                    $pa_c[$i]['budgetaño5'] = $pa_c[$i]['budget'][5]['budget'];

                }else{
                    $pa_c[$i]['budgetaño5'] = 0;
                }



             }

         if(!empty($pa_c[$i]['beneficiaries'][0])){
            $beneficiariostotal = $pa_c[$i]['beneficiaries'][0]['total'];

         }else {
            $beneficiariostotal = 0;
         }

            
              $datalist[$i+2] = [
                'Código 4w' => $pa_c[$i]['id'],
                 'Código interno' => $pa_c[$i]['code'],
                 'Tipo de Proyecto*'=>$pa_c[$i]['tipoproyecto'],
                 'Nombre del proyecto*'=>$pa_c[$i]['name'],
                 'Descripción del proyecto*'=>$pa_c[$i]['description'],
                 'Sigla*'=>$pa_c[$i]['orgejecutorasigla'],
                 'Nombre*'=>$pa_c[$i]['orgejecutoranombre'],
                 'Tipo*'=>$pa_c[$i]['orgejecutoratype']['type'],
                 'Sigla (Sep. por guión)'=>$pa_c[$i]['implementadoresacronym'],
                 'Nombre (Sep. por guión)*'=>$pa_c[$i]['implementadoresname'],
                 'Tipo (Sep. por guión)*'=>$pa_c[$i]['implementadorestipo'],
                 'Separados por guión(-)'=> $pa_c[$i]['sectoresHumanitarios'],
                 'Separados por guión (-)'=>$pa_c[$i]['resultadosUNDAFM'],
                 'Fecha de inicio*'=>$pa_c[$i]['date_start'],
                 'Fecha de finalización*'=>$pa_c[$i]['date_end'],
                 'Meses*'=>$pa_c[$i]['span'],
                 '          '=>$pa_c[$i]['estadoproyecto'],
                'Presupuesto Total(USD)*'=>$pa_c[$i]['budgettotal'],
                'Presupuesto Año 1 (USD)'=>$pa_c[$i]['budgetaño1'],
                'Presupuesto Año 2 (USD)'=>$pa_c[$i]['budgetaño2'],
                'Presupuesto Año 3 (USD)'=>$pa_c[$i]['budgetaño3'],
                'Presupuesto Año 4 (USD)'=>$pa_c[$i]['budgetaño4'],
                'Presupuesto Año 5 (USD)'=>$pa_c[$i]['budgetaño5'],
                'Nombre (Sep. por guión)'=>$pa_c[$i]['donantesname'],
                'Monto (USD)(Sep. por guión)'=>$pa_c[$i]['donantesmonto'],
                'Fecha'=> $pa_c[$i]['date_budget'],
                'Hace parte del plan estratégico de respuesta? (0 o 1)'=>$pa_c[$i]['haceparteplanestrategrespuesta'],
                'Nombre del responsable*'=>$pa_c[$i]['contactoenterreno']['name'],
                'Correo Electrónico*'=>$pa_c[$i]['contactoenterreno']['email'],
                'Celular*'=>$pa_c[$i]['contactoenterreno']['cellphone'],
                'Total beneficiarios*'=>$pa_c[$i]['totalbeneficiariospoblacionales'],
                'Total mujeres'=>$pa_c[$i]['totalbeneficiariospoblacionalesmujeres'],
                'Mujeres 0-5 años'=>$pa_c[$i]['totalmujeresage1'],
                'Mujeres 6-18 años'=>$pa_c[$i]['totalmujeresage2'],
                'Mujeres 18-64 años'=>$pa_c[$i]['totalmujeresage3'],
                'Mujeres 65+ años'=>$pa_c[$i]['totalmujeresage4'],
                'Total hombres'=>$pa_c[$i]['totalbeneficiariospoblacionaleshombres'],
                'Hombres 0-5 años'=>$pa_c[$i]['totalhombresage1'],
                'Hombres 6-18 años'=>$pa_c[$i]['totalhombresage2'],
                'Hombres 18-64 años'=>$pa_c[$i]['totalhombresage3'],
                'Hombres 65+ años'=>$pa_c[$i]['totalhombresage4'],
                'Num. de víctimas de conflicto'=>$pa_c[$i]['totalvictimasdelconflicto'],
                'Num. afectados por desastre'=>$pa_c[$i]['afectadospordesastres'],
                'Num. desmovilizados/Reinsertados'=>$pa_c[$i]['desmovilizadosreinsertados'],
                'Num. afrocolombianos'=>$pa_c[$i]['afrocolombianos'],
                'Num. Indígenas'=>$pa_c[$i]['indigenas'],
                'Total Beneficiarios indirectos'=>$pa_c[$i]['totalbeneficiariosIndirectos'],
                 'Total mujeres '=>$pa_c[$i]['totalbeneficiariosIndirectosMujeres'],
                 'Total hombres '=>$pa_c[$i]['totalbeneficiariosIndirectosHombres'],
                 'Sigla'=>$pa_c[$i]['benefinopoblacionalesacronym'],
                  'Nombre'=>$pa_c[$i]['benefinopoblacionalesnombres'],
                 'Tipo'=>$pa_c[$i]['benefinopoblacionalestipo'],
                 'Código División Político-Administrativa (Sep. por coma)'=>$pa_c[$i]['admindivcodigos'],
                  'Departamento* (Sep. por coma)'=>$pa_c[$i]['departamentos'],
                  'Municipio* (Sep. por coma)'=>$pa_c[$i]['municipios'],
                  'Latitud(Sep. por coma)'=>$pa_c[$i]['admindivlatitudes'],
                   'Longitud(Sep. por coma)'=>$pa_c[$i]['admindivlongitudes'],
                   'Cumple los requisitos de interagencialidad? (0 ó 1)'=>$pa_c[$i]['interegencialidad'],
                   'Modalidad de Asistencia'=>$pa_c[$i]['modalidadasistencia'],
                   'Mecanismo de entrega'=>$pa_c[$i]['mecanismoentrega'],
                   'Frecuencia de distribución'=>$pa_c[$i]['frecuenciadistribucion'],
                   'Valor por persona (UDS)'=>$pa_c[$i]['valpersona'],
                   '                                             '=>$pa_c[$i]['documents'],
                  'Códigos de subtema(Sep. por guión)'=>$pa_c[$i]['acuerdosdepaz'],
                  'Códigos de clasificación CAD(Sep. por guión)'=>$pa_c[$i]['clasificacionDAC'],
                  'Códigos de clasificación ODS'=>$pa_c[$i]['clasificacionODS'],
                  'Nombre de la emergencia'=>$pa_c[$i]['emergencia']

                 ];

                        
               }

               $style = (new StyleBuilder())
               ->setShouldWrapText(false)
               ->build();
           return    (new FastExcel( $datalist))->headerStyle($style)->download('proyectos_4w.xlsx');
               

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
            $fil['org'] = ProjectOrganization::select('organization_id', 'relation_id','value','project_id')->with(['org:id,name,acronym'])
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

        $a= AdministrativeDivisions::select('id','name','parent_id')->with(['childrens' => function($q){
	        $q->select('id','name','parent_id');
        }])->where('level', '1')
            ->where('name', '!=', 'Nacional')
            ->get();

	    return $a;
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


    private function getLatestParentPTexcel($data)
    {
        if($data['tag']){
        $parent = ProjectTags::select('id', 'parent_id')->where('id', $data['tag']['parent_id'])->first();
        }else{
            $parent = ProjectTags::select('id', 'parent_id')->where('id', $data->parent_id)->first();

        }
        if ($parent->parent_id)
            return $this->getLatestParentPTexcel($parent);

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
