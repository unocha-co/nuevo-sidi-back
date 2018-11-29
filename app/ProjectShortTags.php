<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectShortTags extends Model
{
    protected $table = "project_short_tags";

    public function childrens()
    {
        return $this->hasMany('App\ProjectShortTags', 'parent_id', 'id');
    }

    public function getNameAttribute()
    {
        return (isset($this->attributes['code'])) ? $this->attributes['code'] . ' ' . $this->attributes['name'] : $this->attributes['name'];
    }

    public function projectprojecttags()
    {
        return $this->hasMany('App\ProjectProjectShortTags', 'tag_id');
    }


}

