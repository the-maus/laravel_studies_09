<?php

namespace App\Http\Controllers;

use App\Mail\NewUserConfirmation;
use App\Mail\ResetPassword;
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
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        // validate form
        $credentials = $request->validate(
            [
                'username' => 'required|min:3|max:30',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            [
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
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '<=', now());
            })
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->first();

        if (!$user)
            return back()->withInput()->with(['invalid_login' => 'Invalid login']);

        // check if password is valid
        if (!password_verify($credentials['password'], $user->password))
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

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeUser(Request $request): RedirectResponse|View
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
        if (!$result) {
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

        if (!$user)
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

    public function profile(): View
    {
        return view('auth.profile');
    }

    public function changePassword(Request $request)
    {
        // form validation
        $request->validate(
            [
                'current_password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'new_password'     => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|different:current_password',
                'new_password_confirmation' => 'required|same:new_password'
            ],
            [
                'current_password.regex' => 'Current password must contain at least one lower-case letter, one upper-case letter and a number',
                'new_password.regex' => 'New password must contain at least one lower-case letter, one upper-case letter and a number',
            ]
        );

        // check if current password is correct
        if (!password_verify($request->current_password, Auth::user()->password))
            return back()->with(['server_error' => 'Current password is incorrect']);

        // update password
        $user = Auth::user();
        $user->password = bcrypt($request->new_password);
        $user->save();

        // update password on session
        Auth::user()->password = $request->new_password;


        // show success message
        return redirect()->route('profile')->with(['success' => 'Password updated successfuly']);
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot_password');
    }

    public function sendResetPasswordLink(Request $request)
    {
        // form validation
        $request->validate(['email' => 'required|email']);

        $genericMessage = 'Check your mailbox to proceed with password recovery';

        // check if e-mail exists
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return back()->with(['server_message' => $genericMessage]);

        // create link with token to send via e-mail
        $user->token = Str::random(64);

        $tokenLink = route('reset_password', ['token' => $user->token]);

        // send e-mail for password recovery and check if it was sent
        $result = Mail::to($user->email)->send(new ResetPassword($user->username, $tokenLink));
        if (!$result)
            return back()->with(['server_message' => $genericMessage]);

        // store token
        $user->save();

        return back()->with(['server_message' => $genericMessage]);
    }

    public function resetPassword($token): View | RedirectResponse
    {
        // check if token is valid
        $user = User::where('token', $token)->first();
        if (!$user)
            return redirect()->route('login');

        return view('auth.reset_password', compact('token'));
    }

    public function resetPasswordUpdate(Request $request): RedirectResponse
    {
        // form validation
        $request->validate(
            [
                'token'                     => 'required',
                'new_password'              => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'new_password_confirmation' => 'required|same:new_password'
            ],
            [
                'new_password.regex' => 'New password must contain at least one lower-case letter, one upper-case letter and a number',
            ]
        );

        // check if token is valid
        $user = User::where('token', $request->token)->first();
        if (!$user)
            return redirect()->route('login');

        // update password
        $user->password = bcrypt($request->new_password);
        $user->token = null;
        $user->save();

        return redirect()->route('login')->with(['success' => true]);
    }
}
