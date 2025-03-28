<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'success' => true,
            'categories' => $categories
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
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'artwork' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 400);
            }

            //has image file
            $categoryImage = $request->file('artwork');
            if($categoryImage){
                $catImgPath = $categoryImage->store('category', 'public');
            }
            // Log::info("Request: " . $request->all());
            // Log::info("artwork: " . $request->artwork);
            $category = new Category();
            $category->title = $request->title;
            // $category->slug = generateUniqueSlug($category, $request->title);
            $category->artwork = $catImgPath;
            $category->description = $request->description;

            if($category->save()){
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully',
                    'category' => $category
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Category could not be created'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("message: " .$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'artwork' => 'sometimes|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $categories = Category::find($id);

        if(!$categories){
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        //has image file
        $categoryImage = $request->file('artwork');
        if($categoryImage){
            //delete old image
            $oldImagePath = str_replace('/storage/', '', parse_url($categories->artwork, PHP_URL_PATH));
            if(Storage::disk('public')->exists($oldImagePath)){
                Storage::disk('public')->delete($oldImagePath);
            }
            $catImgPath = $categoryImage->store('category', 'public');
            $categories->artwork = $catImgPath;
        }

        $categories->title = $request->title;
        // $categories->slug = generateUniqueSlug($categories, $request->title);
        $categories->description = $request->description;

        $categories->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $categories
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        //delete category image
        $catImgPath = str_replace('/storage/', '', parse_url($category->artwork, PHP_URL_PATH));
        // return $catImgPath;
        if(Storage::disk('public')->exists($catImgPath)){
            Storage::disk('public')->delete($catImgPath);
        }
        //delete category
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
