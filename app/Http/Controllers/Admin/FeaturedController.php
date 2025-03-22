<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeaturedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $featured = Category::where('is_featured', 1)->get();
        if ($featured->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Featured not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'featured' => $featured
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
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $featuredCategory = Category::find($request->category_id);
        if (!$featuredCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        if ($featuredCategory->is_featured == 1) {
            $featuredCategory->is_featured = 0;
            $featuredCategory->save();
            return response()->json([
                'success' => true,
                'message' => 'Category removed from featured successfully'
            ]);
        }else{
            $featuredCategory->is_featured = 1;
            $featuredCategory->save();
            return response()->json([
                'success' => true,
                'message' => 'Category added to featured successfully'
            ]);
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
        //
    }
}
