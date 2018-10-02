<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTags extends Model
{
    protected $table = "project_tags"; 

    public function childrens()
    {
        return $this->hasMany('App\ProjectTags', 'parent_id', 'id');
    }
}
