<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectOrganization extends Model
{
    protected $table = "projects_organizations";

    public function projects(){
        return $this->hasMany('App\Project', 'id', 'project_id');
    }

    public function org(){
        return $this->belongsTo('App\Organizations', 'organization_id');
    }

    public function p_org(){
        return $this->hasMany('App\ProjectOrganization', 'organization_id', 'organization_id');
    }
}
