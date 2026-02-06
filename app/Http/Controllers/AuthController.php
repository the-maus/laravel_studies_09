<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login() : View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request) 
    {
        // validate form
        $credentials = $request->validate(
            [
                'username' => 'required|min:3|max:30',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            [
                'username.required' => 'User field is required',
                'username.min'      => 'User field must have at least :min characters',
                'username.max'      => 'User field can\'t have more than :max characters',
                'password.required' => 'Password field is required',
                'password.min'      => 'Password field must have at least :min characters',
                'password.max'      => 'Password field can\'t have more than :max characters',
                'password.regex'    => 'Password must contain at least one lower-case letter, one upper-case letter and a number'
            ]
        );

        // laravel default login
        // if (Auth::attempt($credentials)) { // considers "email" and "password" fields (validates user/password, sets user info on session)
        //     $request->session()->regenerate();
        //     return redirect()->route('home');
        // } // use only if the request has email/password fields


        // check if user exists
        $user = User::where('username', $credentials['username'])
                    ->where('active', true)
                    ->where(function($query){
                        $query->whereNull('blocked_until')
                              ->orWhere('blocked_until', '<=', now());
                    })
                    ->whereNotNull('email_verified_at')
                    ->whereNull('deleted_at')
                    ->first();

        if(!$user)
            return back()->withInput()->with(['invalid_login' => 'Invalid login']);

        // check if password is valid
        if(!password_verify($credentials['password'], $user->password))
            return back()->withInput()->with(['invalid_login' => 'Invalid login']);

        // update last login 
        $user->last_login_at = now();
        $user->blocked_until = null;
        $user->save();

        // log user in
        $request->session()->regenerate(); // refresh session token
        Auth::login($user);

        // redirects to the page user was trying to access, in case there's no such page it redirects home
        return redirect()->intended(route('home'));
    }
}
