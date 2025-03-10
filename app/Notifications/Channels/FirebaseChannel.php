<?php

namespace App\Notifications\Channels;

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
            $messaging = (new Factory)
            ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
            ->createMessaging();

        // if (!method_exists($notification, 'toFirebase')) {
        //     throw new \Exception('Notification is missing toFirebase method.');
        // }
        $fcmData = $notification->toFirebase($notifiable);

        $message = CloudMessage::withTarget('token', $fcmData['token'])
            ->withNotification([
                'title' => $fcmData['title'],
                'body'  => $fcmData['body'],
            ])
            ->withData($fcmData['data'] ?? []);

        return $messaging->send($message);
        } catch (MessagingException | FirebaseException $e) {
            // Log the error or handle it as needed
            \Log::error('Firebase notification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Firebase notification failed.'], 500);
        }
    }
}
