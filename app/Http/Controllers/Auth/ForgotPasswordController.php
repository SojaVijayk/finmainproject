<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;




class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;
    public function index()
    {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.authentications.auth-forgot-password-cover', ['pageConfigs' => $pageConfigs]);
    }
    public function submitForgetPasswordForm(Request $request): RedirectResponse
      {
          $request->validate([
              'email' => 'required|email|exists:users',
          ]);

          $token = Str::random(64);
          DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

          DB::table('password_reset_tokens')->insert([
              'email' => $request->email,
              'token' => $token,
              'created_at' => Carbon::now()
            ]);

          Mail::send('emails.forgetPassword', ['token' => $token], function($message) use($request){
              $message->to($request->email);
              $message->subject('Reset Password');
          });

          return back()->with('message', 'We have e-mailed your password reset link!');
      }
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showResetPasswordForm($token): View
      {
        //  return view('auth.forgetPasswordLink', ['token' => $token]);
         $pageConfigs = ['myLayout' => 'blank'];
         return view('content.authentications.auth-reset-password-cover', ['pageConfigs' => $pageConfigs,'token' => $token]);
      }

      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);

          $updatePassword = DB::table('password_reset_tokens')
                              ->where([
                                'email' => $request->email,
                                'token' => $request->token
                              ])
                              ->first();


          if(!$updatePassword){
              // return back()->withInput()->with('error', 'Invalid token!');
              return response()->json(['message' => "Invalid token",'status'=>'false'], 200);
          }

          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);

          DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();
          return response()->json(['message' => "Your password has been changed!",'status'=>'true'], 200);
          // return redirect('/login')->with('message', 'Your password has been changed!');
      }
}