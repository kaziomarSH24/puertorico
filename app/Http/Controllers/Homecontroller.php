<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Category;
use App\Models\Story;
use Illuminate\Http\Request;
use getID3\getID3;
use Illuminate\Support\Facades\Auth;

class Homecontroller extends Controller
{
    public function homeSection()
    {
        $featuredCategory = Category::where('is_featured', 1)
    ->with([
        'audios:category_id,url',
        'stories' => function ($query) {
            $query->where('user_id', Auth::id());
        }
    ])
    ->withCount('audios') // Total stories count
    ->paginate(10);

    $featuredCategory->transform(function ($category) {
        $hasStory = $category->stories->isNotEmpty();
        $totalDuration = $this->getCategoryTotalDuration($category->id);

        $minutes = floor($totalDuration / 60);
        $seconds = $totalDuration % 60;
        $totalDurationText = "{$minutes}m {$seconds}s";

        $totalStories = strtoupper($category->audios_count . ' ' . ($category->audios_count > 1 ? 'stories' : 'story') . ' - ' . $minutes . ' minutes ' . $seconds . ' seconds');

        return [
            'id' => $category->id,
            'title' => $category->title,
            'artwork' => $category->artwork,
            'description' => $category->description,
            'has_story' => $hasStory,
            'duration' => $totalDurationText,
            'stories' => $totalStories,
        ];
});


        // $topAudios = Audio::whereIn('category_id', $topCategory)
        //              ->orderBy('views', 'DESC')
        //              ->limit(10)
        //              ->get();
        $englishAudios = Category::whereHas('audios', function ($query) {
            $query->where('language', 'english')
                ->orderBy('views', 'DESC');
        })
            ->withCount('audios')
            ->paginate(10);
        $englishAudios->transform(function ($category) {
            $hasStory = $category->stories->isNotEmpty();
            $totalDuration = $this->getCategoryTotalDuration($category->id, 'english');

        $minutes = floor($totalDuration / 60);
        $seconds = $totalDuration % 60;
        $totalDurationText = "{$minutes}m {$seconds}s";

        $totalStories = strtoupper($category->audios_count . ' ' . ($category->audios_count > 1 ? 'stories' : 'story') . ' - ' . $minutes . ' minutes ' . $seconds . ' seconds');

        return [
            'id' => $category->id,
            'title' => $category->title,
            'artwork' => $category->artwork,
            'description' => $category->description,
            'has_story' => $hasStory,
            'total_duration' => $totalDurationText,
            'total_stories' => $totalStories,
        ];

        });




        $spanishAudios = Category::whereHas('audios', function ($query) {
            $query->where('language', 'spanish')
                ->orderBy('views', 'DESC');
        })
            ->withCount('audios')
            ->paginate(10);
        $spanishAudios->transform(function ($category) {
            $hasStory = $category->stories->isNotEmpty();
            $totalDuration = $this->getCategoryTotalDuration($category->id, 'spanish');
            $minutes = floor($totalDuration / 60);
        $seconds = $totalDuration % 60;
        $totalDurationText = "{$minutes}m {$seconds}s";

        $totalStories = strtoupper($category->audios_count . ' ' . ($category->audios_count > 1 ? 'stories' : 'story') . ' - ' . $minutes . ' minutes ' . $seconds . ' seconds');

        return [
            'id' => $category->id,
            'title' => $category->title,
            'artwork' => $category->artwork,
            'description' => $category->description,
            'has_story' => $hasStory,
            'total_duration' => $totalDurationText,
            'total_stories' => $totalStories,
        ];
        });

        return response()->json([
            'success' => true,
            'featured' => $featuredCategory,
            'english' => $englishAudios,
            'spanish' => $spanishAudios
        ]);
    }

    //category audios
    public function categoryAudios(Request $request, $id)
    {

        // $audios = Category::with(['audios' => function($query) use ($request) {
        //             $query->select('id', 'title', 'url', 'category_id', 'views', 'language');
        //             if($request->has('language')){
        //                 $query->where('language', $request->language);
        //             }
        //             $query->paginate(10);
        //         }])
        //         // ->whereHas('stories'  )
        //         ->where('id', $id)
        //         ->get();

        $category = Category::find($id);
        $audios = Audio::where('category_id', $id)
            ->when($request->has('language'), function ($query) use ($request) {
                $query->where('language', $request->language);
            })
            ->select('id', 'title', 'url', 'category_id', 'views', 'language', 'artist', 'artwork')
            ->paginate($request->per_page ?? 10);

        if ($audios->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Audios not found'
            ], 404);
        }
        $hasStory = Auth::check() && Story::where('category_id', $category->id)
            ->where('user_id', Auth::id())
            ->exists();


             //get audios duration
        // $getID3 = new \getID3;
            foreach ($audios as $audio) {
                $audioFile = getStorageFilePath($audio->url);
                $duration = getAudioDuration($audioFile);
                $audio->duration = $duration;
            }
        $totalDuration = $this->getCategoryTotalDuration($id);
        $minutes = floor($totalDuration / 60);
        $seconds = $totalDuration % 60;
        $totalDuration = $minutes . 'm ' . $seconds . 's';
        // $audios[0]->total_duration = $totalDuration;

        $totalStories = Audio::where('category_id', $id)->count();
        $totalStories = strtoupper($totalStories . ' ' . ($totalStories > 1 ? 'stories' : 'story') . ' - ' . $minutes . ' minutes ' . $seconds . ' secounds');
        // $audios[0]->total_stories = $totalStories;

        $audios = [
            'category' => [
                'id' => $category->id,
                'title' => $category->title,
                'artwork' => $category->artwork,
                'description' => $category->description,
                'has_story' => $hasStory,
                'total_duration' => $totalDuration,
                'total_stories' => $totalStories
            ],
            'audios' => $audios
        ];



        // return $duration;
        return response()->json([
            'success' => true,
            'data' => $audios
        ]);
    }

    //Category audios total duration
    private function getCategoryTotalDuration($categoryId, $language = null)
    {
        $audios = Audio::where('category_id', $categoryId)->get();
        if ($language !== null) {
            $audios = $audios->where('language', $language);
        }
        $totalDuration = 0;
        $getID3 = new \getID3();

        foreach ($audios as $audio) {
            $filePath = storage_path('app/public/' . getStorageFilePath($audio->url));

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
