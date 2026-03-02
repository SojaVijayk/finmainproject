<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Mail\CertificateOtp;
use Illuminate\Http\Request;
use DB;
use PDF;
use Illuminate\Support\Facades\Mail;


class CertificateController extends Controller
{
  /**
     * Display a listing of the resource.
     */
    function __construct()
    {

        // $this->middleware('role:Admin');
    }
    //
    public function index()
    {
      $pageConfigs = ['myLayout' => 'blank'];
    return view('lms.certificate', ['pageConfigs' => $pageConfigs]);
    }
    public function sendOtp(Request $request){
      $request->validate([
        'email' => 'required|max:50'

    ]);


    $user = DB::table('lms_users')->where('email',$request->input('email'))->first();
    if($user){

      $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      DB::table('lms_users')->where('email', $user->email)->update(array('otp' => $otp));


      // return(new CertificateOtp([
      //   'name' => $user->name,
      //   'otp' => $user->otp,
      // ]));

      Mail::to($user->email)->send(new CertificateOtp([
        'name' => $user->name,
        'otp' => $otp,
   ]));

      $pageConfigs = ['myLayout' => 'blank'];
      return view('lms.verify_otp', ['pageConfigs' => $pageConfigs],compact('user'));
    }
    else{
      session()->flash('error', 'Invalid email');
            return back()->with('error', 'Invalid Email');;
    }

    }


    public function verifyOtp(Request $request){
      $request->validate([
        'email' => 'required|max:50',
        'otp' => 'required|min:6'

    ]);

    $user = DB::table('lms_users')->where('email',$request->input('email'))->where('otp',$request->input('otp'))->first();
    if($user){
      DB::table('lms_users')->where('email', $user->email)->update(array('otp_verified' => 1,'certificate_generated_at' => date('Y-m-d H:i:s')));

      // return view('exports.lms.certificate', compact('user'));
      $pdf = PDF::loadView('exports.lms.certificate', compact('user'));
      return $pdf->download('certificate.pdf');
    }
    else{
      return response()->json("Invalid OTP");
    }

    }


}