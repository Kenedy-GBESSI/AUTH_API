<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function sendVerificationEmail(Request $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json("User already verify email",200);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['status' => 'verification-link-sent']);
    }

    public function verify(EmailVerificationRequest $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json("User already verify email",200);
        }
        if ($request->user()->markEmailAsVerified()) {
          event(new Verified($request->user()));
        }

        return response()->json("Email has been verified",200);
    }
}
