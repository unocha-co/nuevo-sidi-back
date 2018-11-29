<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectAdmin extends Model
{
    protected $table = "projects_admins";

    protected $fillable = [
        'project_id', 'admin_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function adminDivision()
    {
        return $this->belongsTo(AdministrativeDivisions::class, 'admin_id');
    }

}
