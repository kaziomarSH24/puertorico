<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $favorites = Favorite::with(['audio' => function ($query) {
            $query->select(['id', 'title', 'url', 'artwork', 'category_id', 'views', 'language', 'artist', 'is_favorite', 'is_bookmarked']);
        }])
            ->where('user_id', $user->id)
            ->paginate($request->input('per_page', 10));
        $favorites->getCollection()->transform(function($favorite){
            return [
                'id' => $favorite->id,
                'audio_id' => $favorite->audio->id,
                'category_id' => $favorite->audio->category_id,
                'title' => $favorite->audio->title,
                'url' => $favorite->audio->url,
                'artist' => $favorite->audio->artist,
                'artwork' => $favorite->audio->artwork,
                'views' => $favorite->audio->views,
                'language' => $favorite->audio->language,
            ];
        });
        if ($favorites->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Favorites not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $favorites,
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

        $favorite = Favorite::where('user_id', $user->id)
            ->where('audio_id', $request->audio_id)
            ->first();

        if ($favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Audio already in favorites'
            ], 400);
        }else{
            $favorite = new Favorite();
            $favorite->user_id = $user->id;
            $favorite->audio_id = $request->audio_id;
            $favorite->save();
            return response()->json([
                'success' => true,
                'message' => 'Audio added to favorites'
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
        $favorite = Favorite::find($id);
        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorite removed successfully'
        ]);
    }
}
