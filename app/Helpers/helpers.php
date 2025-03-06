<?php

use App\Mail\OTP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

if(!function_exists('sentOTP')){
    function sentOTP(array $data, $otp_expire_time){
        $otp = generateOtp();
        $otp_expire_at = Carbon::now()->addMinutes($otp_expire_time)->format('Y-m-d H:i:s');
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => 'OTP Verification',
            'otp' => $otp,
            'otp_expire_at' => $otp_expire_at,
            'otp_expire_time' => $otp_expire_time
        ];
        Mail::to($data['email'])->send(new OTP($data));
        return $data;
    }
}

//generate otp
if (!function_exists('generateOtp')) {
    function generateOtp($length = 6)
    {
        $otp = str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        return $otp;
    }
}
