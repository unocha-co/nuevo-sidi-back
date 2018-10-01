<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    protected $table = "permissions";

    public function children()
    {
        return $this->hasMany('App\Permissions', 'parent', 'id');
    }

}
