<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricingPlanController extends Controller
{

    // Get pricing plan by name
    public function getAllPlan()
    {
        $plans = PricingPlan::all();
        if($plans->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No pricing plan found!'
            ], 404);
        }

        $plans->transform(function($plan) {

            $per_day = $plan->plan_name == 'daily' ? $plan->price : ($plan->plan_name == 'weekly' ? $plan->price / 7 : ($plan->plan_name == 'monthly' ? $plan->price / 30 : ($plan->plan_name == 'yearly' ? $plan->price / 365 : 0)));
            return [
                'id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'price' => $plan->price,
                'per_day' => round($per_day, 2),
                'audio_limit' => $plan->audio_limit == -1 ? 'Unlimited' : $plan->audio_limit,
            ];
        });

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);

    }



    public function updateOrCreatePlan(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'plan_name' => 'required|string|in:daily,weekly,monthly,yearly',
                'price' => 'required|numeric',
                'audio_limit' => 'required|integer', // -1 for unlimited
            ]);

            if($validated->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validated->errors()
                ], 400);
            }

            $plan = PricingPlan::updateOrCreate(
                ['plan_name' => $request->plan_name],
                [
                    'price' => $request->price,
                    'audio_limit' => $request->audio_limit
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pricing plan updated successfully!',
                'plan'    => $plan
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
