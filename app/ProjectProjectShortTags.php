<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectProjectShortTags extends Model
{
    protected $table = "projects_project_short_tags"; 


     public function shorttag(){
    	return $this->belongsTo('App\ProjectShortTags', 'tag_id');
    }
}