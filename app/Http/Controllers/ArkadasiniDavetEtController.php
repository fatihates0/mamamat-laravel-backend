<?php

namespace App\Http\Controllers;

use App\Http\Helper\fHelper;
use App\Models\ArkadasiniDavetEt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArkadasiniDavetEtController extends Controller
{
    public function arkadasiniDavetEt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'phone_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 400);
        }

        $userId = $request->input('user_id');
        $phoneNumber = $request->input('phone_number');

        $userExists = User::where('id', $userId)->exists();

        if ($userExists) {
            $davetExists = ArkadasiniDavetEt::where('davet_edilen_phone', $phoneNumber)->exists();
            $userPhoneExists = User::where('phone_number', $phoneNumber)->exists();

            if (!$davetExists && !$userPhoneExists) {
                $davetiyeEkle = ArkadasiniDavetEt::create([
                    'user_id' => $userId,
                    'davet_edilen_phone' => $phoneNumber
                ]);

                if ($davetiyeEkle) {
                    $tel = str_replace([' ', '+90'], '', $phoneNumber);
                    fHelper::davetiyeGonder($tel);

                    return response()->json([
                        'error' => false,
                        'message' => "Davetiye başarıyla gönderildi."
                    ], 201);
                }

                return response()->json([
                    'error' => true,
                    'message' => "Davetiye gönderilirken bir hata ile karşılaşıldı."
                ], 201);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "Daha önce kayıt olmuş veya davet edilmiş."
                ], 201);
            }
        }

        return response()->json([
            'error' => true,
            'message' => "Beklenmedik bir hata ile karşılaşıldı! Lütfen daha sonra tekrar deneyin."
        ], 201);
    }
}
