<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfilePermissions extends Model
{
    protected $table = "permissions_profile";

    public function permissions()
    {
        return $this->hasMany('App\Permissions', 'id', 'profile_id');
    }
}
