<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = "projects";

    public function budget()
    {
        return $this->hasMany('App\Budget', 'project_id', 'id');
    }

    public function location()
    {
        return $this->hasMany('App\ProjectAdmin', 'project_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany('App\ProjectProjectTags', 'project_id', 'id');
    }

    public function beneficiaries()
    {
        return $this->hasMany('App\ProjectBeneficiaries', 'project_id', 'id');
    }
}
