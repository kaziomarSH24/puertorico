<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\UserNotificationCheck;
use App\Notifications\NearbySongNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NearbyAudioController extends Controller
{

    public function checkNearbyAudios(Request $request)
    {
        // Log::info('Check Nearby Audios');
        // log::info('++++++++++++++',$request->all());
        $validator = Validator::make($request->all(), [
            'lat'  => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $latitude  = $request->lat;
        $longitude = $request->lng;

        $nearbyAudios = Audio::selectRaw('lat, lng, COUNT(id) as total_audios')
            ->whereRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )) < 1", [$latitude, $longitude, $latitude])
            ->groupBy('lat', 'lng')
            ->get();

            // return $nearbyAudios->count();

        //send  notification
        $data = $this->findAudios($latitude, $longitude);
        // return $data;
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }
        $newSongs = $data['new_songs'] ?? [];
        Log::info(count($newSongs) > 0 ? 'Notification sent' : 'No new songs to notify++');
        Log::info($nearbyAudios);
        return response()->json([
            'success' => true,
            'message' => count($newSongs) > 0 ? 'Notification sent' : 'No new songs to notify',
            'data'    => $nearbyAudios,
        ]);
    }


    public function getNearByAudios(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(), [
            'lat'  => 'required|numeric',
            'lng' => 'required|numeric',
            'language' => 'required|string|in:english,spanish', //when filtering by language
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $latitude  = $request->lat;
        $longitude = $request->lng;

        //Find Nearby Songs
        $data = $this->findAudios($latitude, $longitude, $request->language);
        // dd($data);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }
        $newSongs = $data['new_songs'];
        $nearbySongs = $data['nearby_songs'];

        return response()->json([
            'success' => true,
            'nearby_songs' => $nearbySongs,
        ]);
    }

    private function findAudios($latitude, $longitude, $language = null){
        $user = Auth::user();
         $query = Audio::select('id', 'title', 'lat', 'lng', 'category_id', 'language', 'url','artist', 'artwork', 'views')
            ->selectRaw("
            (6371 * acos(
            cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
            sin(radians(?)) * sin(radians(lat))
            )) AS distance
        ", [$latitude, $longitude, $latitude])
            ->having('distance', '<', 1)
            ->orderBy('distance');

        if ($language !== null) {
            $query->where('language', $language);
        }

        $nearbySongs = $query->paginate(10);
        // return $nearbySongs;

        if ($nearbySongs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No nearby songs foundsss'], 404);
        }

       if($language === null){
        $newSongs = [];
        foreach ($nearbySongs as $song) {
            $alreadyNotified = UserNotificationCheck::where('user_id', $user->id)
                ->where('audio_id', $song->id)
                ->where('notified_at', '>=', now()->subSeconds(2)) // Notify after 1 second
                ->exists();
            // return $alreadyNotified;
            if (!$alreadyNotified || $alreadyNotified) {
                $newSongs[] = $song;

                // Store Notification Log
                UserNotificationCheck::create([
                    'user_id'  => $user->id,
                    'audio_id' => $song->id,
                ]);
            }
        }

        if (count($newSongs) > 0) {
            // Send a single notification with the count of new songs
            $user->notify(new NearbySongNotification(count($newSongs) . ' new songs found', $user->device_token));
        }
       }
        return[
            'new_songs' => $newSongs ?? [],
            'nearby_songs' => $nearbySongs
        ];
    }
}




