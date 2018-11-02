<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectProjectTags extends Model
{
    protected $table = "projects_project_tags"; 


    /*public function tags(){
    	return $this->hasMany('App\ProjectTags', 'tag_id');
    }*/
}
