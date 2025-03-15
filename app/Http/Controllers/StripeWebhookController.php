<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Event;
use App\Models\Subscription;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Webhook Secret Key
        $endpoint_secret = config('services.stripe.webhook_secret');

        // Stripe Signature Verification
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $payload = $request->getContent();

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook signature verification failed'], 400);
        }

        Log::info('Stripe Webhook Received: ', (array) $event);

        // Webhook Event Type
        $eventType = $event->type;

        if ($eventType === 'checkout.session.completed') {
            $this->handlePaymentSuccess($event->data->object);
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentSuccess($session)
    {
        $userId = $session->metadata->user_id;
        $planName = $session->metadata->plan_name;
        $price = $session->amount_total / 100; // Convert cents to dollars

        // 🔥 Subscription Duration Set করুন
        $durations = [
            "daily" => 1,
            "weekly" => 7,
            "monthly" => 30,
            "yearly" => 365
        ];

        $days = $durations[strtolower($planName)] ?? 30;
        $expiresAt = Carbon::now()->addDays($days);

        // 🔥 Audio Limit Set করুন (Plan অনুযায়ী)
        $audioLimits = [
            "daily" => 100,
            "weekly" => 300,
            "monthly" => 1000,
            "yearly" => -1 // Unlimited
        ];

        $audioLimit = $audioLimits[strtolower($planName)] ?? 100;

        // ✅ আগের Subscription Expired করুন
        Subscription::where('user_id', $userId)->update(['status' => 'expired']);

        // ✅ নতুন Subscription Active করুন
        Subscription::create([
            'user_id' => $userId,
            'plan_name' => ucfirst($planName),
            'start_date' => Carbon::now(),
            'price' => $price,
            'audio_limit' => $audioLimit,
            'status' => 'active',
            'expires_at' => $expiresAt,
        ]);

        Log::info("✅ Payment Successful for User ID: $userId | Plan: $planName | Expiry: $expiresAt");
    }
}

