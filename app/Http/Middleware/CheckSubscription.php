<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Models\UserAudioListen;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        // return $user;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }
        $audioPath = parse_url($request->url(), PHP_URL_PATH);

        if(str_starts_with($audioPath, '/storage/audio/')) {
            $audioCount = UserAudioListen::where('user_id', $user->id)->count();


        if ($audioCount < 3) {
            return $next($request);
        }

        $subscription = Subscription::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->where('expires_at', '>=', now())
                        ->latest()
                        ->first();


        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found. Please subscribe to continue.',
                'subscription_required' => true
            ], 403);
        }


        if ($subscription->expires_at < now()) {
            $subscription->update(['status' => 'expired']);

            return response()->json([
                'message' => 'Your subscription has expired. Please renew.',
                'subscription_required' => true
            ], 403);
        }


        $totalLimit = 3 + $subscription->audio_limit;

        if ($subscription->audio_limit !== -1 && $audioCount >= $totalLimit) {
            return response()->json([
                'message' => 'You have reached your audio play limit.',
                'subscription_required' => true
            ], 403);
        }

        return $next($request);
        }
    }
}
