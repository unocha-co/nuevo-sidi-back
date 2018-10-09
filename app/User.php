<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function permissions_profile()
    {
        return $this->hasMany('App\ProfilePermissions', 'id_profile', 'user_profile_id');
    }

    public function auth0()
    {
        return $this->hasOne('App\UserAuth', 'user_id', 'id');
    }
    
}
