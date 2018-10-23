<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserAuth;
use Auth0\Login\Contract\Auth0UserRepository;
use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\InvalidTokenException;
use Closure;
use App\Permissions;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(Auth0UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function store(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $userauth = UserAuth::where('user_id', $user->id)->first();
            if ($userauth) {
                $userauth->auth0_id = $request->user_id;
                $userauth->relation_date = time();
                $userauth->linked = 1;
            } else {
                $userauth = new UserAuth();
                $userauth->auth0_id = $request->user_id;
                $userauth->user_id = $user->id;
                $userauth->relation_date = time();
                $userauth->linked = 1;
            }
            $save = $userauth->save();
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->organization_id = 0;
            $user->user_profile_id = 0;
            $save = $user->save();
            $userauth = new UserAuth();
            $userauth->auth0_id = $request->user_id;
            $userauth->user_id = $user->id;
            $userauth->relation_date = time();
            $userauth->linked = 1;
            $userauth->save();
        }
        if ($save) {
            $user->navigation = $this->getPermissions($request->bearerToken());
            return ['status' => true, 'data' => $user];
        } else
            return ['status' => false];
    }

    public function getPermissions($accessToken)
    {
        $auth0 = \App::make('auth0');
        $tinfo = $auth0->decodeJWT($accessToken);
        $user = UserAuth::with('user')->where('auth0_id', $tinfo->sub)->first()->user;
        $permissions = Permissions::join('permissions_profile', 'permissions_profile.id_permission', '=', 'permissions.id')
            ->select('permissions.id', 'permissions.name', 'permissions.url', 'permissions.icon', 'permissions.title')
            ->where('permissions_profile.id_profile', $user->user_profile_id)
            ->whereNull('permissions.parent')
            ->get();
        foreach ($permissions as $p) {
            $p->title = $p->title == 1 ? true : false;
            $childrens = Permissions::join('permissions_profile', 'permissions_profile.id_permission', '=', 'permissions.id')
                ->select('permissions.name', 'permissions.url', 'permissions.icon')
                ->where('permissions_profile.id_profile', $user->user_profile_id)
                ->where('permissions.parent', $p->id)
                ->get();
            if (count($childrens) > 0)
                $p->children = $childrens;
        }
        return $permissions;
    }

    public function validatePermission(Request $request)
    {
        $auth0 = \App::make('auth0');
        $tinfo = $auth0->decodeJWT($request->bearerToken());
        $user = UserAuth::with('user')->where('auth0_id', $tinfo->sub)->first()->user;
        $permission = Permissions::join('permissions_profile', 'permissions_profile.id_permission', '=', 'permissions.id')
            ->select('permissions.id', 'permissions.name', 'permissions.url', 'permissions.icon', 'permissions.title')
            ->where('permissions_profile.id_profile', $user->user_profile_id)
            ->where('permissions.url', $request->url)
            ->first();
        return ['status' => true, 'granted' => $permission ? true : false];
    }

}
