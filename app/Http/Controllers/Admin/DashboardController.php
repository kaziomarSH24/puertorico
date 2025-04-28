<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserAudioListen;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $usersCount = User::where('role','user')->count();
            $totalListenAudio = Audio::sum('views');
            $totalEarning = Subscription::sum('price');
            $dashboardData = User::selectRaw('
                    MONTH(created_at) as month,
                    COUNT(id) as total_users')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();


            $user_type = strtolower(str_replace(' ', '_', $request->input('user_type', 'total_users'))); // lawyer, client

            $months = collect(range(1, 12))->map(function ($month) use ($dashboardData, $user_type) {
                $data = $dashboardData->where('month', $month)->first();
                return [
                    'month' => date('F', mktime(0, 0, 0, $month, 10)),
                    'data' => $user_type == 'total_users' ? ($data ? $data->total_users : 0) : ($user_type == 'lawyers' ? ($data ? $data->total_lawyers : 0) : ($data ? $data->total_clients : 0)),
                ];
            });

            return response()->json([
                'success' => true,
                'usersCount'=> $usersCount,
                'totalListenAudio' => $totalListenAudio,
                'totalEarning' => $totalEarning,
                'data' => $months
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
