<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Category;
use Illuminate\Http\Request;
use getID3\getID3;

class Homecontroller extends Controller
{
    public function homeSection(){
        $topCategory = Audio::select('category_id')
                 ->groupBy('category_id')
                 ->orderByRaw('SUM(views) DESC')
                 ->limit(4)
                 ->pluck('category_id');

        $topCategoryDetails = Category::whereIn('id', $topCategory)
                            ->select('id', 'category_name', 'category_image')
                             ->get();

        // $topAudios = Audio::whereIn('category_id', $topCategory)
        //              ->orderBy('views', 'DESC')
        //              ->limit(10)
        //              ->get();
        $englishAudios = Audio::where('language', 'english')
                        ->orderBy('views', 'DESC')
                        ->limit(10)
                        ->get();
        $spanishAudios = Audio::where('language', 'spanish')
                        ->orderBy('views', 'DESC')
                        ->limit(10)
                        ->get();
        return response()->json([
            'success' => true,
            'featured' => $topCategoryDetails,
            'english' => $englishAudios,
            'spanish' => $spanishAudios
        ]);
    }

    //category audios
    public function categoryAudios(Request $request, $id){

        $audios = Category::with(['audios' => function($query){
            $query->select('id', 'title', 'audio_file', 'category_id', 'views', 'language');
        }])
                ->where('id', $id)
                ->get();
        if ($audios->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Audios not found'
            ], 404);
        }

        //get audios duration
        // $getID3 = new \getID3;
        foreach ($audios as $audio) {
            foreach ($audio->audios as $audio) {
                $audioFile = getStorageFilePath($audio->audio_file);
                $duration = getAudioDuration($audioFile);
                $audio->duration = $duration;
            }
        }
        $totalDuration = $this->getCategoryTotalDuration($id);
        $minutes = floor($totalDuration / 60);
        $seconds = $totalDuration % 60;
        $totalDuration = $minutes . 'm ' . $seconds . 's';
        $audios[0]->total_duration = $totalDuration;

        $totalStories = Audio::where('category_id', $id)->count();
        $totalStories = strtoupper($totalStories . ' ' . ($totalStories > 1 ? 'stories' : 'story'). ' - '.$minutes.' minutes '.$seconds.' secounds');
        $audios[0]->total_stories = $totalStories;
        // return $duration;
        return response()->json([
            'success' => true,
            'audios' => $audios
        ]);
    }

    //Category audios total duration
    private function getCategoryTotalDuration($categoryId){
        $audios = Audio::where('category_id', $categoryId)->get();

    $totalDuration = 0;
    $getID3 = new \getID3();

    foreach ($audios as $audio) {
        $filePath = storage_path('app/public/' . getStorageFilePath($audio->audio_file));

        if (file_exists($filePath)) {
            $fileInfo = $getID3->analyze($filePath);
            $duration = $fileInfo['playtime_seconds'] ?? 0;
            $totalDuration += $duration;
        }
    }

    return $totalDuration;

    // return gmdate("H:i:s", $totalDuration);
    }
}
