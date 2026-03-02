<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    protected $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;
    protected $username;

    /**
     * show login form for admin guard
     *
     * @return void
     */
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-login-cover', ['pageConfigs' => $pageConfigs]);
    }


    /**
     * login admin
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        // Validate Login Data
        $request->validate([
            'email' => 'required|max:50',
            'password' => 'required',
        ]);

        // Attempt to login
        // if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {

          if (Auth::attempt($request->only($this->username(), 'password'), $request->remember)) {
            // Redirect to dashboard
            session()->flash('success', 'Successully Logged in !');

            if( auth()->user()->user_role==1){
              return redirect()->route('dashboard-admin')->with('success', 'Successfully Logged in!');
            }
            else if(auth()->user()->user_role==2){
              return redirect()->route('dashboard-user')->with('success', 'Successfully Logged in!');;
            }


        }
        else {
        //     // Search using username
        //     if (Auth::attempt(['username' => $request->email, 'password' => $request->password], $request->remember)) {
        //         session()->flash('success', 'Successully Logged in !');
        //         if( auth()->user()->user_role==1){
        //         return redirect()->route('dashboard-admin')->with('success', 'Successfully Logged in!');
        //         }
        //         else if(auth()->user()->user_role==2){
        //           return redirect()->route('dashboard-user')->with('success', 'Successfully Logged in!');;
        //         }
        //     }
        //     // error
            session()->flash('error', 'Invalid email and password');
            return back()->with('error', 'Invalid User Information');;
        }
    }



    /**
     * logout admin guard
     *
     * @return void
     */
    public function logout()
    {
        // Auth::guard('admin')->logout();
        auth()->logout();
        return redirect()->route('login');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->username = $this->findUsername();
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername()
    {
        $login = request()->input('email');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}