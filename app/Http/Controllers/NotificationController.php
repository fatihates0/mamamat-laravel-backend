<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotifiId;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // Bütün kullanıcılara Bildirim Gönder
    public function sendAllUserNotification(Request $request, PushNotificationService $pushService)
    {
        $notifiIds = NotifiId::all(['notifi_id']);

        $validator = Validator::make($request->all(), [
            'notifi_title' => 'required|string',
            'notifi_text'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $title = $request->input('notifi_title');
        $text  = $request->input('notifi_text');

        foreach ($notifiIds as $notifi) {
            Notification::create([
                'notifi_id'    => $notifi->notifi_id,
                'notifi_title' => $title,
                'notifi_text'  => $text,
            ]);

            $pushService->sendPushNotification($notifi->notifi_id, $title, $text);
        }

        return response()->json([
            'status' => true
        ], 201);
    }

    // Kullanıcı Notification ID'sine göre bu zamana kadar gönderilmiş bütün Bildirimleri çek
    public function getUserNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifi_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $notifiId = $request->input('notifi_id');
        $notificationCount = NotifiId::where('notifi_id', $notifiId)->count();

        if ($notificationCount > 0) {
            $notifications = Notification::where([
                ['notifi_id', $notifiId],
                ['status', 0]
            ])->orderBy('created_at', 'DESC')->get([
                'id',
                'notifi_title',
                'notifi_text',
                'status',
                'created_at'
            ])->map(function ($notification) {
                $notification->tarih = Carbon::parse($notification->created_at)->format('d/m/Y H:i');
                return $notification;
            });

            return response()->json([
                'status'  => true,
                'count'   => count($notifications),
                'message' => count($notifications) > 0 ? "" : "Hiç Bildiriminiz Yok",
                'data'    => $notifications
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Notifications not found'
            ], 404);
        }
    }

    public function UserNotificationRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifi_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 400);
        }

        $notifiId = $request->input('notifi_id');
        $notificationCount = Notification::where('notifi_id', $notifiId)->count();

        if ($notificationCount > 0) {
            Notification::where('notifi_id', $notifiId)->update(['status' => 1]);
        }

        return response()->json([
            'status' => true,
        ], 200);
    }
}
