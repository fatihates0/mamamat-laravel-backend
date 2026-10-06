<?php

namespace App\Http\Controllers;

use App\Http\Helper\fHelper;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getOrCreateUser(Request $request, SmsService $smsService)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $phoneNumber = $request->input('phone_number');
        $existingUser = User::where('phone_number', $phoneNumber)->first();

        if ($existingUser) {
            $code = rand(111111, 999999);
            $existingUser->verification_code = $code;
            $existingUser->save();

            try {
                $tel = fHelper::phoneNumberCheck($phoneNumber);
                $smsService->sendVerificationCode($tel, $code);

                return response([
                    'success' => true,
                    'id'      => $existingUser->id
                ], 201);
            } catch (\Exception $e) {
                Log::error("SMS Gönderim Hatası: " . $e->getMessage());
                return response([
                    'success' => false,
                    'message' => 'SMS Gönderim Hatası'
                ], 201);
            }
        } else {
            $code = rand(111111, 999999);
            try {
                $tel = str_replace([' ', '+90'], '', $phoneNumber);
                $smsService->sendVerificationCode($tel, $code);

                // Kullanıcı yok oluşturulup giriş yaptı
                $user = User::create([
                    'phone_number'      => $phoneNumber,
                    'verification_code' => $code,
                    'name'              => 'Kullanıcı ' . rand(1000, 9999),
                    'email'             => 'user_' . rand(100000, 999999) . '_' . time() . '@mamamat.com',
                    'password'          => bcrypt(rand(100000, 999999))
                ]);

                return response([
                    'success' => true,
                    'message' => 'User created',
                    'id'      => $user->id
                ], 201);
            } catch (\Exception $e) {
                Log::error("Mail Gönderim Hatası: " . $e->getMessage());

                return response([
                    'success' => false,
                    'message' => 'Beklenmedik bir hata ile karşılaşıldı'
                ], 201);
            }
        }
    }

    // SMS Doğrulaması
    public function userSmsVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number'      => 'required|string',
            'verification_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $user = User::where([
            ['phone_number', $request->input('phone_number')],
            ['verification_code', $request->input('verification_code')]
        ])->first();

        if ($user) {
            return response([
                'status'  => true,
                'message' => 'Success Verification Code',
                'id'      => $user->id
            ], 201);
        }

        return response([
            'status'  => false,
            'message' => 'Wrong Verification Code'
        ], 201);
    }

    // Get User Data
    public function getUserData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $user = User::find($request->input('user_id'));

        if ($user) {
            return response([
                'status' => true,
                'data'   => $user
            ], 201);
        }

        return response([
            'status'  => false,
            'message' => "Kullanıcı bulunamadı."
        ], 200);
    }

    public function UpdateUserData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uniq_id'      => 'required|string',
            'name'         => 'required|string',
            'email'        => 'required|email',
            'phone_number' => 'required',
            'dogum_tarihi' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Lütfen bütün alanları doldurun.',
                'errors'  => $validator->errors()
            ], 400);
        }

        $uniqId = $request->input('uniq_id');
        $user = User::where('uniq_id', $uniqId)->first();

        if (!$user) {
            return response([
                'status'  => false,
                'message' => "Kullanıcı bulunamadı."
            ], 404);
        }

        $updated = User::where('uniq_id', $uniqId)->update([
            'name'         => $request->input('name'),
            'email'        => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'dogum_tarihi' => $request->input('dogum_tarihi'),
        ]);

        if ($updated) {
            return response([
                'status'  => true,
                'message' => "Kullanıcı güncellendi."
            ], 201);
        }

        return response([
            'status'  => false,
            'message' => "İşlem sırasında bir hata meydana geldi."
        ], 404);
    }
}
