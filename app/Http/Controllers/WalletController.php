<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function bakiyeYukle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'tutar'   => 'required',
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
            $bakiye = floatval($user->bakiye);
            $yeniBakiye = $bakiye + floatval($request->input('tutar'));

            $user->bakiye = $yeniBakiye;
            if ($user->save()) {
                return response()->json([
                    'error'   => false,
                    'message' => "Bakiye başarıyla eklendi."
                ], 201);
            } else {
                return response()->json([
                    'error'   => true,
                    'message' => "Bakiye eklenirken bir hata ile karşılaşıldı."
                ], 500);
            }
        }

        return response()->json([
            'error'   => true,
            'message' => "Bakiye eklenirken bir hata ile karşılaşıldı."
        ], 500);
    }
}
