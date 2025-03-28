<?php

namespace App\Notifications\Channels;

use GPBMetadata\Google\Api\Log;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFirebase')) {
            return response()->json(['message' => 'Notification is missing toFirebase method.'], 400);
        }

        try{
            $serviceAccountPath = storage_path('puertorico-push-notificaton-firebase-adminsdk-fbsvc-f0bd2f48d4.json');
            // \Log::info('serviceAccountPath: ' . $serviceAccountPath);
            $messaging = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->createMessaging();

        // if (!method_exists($notification, 'toFirebase')) {
        //     throw new \Exception('Notification is missing toFirebase method.');
        // }
        $fcmData = $notification->toFirebase($notifiable);
        \Log::info("User Device Token: " . $fcmData['token']);
        // if(!$fcmData['token']){
        //     return response()->json(['message' => 'User device token not found.'], 400);
        // }
        $message = CloudMessage::withTarget('token', $fcmData['token'])
            ->withNotification([
                'title' => $fcmData['title'],
                'body'  => $fcmData['body'],
            ])
            ->withData($fcmData['data'] ?? []);
            \Log::info("User Device Token: " . $fcmData['token']);

                // dd($message);
        return $messaging->send($message);
        } catch (MessagingException | FirebaseException $e) {
            // Log the error or handle it as needed
            \Log::error('Firebase notification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Firebase notification failed.'], 500);
        }
    }
}
