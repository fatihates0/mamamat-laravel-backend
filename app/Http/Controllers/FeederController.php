<?php

namespace App\Http\Controllers;

use App\Models\Feeder;
use App\Models\GecmisKullanimlar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeederController extends Controller
{
    public function getAllOnlineFeeder(Request $request)
    {
        $feeder = Feeder::all();

        $list = $feeder->map(function ($veri) {
            return [
                'latlng' => [
                    'latitude' => floatval($veri->latitude),
                    'longitude' => floatval($veri->longitude)
                ],
                'title' => $veri->title,
                'doluluk' => $veri->doluluk
            ];
        })->toArray();

        return response()->json($list, 201);
    }

    public function getFeederDetail(Request $request)
    {
        $fields = $request->only('feeder_title');
        $feeder = Feeder::where('title', $fields['feeder_title'] ?? null)->first();

        $data = [
            'status' => true,
            'feeder' => $feeder
        ];

        return response()->json($data, 201);
    }

    public function takviyeYap(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|integer',
            'feeder_title' => 'required|string',
            'adet'         => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'   => true,
                'message' => 'Geçersiz veri.',
                'errors'  => $validator->errors()
            ], 400);
        }

        $feeder = Feeder::where('title', $request->input('feeder_title'))->first();
        if (!$feeder) {
            return response()->json(['error' => true, 'message' => 'Feeder bulunamadı.'], 404);
        }

        $user = User::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Kullanıcı bulunamadı.'], 404);
        }

        // Her adet 5 TL, toplam tutar
        $birimFiyat = 5;
        $tutar = $request->input('adet') * $birimFiyat;

        $bakiye = floatval($user->bakiye);
        if ($bakiye < $tutar) {
            return response()->json([
                'error'   => true,
                'message' => 'Yeterli bakiyeniz bulunmamaktadır. Lütfen bakiye yükleyin.'
            ], 200);
        }

        // Feeder doluluk yüzdesini artır (max 100)
        $mevcutDoluluk = intval($feeder->doluluk);
        $artis = $request->input('adet') * 5; // her adet %5 artış
        $yeniDoluluk = min(100, $mevcutDoluluk + $artis);
        $feeder->doluluk = $yeniDoluluk;
        $feeder->save();

        // Geçmiş kullanım kaydı oluştur
        GecmisKullanimlar::create([
            'user_id'   => $user->id,
            'feeder_id' => $feeder->title,
            'adet'      => $request->input('adet'),
            'tutar'     => $tutar,
        ]);

        // Kullanıcı bakiyesini düş
        $user->bakiye = $bakiye - $tutar;
        $user->save();

        return response()->json([
            'error'        => false,
            'message'      => 'Takviye başarıyla yapıldı.',
            'yeni_doluluk' => $yeniDoluluk,
            'yeni_bakiye'  => $user->bakiye,
        ], 201);
    }
}
