<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $stories = Story::with(['category' => function ($query) {
            $query->select(['id', 'title', 'artwork']);
        }])
            ->where('user_id', $user->id)
            ->paginate($request->input('per_page', 10));

        if ($stories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Stories not found'
            ], 404);
        }

        $stories->getCollection()->transform(function ($story) {
            return [
                'id'=> $story->id,
                'category_id' => $story->category->id,
                'title' => $story->category->title,
                'artwork' => $story->category->artwork,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $stories,
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
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }
        //check if category already exists or not
        $story = Story::where('user_id', $user->id)
            ->where('category_id', $request->category_id)
            ->first();

        if ($story) {
            return response()->json([
                'success' => false,
                'message' => 'Story already exists!',
            ], 400);
        }
        // dd($user->id);
        if (!$story) {
            $story = new Story();
        }
        $story->user_id = $user->id;
        $story->category_id = $request->category_id;
        $story->save();

        return response()->json([
            'success' => true,
            'message' => 'Story created successfully',
            'data'    => $story,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

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
        $story = Story::find($id);
        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => 'Story not found'
            ], 404);
        }

        $story->delete();

        return response()->json([
            'success' => true,
            'message' => 'Story deleted successfully'
        ]);
    }
}
