<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/verification',[\App\Http\Controllers\ApiController::class,'apiVerification'])->name('apiVerification');
Route::post('/feeders',[\App\Http\Controllers\ApiController::class,'apiFeeders'])->name('apiFeeders');
Route::post('/feederDetail',[\App\Http\Controllers\ApiController::class,'apiFeederDetail'])->name('apiFeederDetail');
Route::post('/sendNotifi',[\App\Http\Controllers\ApiController::class,'apiSendNotifi'])->name('apiSendNotifi');

Route::post('/saveNotifiID',[\App\Http\Controllers\ApiController::class,'saveNotifiID'])->name('saveNotifiID');


Route::group(['prefix'=>'feeder'],function (){
    Route::post('/getFeeders',[\App\Http\Controllers\FeederController::class,'getAllOnlineFeeder'])->name('getAllOnlineFeeder');
    Route::post('/getFeederDetail',[\App\Http\Controllers\FeederController::class,'getFeederDetail'])->name('getFeederDetail');
    Route::post('/takviyeYap',[\App\Http\Controllers\FeederController::class,'takviyeYap'])->name('takviyeYap');
});


Route::group(['prefix'=>'create/user'],function (){
    //Login and Verification
    Route::post('/getOrCreateUser',[\App\Http\Controllers\UserController::class,'getOrCreateUser'])->name('getOrCreateUser');
    Route::post('/smsVerification',[\App\Http\Controllers\UserController::class,'userSmsVerification'])->name('userSmsVerification');
    //Notification
    Route::post('/sendAllUserNotification',[\App\Http\Controllers\NotificationController::class,'sendAllUserNotification'])->name('sendAllUserNotification');
    //Usage
    Route::post('/kullanimOlustur',[\App\Http\Controllers\GecmisKullanimlarController::class,'gecmisKullanimEkle'])->name('gecmisKullanimEkle');
    //Arkadasini Davet Et
    Route::post('/arkadasiniDavetEt',[\App\Http\Controllers\ArkadasiniDavetEtController::class,'arkadasiniDavetEt'])->name('arkadasiniDavetEt');
});

Route::group(['prefix'=>'update/user'],function (){
    Route::post('/readNotification',[\App\Http\Controllers\NotificationController::class,'UserNotificationRead'])->name('UserNotificationRead');
    Route::post('/updateUserData',[\App\Http\Controllers\UserController::class,'UpdateUserData'])->name('UpdateUserData');
});

Route::group(['prefix'=>'get/user'],function (){
    Route::post('/data',[\App\Http\Controllers\UserController::class,'getUserData'])->name('getUserData');
    Route::post('/notifications',[\App\Http\Controllers\NotificationController::class,'getUserNotifications'])->name('getUserNotifications');
    Route::post('/gecmisKullanimlar',[\App\Http\Controllers\GecmisKullanimlarController::class,'gecmisKullanimlariListele'])->name('gecmisKullanimlariListele');
});

//Wallet
Route::group(['prefix'=>'wallet'],function (){
    Route::post('/bakiyeYukle',[\App\Http\Controllers\WalletController::class,'bakiyeYukle'])->name('bakiyeYukle');
});
