<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organizations extends Model
{
    protected $table = "organizations";

    public function parent()
    {
        return $this->hasOne('App\OrganizationTypes', 'id', 'organization_type_id');
    }
}
