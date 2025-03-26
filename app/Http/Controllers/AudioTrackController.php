<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\UserAudioListen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AudioTrackController extends Controller
{
    //user audio listening history and view count
    public function userAudioHistory(Request $request)
    {       DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'audio_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 401);
            }

            $user = auth()->user();

            $audioListen = UserAudioListen::updateOrCreate(
                ['user_id' => $user->id, 'audio_id' => $request->audio_id],
                ['last_listen_at' => now()]
            );

            $audio = Audio::find($request->audio_id);
            if ($audio) {
                $audio->views = $audio->views + 1;
                $audio->save();
            }


            DB::commit();
            return response()->json([
                'success' => true,
                'is_subscription_required' => $this->isSubscribedRequired()->original['is_subscription_required'],
                'message' => 'Audio history added successfully'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    //get all audio tracks history
    public function getAudioHistory()
    {
        $user = auth()->user();
        $audioHistory = UserAudioListen::where('user_id', $user->id)
                        ->with('audio')
                        ->orderBy('last_listen_at', 'desc')
                        ->paginate(10);
        $audioHistory->transform(function ($value) {
            return $value->audio;
        });
        if($audioHistory->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No audio history found'
            ], 404);
        }
        return response()->json($audioHistory, 200);
    }

    //count user 3free audio listen history
    public function countAudio()
    {
        $user = auth()->user();
        $audioCount = UserAudioListen::where('user_id', $user->id)
                        ->count();
        if($audioCount >= 3){
            return response()->json([
                'success' => false,
                'is_subscription_required' => $this->isSubscribedRequired()->original['is_subscription_required'],
                'message' => 'Free Limit Exceeded'
            ], 404);
        }
        $count = 3 - $audioCount;
        return response()->json([
            'success' => true,
            'is_subscription_required' => $this->isSubscribedRequired()->original['is_subscription_required'],
            'message' => 'Enjoy ' . $count . ' ' . ($count == 1 ? 'Audio' : 'Audios') . ' Free'
        ], 200);
    }

    //check is_subscribed_required
    public function isSubscribedRequired()
    {
        $user = auth()->user();
        $audio = Audio::select('id')
                ->first();
        return response()->json([
            'success' => true,
            'is_subscription_required' => $audio->is_subscription_required
        ], 200);
    }
}
