<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Mail\CertificateOtp;
use Illuminate\Http\Request;
use DB;
use PDF;
use Illuminate\Support\Facades\Mail;


class ResearchController extends Controller
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
    return view('research.register', ['pageConfigs' => $pageConfigs]);
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

    public function register(Request $request){
      $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email',
        'mobile' => 'required|',
        'institution' => 'required|',
        'discipline' => 'required|',
        'programme' => 'required|',
        'type' => 'required|',
        'education' => 'required|',
        'pro_qualification' => 'required|',
        'reservation' => 'required|',
        'physical_status' => 'required|',
        'qualification' => 'required|',



    ]);
    $date= date('Y-m-d H:i:s');

    $values = array(
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'mobile' => $request->input('mobile'),
        'institution' => $request->input('institution'),
        'discipline' => $request->input('discipline'),
        'programme' => $request->input('programme'),
        'type' => $request->input('type'),
        'qualification' => $request->input('qualification'),
        'addl_qualification' => $request->input('addl_qualification'),
        'reservation' =>  $request->input('reservation'),
        'education' =>  json_encode($request->input('education')),
        'physical_status' =>  $request->input('physical_status'),
        'pro_qualification' =>  $request->input('pro_qualification'),
    );
    if($request->input('reservation') == 'Yes'){
      $values['res_category'] = $request->input('res_category');
    }
    else{
      $values['res_category'] = 'Nil';
    }

    if($request->input('addl_qualification')){
      $values['addl_qualification'] = $request->input('addl_qualification');
    }

   $result= DB::table('research_candidates')->insert($values);

    if($result && $request->input('institution') == 'Amrita'){
      return response()->json(['message' => "Data submitted successfully for Ph.D. at Amrita follow the link for complete your Application. URL :https://espro.christuniversity.in/Application/",
    'url' => 'https://aoap.amrita.edu/phd-24/index/','institute'=> $request->input('institution')], 200);

    }
    else if($result && $request->input('institution') == 'CHRIST' && $request->input('discipline') == 'Management'){

      return response()->json(['message' => "Data submitted successfully for Ph.D. at CHRIST follow the link for complete your Application. URL :https://espro.christuniversity.in/Application/",
      'url' => 'https://christuniversity.in/academics/Main-Campus/school-of-business-and-management','institute'=> $request->input('institution')], 200);

    }

    else if($result && $request->input('institution') == 'CHRIST' && $request->input('discipline') == 'Social-Sciences'){

      return response()->json(['message' => "Data submitted successfully for Ph.D. at CHRIST follow the link for complete your Application. URL :https://espro.christuniversity.in/Application/",
      'url' => 'https://christuniversity.in/departments/main%20campus/school%20of%20social%20sciences/sociology%20and%20social%20work','institute'=> $request->input('institution')], 200);

    }

    }


}