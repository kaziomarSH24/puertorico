<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

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
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        //has image file
        $categoryImage = $request->file('category_image');
        if($categoryImage){
            $catImgPath = $categoryImage->store('category', 'public');
        }

        $category = new Category();
        $category->category_name = $request->category_name;
        $category->slug = generateUniqueSlug($category, $request->category_name);
        $category->category_image = $catImgPath;
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
            'category_name' => 'required|string|max:255',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
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
        $categoryImage = $request->file('category_image');
        if($categoryImage){
            //delete old image
            if(Storage::disk('public')->exists($categories->category_image)){
                Storage::disk('public')->delete($categories->category_image);
            }
            $catImgPath = $categoryImage->store('category', 'public');
        }

        $categories->category_name = $request->category_name;
        $categories->slug = generateUniqueSlug($categories, $request->category_name);
        $categories->category_image = $catImgPath;
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

        if(Storage::disk('public')->exists($category->category_image)){
            Storage::disk('public')->delete($category->category_image);
        }
        //delete category
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
