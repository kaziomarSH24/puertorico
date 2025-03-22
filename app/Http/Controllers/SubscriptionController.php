<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{

    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
        ]);
        // dd(Auth::id());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }
        Stripe::setApiKey(config('services.stripe.secret'));

        $plan = PricingPlan::find($request->plan_id);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Subscription Plan - ' . $plan->plan_name,
                    ],
                    'unit_amount' => $plan->price * 100, // $10 -> 1000
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.verify') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'user_id' => Auth::id(),
                'plan_id' => $request->plan_id,
                'plan_name' => $plan->plan_name,
                'amount' => $plan->price,
                'audio_limit' => $plan->audio_limit,

            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully',
            'payment_url' => $session->url,
        ]);
    }
    // public function createPayment(Request $request)
    // {   //use db transaction
    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();
    //         $validator = Validator::make($request->all(), [
    //             'plan_id' => 'required',
    //             'stripe_token' => 'nullable',
    //             // 'payment_method' => 'required',
    //             // 'currency' => 'required'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $validator->errors()
    //             ], 400);
    //         }

    //         $plan = PricingPlan::find($request->plan_id);
    //         if (!$plan) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Plan not found'
    //             ], 404);
    //         }

    //         $stripe = new \Stripe\StripeClient(
    //             env('STRIPE_SECRET')
    //         );

    //         $charge = $stripe->charges->create([
    //             'amount' => $plan->price * 100,
    //             'currency' => 'usd',
    //             'source' => $request->stripe_token,
    //             'description' => 'Payment for ' . $plan->name
    //         ]);

    //         if ($charge->status != 'succeeded') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Payment failed'
    //             ], 400);
    //         } else {
    //             //expire date calculation
    //             if ($plan->plan_name === 'daily') {
    //                 $expires_at = now()->addDay();
    //             } elseif ($plan->plan_name === 'weekly') {
    //                 $expires_at = now()->addWeek();
    //             } elseif ($plan->plan_name === 'monthly') {
    //                 $expires_at = now()->addMonth();
    //             } elseif ($plan->plan_name === 'yearly') { //1 year
    //                 $expires_at = now()->addYear();
    //             }

    //             $subscription = new Subscription();
    //             $subscription->user_id = $user->id;
    //             $subscription->plan_name = $plan->plan_name;
    //             $subscription->price = $plan->price;
    //             $subscription->audio_limit = $plan->audio_limit;
    //             $subscription->start_date = now();
    //             $subscription->expires_at = $expires_at;
    //             $subscription->save();

    //             DB::commit();
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Payment successful',
    //                 'charge' => $charge
    //             ]);
    //         }
    //     } catch (\Stripe\Exception\CardException $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' =>  $e->getError()->message
    //         ], 400);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function paymentSuccess(Request $request)
    {
        // return $request->all();
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::retrieve($request->session_id);
        $metadata = $session->metadata;

        // return $metadata;

        if($session->payment_status === 'paid') {
            //expire date calculation
            if ($metadata['plan_name'] === 'daily') {
                $expires_at = now()->addDay();
            } elseif ($metadata['plan_name'] === 'weekly') {
                $expires_at = now()->addWeek();
            } elseif ($metadata['plan_name'] === 'monthly') {
                $expires_at = now()->addMonth();
            } elseif ($metadata['plan_name'] === 'yearly') { //1 year
                $expires_at = now()->addYear();
            }

            $subscription = Subscription::where('payment_id', $session->payment_intent)->first();
            if ($subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already processed'
                ], 400);
            }
            $subscription = Subscription::where('user_id', $metadata['user_id'])
                        ->where('status', 'active')
                        ->orWhere('status','!=', 'expired')
                        ->first();
            if ($subscription) {
                $subscription->status = 'inactive';
                $subscription->save();
            }
                $subscription = new Subscription();
                $subscription->user_id = $metadata['user_id'];
                $subscription->plan_name = $metadata['plan_name'];
                $subscription->price = $metadata['amount'];
                $subscription->audio_limit = $metadata['audio_limit'];
                $subscription->start_date = now();
                $subscription->expires_at = $expires_at;
                $subscription->payment_id = $session->payment_intent;
                $subscription->status = 'active';
                $subscription->save();


        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successful! Enjoy your subscription',
            'session' => $session
        ]);
    }

    //cancel payment api response
    public function paymentCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment cancelled'
        ]);
    }
}
