<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum',"verified")->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(["middleware" => "auth:sanctum"],function(){
    Route::post("/change-password",[AuthController::class,'changePassword'])->name("change-password");
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'sendVerificationEmail']);
    Route::get('/email/verify/{id}/{hash}',[VerifyEmailController::class,'verify'])->name("verification.verify");
    Route::post("/logout",[AuthController::class,'logout'])->name('logout');
});

// Routes of authentification without middleware
Route::post("/register",[AuthController::class,'register'])->name("register");
Route::post("/login",[AuthController::class,'login'])->name("login");
Route::post('/forgot-password',[AuthController::class,'forgotPassword'])->name("forgotPassword");
Route::post('/reset-password',[AuthController::class,'reset'])->name("forgotPassword");


