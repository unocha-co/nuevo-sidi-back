<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdministrativeDivisions extends Model
{
    protected $table = "administrative_divisions";

    public function parent()
    {
        return $this->hasOne('App\AdministrativeDivisions', 'id', 'parent_id');
    }

    public function childrens()
    {
        return $this->hasMany('App\AdministrativeDivisions', 'parent_id', 'id');
    }

    public function projects()
    {
        return $this->hasMany('App\ProjectAdmin', 'admin_id', 'id');
    }


    /*public function projects(){
    return $this->hasMany(Project::class,'ProjectAdmin','admin_id');
       }*/


}


