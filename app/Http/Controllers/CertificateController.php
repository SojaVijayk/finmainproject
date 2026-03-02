<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificateOtpGeneral; // ✅ make sure you created this Mailable class
use PDF; // barryvdh/laravel-dompdf

class CertificateController extends Controller
{
    // 🔹 Show the initial form
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('certificate.form', compact('pageConfigs'));
    }

    // 🔹 Normal verification (email/mobile check only)
    public function verify(Request $request)
    {
        $request->validate([
            'email'  => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
        ]);

        $participant = EventParticipant::when($request->email, function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->when($request->mobile, function ($query) use ($request) {
                $query->orWhere('mobile', $request->mobile);
            })
            ->first();

        if ($participant) {
            return view('certificate.success', compact('participant'));
        }

        return back()->with('error', 'No record found. Please check your email or mobile number.');
    }

    // 🔹 Send OTP to participant’s email
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email'  => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
        ]);

        $user = EventParticipant::when($request->email, function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->when($request->mobile, function ($query) use ($request) {
                $query->orWhere('mobile', $request->mobile);
            })
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid Email or Mobile');
        }

        // ✅ Generate & save OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['otp' => $otp]);

        // ✅ Send Email (make sure mail config works)
        try {
            Mail::to($user->email)->send(new CertificateOtpGeneral([
                'name' => $user->name,
                'otp'  => $otp,
            ]));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send OTP. Please check mail configuration.');
        }

        $pageConfigs = ['myLayout' => 'blank'];
        return view('certificate.verify_otp', compact('pageConfigs', 'user'));
    }

    // 🔹 Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:50',
            'otp'   => 'required|digits:6',
        ]);

        $user = EventParticipant::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid OTP, please try again.');
        }

        // ✅ Update verification details
        $user->update([
            'otp_verified'          => 1,
            'certificate_generated_at' => now(),
        ]);

        // ✅ Generate certificate PDF
        // $pdf = PDF::loadView('certificate.pdf', compact('user'));
        // return $pdf->download('Certificate_' . $user->name . '.pdf');

         // ✅ Return success partial view with correct user data
    $html = view('certificate.success', compact('user'))->render();
    return response()->json(['html' => $html]);
    }

    // 🔹 View certificate online
    public function view($id)
    {
        $user = EventParticipant::findOrFail($id);
        $pdf = PDF::loadView('certificate.pdf', compact('user'));
        return $pdf->stream("Certificate.pdf");
    }

    // 🔹 Download certificate manually
    public function download($id)
    {
        $user = EventParticipant::findOrFail($id);
        $pdf = PDF::loadView('certificate.pdf', compact('user'));
        return $pdf->download("Certificate_{$user->name}.pdf");
    }
}