<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    
    public function register(RegisterRequest $request){
      $requestData = $request->validated();
      $requestData['password'] = Hash::make($requestData['password']);
      $user = User::create($requestData);

      event(new Registered($user));

      $token = $user->createToken('app-token')->plainTextToken;
      $data = [
        'token' => $token,
        'user' => $user
     ];

     return response()->json($data,201);
    }


    // Login method
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

         if(Auth::attempt($credentials)){
             $token = Auth::user()->createToken("app-token")->plainTextToken;
             return response()->json(['user' => Auth::user(), 'token'=> $token],201);
         }
         return response()->json("Email or Password not exists",404);
    }

    // forgotPassword method
    public function forgotPassword(Request $request){
        $validated = $request->validate([
            'email' => 'email|required'
        ]);
        // Get user .....
        $user = User::where("email",$validated['email'])->first();
        if(!$user){
          return response()->json("User not exist");
        }
        /**
         * Reset password part
         */
        $status = Password::sendResetLink($request->only("email"));
        if($status === Password::RESET_LINK_SENT){
            return response()->json(['status' => __($status)],200);
        }else{
            return response()->json(['status' => __($status)],500);
        }

    }

     //Reset method
     public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if($status === Password::PASSWORD_RESET){
            return response()->json(['message' =>'User logout!']);
            return response()->json(['status' => __($status)],200);
        }
        return response()->json(['status' => __($status)],500);
}


   // changePassword method
    public function changePassword(Request $request){

       $validated = $request->validate([
         'old_password' => 'required',
         'new_password' => 'required'
       ]);

       $user = Auth::user();
        if(Hash::check($validated['old_password'],$user->password)){
            $user->update(['password' => Hash::make($validated['new_password'])]);
            return response()->json($user,200);
        }else{
            return response()->json("Incorrect password");
        }
    }


    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' =>'User logout!']);
    }

}
