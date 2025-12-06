<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $login = $request->get('login');
        
        // Check if the input is an email or serial number
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            // It's an email
            return [
                'email' => $login,
                'password' => $request->get('password')
            ];
        } else {
            // It's a serial number
            return [
                'serial_number' => $login,
                'password' => $request->get('password')
            ];
        }
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Redirect users to their specific dashboards based on role
        if ($user->isHOD()) {
            return redirect()->route('hod.dashboard');
        } elseif ($user->isPresident()) {
            return redirect()->route('president.dashboard');
        } elseif ($user->isRegistrar()) {
            return redirect()->route('registrar.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isBank()) {
            return redirect()->route('bank.dashboard');
        }

        // Redirect regular users to portal dashboard
        return redirect()->route('portal.dashboard');
    }
}
