<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectProjectTags extends Model
{
    protected $table = "projects_project_tags"; 


    public function tag(){
    	return $this->belongsTo('App\ProjectTags', 'tag_id');
    }
}
   