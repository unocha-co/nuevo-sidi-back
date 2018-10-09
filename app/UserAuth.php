<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    protected $table = "users_auth0";
    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
