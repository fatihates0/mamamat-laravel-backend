<?php

namespace App\Http\Controllers;

use App\Models\Feeder;
use App\Models\GecmisKullanimlar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GecmisKullanimlarController extends Controller
{
    public function gecmisKullanimEkle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required',
            'feeder_id' => 'required|string',
            'adet'      => 'required',
            'tutar'     => 'required',
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
            $tutar  = floatval($request->input('tutar'));

            if ($bakiye >= $tutar) {
                $ekle = GecmisKullanimlar::create([
                    'user_id'   => $user->id,
                    'feeder_id' => $request->input('feeder_id'),
                    'adet'      => $request->input('adet'),
                    'tutar'     => $tutar
                ]);

                if ($ekle) {
                    $user->bakiye = $bakiye - $tutar;
                    $update = $user->save();

                    if ($update) {
                        // Feeder doluluk yüzdesini güncelle (her adet +%5, max 100)
                        $feeder = Feeder::where('title', $request->input('feeder_id'))->first();
                        if ($feeder) {
                            $yeniDoluluk = min(100, intval($feeder->doluluk) + ($request->input('adet') * 5));
                            $feeder->doluluk = $yeniDoluluk;
                            $feeder->save();
                        }

                        return response()->json([
                            'error'   => false,
                            'message' => "Geçmiş kullanım başarıyla eklendi."
                        ], 201);
                    } else {
                        return response()->json([
                            'error'   => true,
                            'message' => "Takviye başarılı ancak bakiyenizle ilgili bir sorun yaşıyoruz."
                        ], 201);
                    }
                } else {
                    return response()->json([
                        'error'   => true,
                        'message' => "Geçmiş kullanım eklenirken bir hata ile karşılaşıldı."
                    ], 201);
                }
            }

            return response()->json([
                'error'   => true,
                'message' => "Yeterli bakiyeniz bulunmamaktadır. Lütfen bakiye yükleyin."
            ], 201);
        }

        return response()->json([
            'error'   => true,
            'message' => "Kullanıcı bulunamadı."
        ], 201);
    }

    public function gecmisKullanimlariListele(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $userId = $request->input('user_id');
        $userExists = User::where('id', $userId)->exists();

        if ($userExists) {
            $gecmisKullanimlar = GecmisKullanimlar::where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->get([
                    'id',
                    'feeder_id',
                    'adet',
                    'tutar',
                    'created_at'
                ])->map(function ($gecmisKullanim) {
                    $gecmisKullanim->tarih = Carbon::parse($gecmisKullanim->created_at)->format('d/m/Y H:i');
                    return $gecmisKullanim;
                });

            return response()->json([
                'data'    => $gecmisKullanimlar,
                'error'   => false,
                'message' => "Geçmiş kullanımlar listelendi."
            ], 201);
        }

        return response()->json([
            'error'   => true,
            'message' => "Kullanıcı bulunamadı."
        ], 400);
    }
}
