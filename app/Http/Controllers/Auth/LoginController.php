<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Symfony\Component\HttpFoundation\Request;
// use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    // use ListensForLdapBindFailure;

    protected $redirectTo = '/';

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        //
    }

    // protected function credentials(Request $request)
    // {

    //     return [
    //         'samaccountname' => $request->username,
    //         'password' => $request->password,
    //         'fallback' => [
    //             'email' => $request->email,
    //             'password' => $request->password,
    //         ],
    //     ];

    // }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        // $this->listenForLdapBindFailure();
    }

}