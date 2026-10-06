<?php

use App\Models\notifiId;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    echo "Welcome to Mamamat Backend App";

});

Route::get('/gonder', function () {
    $notifiId = notifiId::all(['notifi_id']);
    foreach ($notifiId as $notifi) {
        $message = [
            'to' => $notifi->notifi_id,
            'title' => "MAMAMAT",
            'body' => "Bugün beslemeyi unutma!",
        ];

        $postData = json_encode($message);

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => $postData,
            ],
        ];

        $context = stream_context_create($options);

        $result = file_get_contents('https://exp.host/--/api/v2/push/send', false, $context);
    }
});
