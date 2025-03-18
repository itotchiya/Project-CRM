<?php

namespace App\Helper;

class Common
{

    /** Return success response
     * @param $date
     * @return string
     */

    public static function dateColor($date, $past=true): string
    {
        if (is_null($date)) {
            return '--';
        }

        $formattedDate = $date->translatedFormat(company()->date_format);
        $todayText = __('app.today');

        if ($date->setTimezone(company()->timezone)->isToday()) {
            return '<span class="text-success">' . $todayText . '</span>';
        }

        if ($date->endOfDay()->isPast() && $past ) {
            return '<span class="text-danger">' . $formattedDate . '</span>';
        }

        return '<span>' . $formattedDate . '</span>';
    }

    public static function active(): string
    {
        return '<i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
    }

    public static function inactive(): string
    {
        return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
    }

    public static function encryptDecrypt($string, $action = 'encrypt')
    {

        // DO NOT CHANGE IT. CHANGING IT WILL AFFECT THE APPLICATION
        $secret_key = 'worksuite'; // User define private key
        $secret_iv = 'froiden'; // User define secret key

        $encryptMethod = 'AES-256-CBC';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encryptMethod, $key, 0, $iv);

            return base64_encode($output);
        }

        if ($action == 'decrypt') {
            return openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv);
        }

        throw new \Exception('No action provided for Common::encryptDecrypt');

    }

}
