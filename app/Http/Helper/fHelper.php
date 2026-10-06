<?php

namespace App\Http\Helper;

use App\Services\SmsService;

class fHelper
{
    /**
     * Send login verification SMS code.
     *
     * @param string $ceptel
     * @param string|int $smskode
     * @return void
     */
    public static function girisDogrulamaKoduGonder($ceptel, $smskode)
    {
        $smsService = app(SmsService::class);
        $smsService->sendVerificationCode($ceptel, $smskode);
    }

    /**
     * Send invitation SMS.
     *
     * @param string $ceptel
     * @return void
     */
    public static function davetiyeGonder($ceptel)
    {
        $smsService = app(SmsService::class);
        $smsService->sendInvitation($ceptel);
    }

    /**
     * Format/clean phone number.
     *
     * @param string $phoneNumber
     * @return string
     */
    public static function phoneNumberCheck($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) > 10) {
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);
            $phoneNumber = $areaCode . $nextThree . $lastFour;
        } elseif (strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);
            $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } elseif (strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);
            $phoneNumber = $nextThree . '-' . $lastFour;
        }

        return $phoneNumber;
    }
}
