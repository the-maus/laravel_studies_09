<?php

namespace App\Http\Controllers;

use App\Mail\NewUserConfirmation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController extends Controller
{
    public function login() : View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request) : RedirectResponse
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

    public function logout() : RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function register() : View
    {
        return view('auth.register');
    }

    public function storeUser(Request $request) : RedirectResponse|View
    {
        // form validation
        $request->validate(
            [
                //unique:table,column (by default considers column name same as field, so it could be "unique:users")
                //could specify via model name also: "unique:App\Model\User,username"
                'username'              => 'required|min:3|max:30|unique:users,username', 
                'email'                 => 'required|email|unique:users,email',
                'password'              => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'password_confirmation' => 'required|same:password'
            ],
            [
                'username.unique' => 'This username can\'t be used',
                'email.unique'    => 'This e-mail can\'t be used',
            ]
        );

        // create new user setting a token for email verification
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->token = Str::random(64);

        // generate link
        $confirmation_link = route('new_user_confirmation', ['token' => $user->token]);

        // send mail
        $result = Mail::to($user->email)->send(new NewUserConfirmation($user->username, $confirmation_link));

        // check if mail was successfully sent
        if(!$result) {
            return back()->withInput()->with(['server_error' => 'An error occurred when sending confirmation mail.']);
        }

        // store user
        $user->save();
        
        // show success view
        return view('auth.email_sent', ['email' => $user->email]);
    }

    public function newUserConfirmation($token)
    {
        // check if token is valid
        $user = User::where('token', $token)->first();

        if(!$user)
            return redirect()->route('login');

        // confirm user registration
        $user->email_verified_at = Carbon::now();
        $user->last_login = Carbon::now();
        $user->token = null;
        $user->active = true;
        $user->save();

        // automatic user authentication
        Auth::login($user);

        // show success message
        return view('auth.new_user_confirmation');
    }
}
