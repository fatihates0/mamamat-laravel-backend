<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS verification code.
     *
     * @param string $phone
     * @param string|int $code
     * @return bool
     */
    public function sendVerificationCode(string $phone, $code): bool
    {
        if (env('SMS_ENABLED', true) === false) {
            Log::info("Mamamat Doğrulama Kodu: {$code} (Tel: {$phone})");
            return true;
        }

        $message = 'Mamamat Doğrulama Kodu: ' . $code;
        return $this->sendSms($phone, $message);
    }

    /**
     * Send invitation SMS.
     *
     * @param string $phone
     * @return bool
     */
    public function sendInvitation(string $phone): bool
    {
        if (env('SMS_ENABLED', true) === false) {
            Log::info("Mamamat Davetiye SMS: Tel: {$phone}");
            return true;
        }

        $message = "Arkadaşın seni Mamamat'ı kullanmaya davet etti. Hemen aşağıdaki linkten üyeliğini aç, arkadaşında sende 1 takviye hakkı kazan.";
        return $this->sendSms($phone, $message);
    }

    /**
     * Internal VatanSMS HTTP sender.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    protected function sendSms(string $phone, string $message): bool
    {
        $config = config('services.vatan_sms');
        $postUrl = $config['url'] ?? 'http://panel.vatansms.com/panel/smsgonder1Npost.php';

        $xmlBody = 'data=<sms>
	<kno>' . ($config['customer_no'] ?? '') . '</kno>
	<kulad>' . ($config['username'] ?? '') . '</kulad>
	<sifre>' . ($config['password'] ?? '') . '</sifre>
	<gonderen>' . ($config['originator'] ?? '') . '</gonderen>
	<mesaj>' . $message . '</mesaj>
	<numaralar>' . $phone . '</numaralar>
	<tur>Normal</tur>
	</sms>';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $postUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlBody);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            curl_close($ch);

            return !empty($response);
        } catch (\Exception $e) {
            Log::error("SmsService Error: " . $e->getMessage());
            return false;
        }
    }
}
