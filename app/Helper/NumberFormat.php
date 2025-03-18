<?php

namespace App\Helper;

use App\Models\Order;

class NumberFormat
{

    public static function contract($number, $setting = null)
    {
        $setting = $setting ?? invoice_setting();
        $zero = '';

        if (strlen($number) < $setting->contract_digit) {
            $condition = $setting->contract_digit - strlen($number);

            for ($i = 0; $i < $condition; $i++) {
                $zero = '0' . $zero;
            }
        }

        return $setting->contract_prefix . $setting->contract_number_separator . $zero . $number;
    }

    public static function creditNote($number, $setting = null)
    {
        $setting = $setting ?? invoice_setting();
        $zero = '';

        if (strlen($number) < $setting->credit_note_digit) {
            $condition = $setting->credit_note_digit - strlen($number);

            for ($i = 0; $i < $condition; $i++) {
                $zero = '0' . $zero;
            }
        }

        return $setting->credit_note_prefix . $setting->credit_note_number_separator . $zero . $number;
    }

    public static function invoice($number, $setting = null)
    {
        $setting = $setting ?? invoice_setting();
        $zero = '';

        if (strlen($number) < $setting->invoice_digit) {
            $condition = $setting->invoice_digit - strlen($number);

            for ($i = 0; $i < $condition; $i++) {
                $zero = '0' . $zero;
            }
        }

        return $setting->invoice_prefix . $setting->invoice_number_separator . $zero . $number;
    }

    public static function estimate($number, $setting = null)
    {
        $setting = $setting ?? invoice_setting();
        $zero = '';

        if (strlen($number) < $setting->estimate_digit) {
            $condition = $setting->estimate_digit - strlen($number);

            for ($i = 0; $i < $condition; $i++) {
                $zero = '0' . $zero;
            }
        }

        return $setting->estimate_prefix . $setting->estimate_number_separator . $zero . $number;
    }

    public static function order($number, $setting = null)
    {
        if (is_null($number)) {
            $number = ((int)Order::latest()->first()?->original_order_number ?? 0) + 1;
        }

        $setting = $setting ?? invoice_setting();
        $zero = '';

        if (strlen($number) < $setting->order_digit) {
            $condition = $setting->order_digit - strlen($number);

            for ($i = 0; $i < $condition; $i++) {
                $zero = '0' . $zero;
            }
        }

        return $setting->order_prefix . $setting->order_number_separator . $zero . $number;
    }

}
