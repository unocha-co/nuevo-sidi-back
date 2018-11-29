<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('administrativeMap','AdministrativeDivisionsController@indexMap');
Route::get('project-short-tags-parents', 'ProjectShortTagsController@pt_parents');
Route::get('project-tags-parents', 'ProjectTagsController@pt_parents');

Route::post('login', 'AuthController@login');
Route::post('user', 'UserController@store');

Route::group(['middleware' => 'jwt_auth0'],function(){  

	//Datatables Pagination
    Route::get('list-administrative', 'AdministrativeDivisionsController@getByPagination');
	Route::get('list-relation', 'OrganizationProjectRelationController@getByPagination');
	Route::get('list-organizations', 'OrganizationsController@getByPagination');
	Route::get('list-project', 'ProjectClassController@getByPagination');
	Route::get('list-contact', 'ContactGroupsController@getByPagination');
	Route::get('list-types', 'OrganizationTypesController@getByPagination');
	Route::get('list-userprofiles', 'UserProfilesController@getByPagination');
	Route::get('list-allprojects', 'ProjectController@getByPagination');

	//Resources
	Route::resource('administrative', 'AdministrativeDivisionsController');

	//para el mapa
	Route::get('AllProjectsOfAdmin/{idadmin}','AdministrativeDivisionsController@getAllprojectsOfAdmin'); //TRAE TODOS LSO PROYECTOS DE UNA DIVISION ADMINISTRATIVA


	Route::resource('relation', 'OrganizationProjectRelationController');
	Route::resource('organizations', 'OrganizationsController');
	Route::resource('project', 'ProjectController');

	//Ruta para el mapan, que debe consultar todos los projectos
    Route::get('projectsMap', 'ProjectController@projectsmap');

	Route::resource('projectclass', 'ProjectClassController');
	Route::resource('contact', 'ContactGroupsController');
	Route::resource('types', 'OrganizationTypesController');
	Route::resource('userprofiles', 'UserProfilesController');
	Route::resource('allprojects', 'ProjectController');
	Route::resource('contacts', 'ContactsController');
	Route::resource('project_beneficiaries_groups', 'ProjectBGController');
	Route::resource('hrp', 'HrpController');
	Route::resource('project_tags', 'ProjectTagsController');
	Route::resource('project_tags_rel', 'ProjectProjectTagsController');
    Route::resource('project_short_tags', 'ProjectShortTagsController');
    Route::resource('project_short_tags_rel', 'ProjectProjectShortTagsController');
    Route::resource('project_beneficiaries_groups', 'ProjectBGController');
 
    //FILTRO POR CIUDAD
    Route::get('filtroProjectsByAdmin/{adminid}','AdministrativeDivisionsController@filtroProjectsByAdmin');

    //FILTRO POR FECHA
    Route::get('ProjectsByFilterMap/{info}/{filter}','AdministrativeDivisionsController@ProjectsByFilterMap');


	//Custom
	Route::post('validatePermission', 'UserController@validatePermission');
	Route::get('getAllRegions', 'AdministrativeDivisionsController@getAllRegions');
	Route::post('step3/{id}', 'ProjectController@step3');
});

   
