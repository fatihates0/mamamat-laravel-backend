<?php

namespace App\Http\Controllers;

use App\Models\Feeder;
use App\Models\NotifiId;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function apiVerification(Request $request)
    {
        $fields = $request->only('phone_number', 'verification_code');
        $user = User::where([
            ['phone_number', $fields['phone_number']],
            ['verification_code', $fields['verification_code']]
        ])->first();

        if ($user) {
            return response([
                'success' => true,
                'message' => 'Success Verification Code',
                'id' => $user->id
            ], 201);
        }

        return response([
            'success' => false,
            'message' => 'Wrong Verification Code'
        ], 201);
    }

    public function apiFeeders(Request $request)
    {
        $data = Feeder::all();

        $list = $data->map(function ($veri) {
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

    public function apiFeederDetail(Request $request)
    {
        $fields = $request->only('feeder');
        $feeder = Feeder::where('title', $fields['feeder'])->first();

        $data = [
            'status' => true,
            'feeder' => $feeder
        ];

        return response()->json($data, 201);
    }

    public function apiSendNotifi(Request $request, PushNotificationService $pushService)
    {
        $fields = $request->validate([
            'pushToken' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable'
        ]);

        $result = $pushService->sendPushNotification(
            $fields['pushToken'],
            $fields['title'],
            $fields['body'],
            $fields['data'] ?? null
        );

        return response()->json($result, 201);
    }

    public function saveNotifiID(Request $request)
    {
        $fields = $request->validate([
            'notifi_id' => 'required|string'
        ]);

        NotifiId::firstOrCreate(['notifi_id' => $fields['notifi_id']]);

        return response()->json([
            'status' => true
        ], 201);
    }
}
