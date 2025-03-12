<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $Bookmark = Bookmark::with(['audio' => function ($query) {
            $query->select(['id', 'title', 'url','artist','artwork','category_id', 'views', 'language', 'is_favorite', 'is_bookmarked']);
        }])
            ->where('user_id', $user->id)
            ->paginate($request->input('per_page', 10));
        $Bookmark->getCollection()->transform(function($bookmark){
            return [
                'id' => $bookmark->id,
                'audio_id' => $bookmark->audio->id,
                'category_id' => $bookmark->audio->category_id,
                'title' => $bookmark->audio->title,
                'url' => $bookmark->audio->url,
                'artist' => $bookmark->audio->artist,
                'artwork' => $bookmark->audio->artwork,
                'views' => $bookmark->audio->views,
                'language' => $bookmark->audio->language,
            ];
        });

        if ($Bookmark->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmarks not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $Bookmark,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'audio_id' => 'required|exists:audios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('audio_id', $request->audio_id)
            ->first();

        if($bookmark){
            return response()->json([
                'success' => false,
                'message' => 'Audio already bookmarked'
            ], 400);
        }else{
            $favorite = new Bookmark();
            $favorite->user_id = $user->id;
            $favorite->audio_id = $request->audio_id;
            $favorite->save();
            return response()->json([
                'success' => true,
                'message' => 'Audio bookmarked successfully'
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bookmark = Bookmark::find($id);
        if(!$bookmark){
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found'
            ], 404);
        }
        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark deleted successfully'
        ]);
    }
}
