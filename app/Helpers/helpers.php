<?php

use App\Mail\OTP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use getID3\getID3;
use Illuminate\Support\Facades\Auth;

if(!function_exists('sentOTP')){
    function sentOTP(array $data, $otp_expire_time){
        $otp = generateOtp(4);
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

//generate slug
if (!function_exists('generateSlug')) {
    function generateSlug($string)
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string));
        $slug = rtrim($slug, '-');
        return $slug;
    }
}

//generate unique slug
if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($model, $string)
    {
        $slug = generateSlug($string);
        $originalSlug = $slug;
        $count = 1;
        while ($model::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        return $slug;
    }
}

if (!function_exists('getStorageFilePath')) {
    function getStorageFilePath($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        return str_replace('/storage/', '', $path);
    }
}


//get audio duration
if (!function_exists('getAudioDuration')) {
    function getAudioDuration($filePath)
    {
        $getID3 = new \getID3;
        $filePath = storage_path('app/public/' . $filePath);
        $file = $getID3->analyze($filePath);
        // return $file['playtime_string'];
        if (isset($file['playtime_string'])) {
            return $file['playtime_string'];
        } else {
            return "Duration not available";
        }
    }
}



