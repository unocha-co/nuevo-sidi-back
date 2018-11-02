<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTags extends Model
{
    protected $table = "project_tags"; 

    protected $fillable = [
        'name','code',
    ];

    public function childrens()
    {
        return $this->hasMany('App\ProjectTags', 'parent_id', 'id');
    }

      public function getNameAttribute()
    {
         return $this->attributes['code'] . ' ' . $this->attributes['name'];
    }

    public function projectprojecttags(){
        return $this->hasMany('App\ProjectProjectTags', 'tag_id');
    }

}
