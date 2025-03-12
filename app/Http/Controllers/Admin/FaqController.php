<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $faqs = Faq::all();
        if($faqs->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No FAQs found!'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'faqs' => $faqs
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
            $validated = Validator::make($request->all(), [
                'question' => 'required|string',
                'answer' => 'required|string'
            ]);

            if($validated->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validated->errors()
                ], 400);
            }

            $faq = Faq::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'FAQ created successfully!',
                'faq'    => $faq
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $th->getMessage()
            ], 500);
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
       try{
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found!'
            ], 404);
        }
        $faq->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully!',
            'faq'    => $faq
        ]);
       }catch(\Exception $th){
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong!',
            'error'   => $th->getMessage()
        ], 500);
       }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ not found!'
                ], 404);
            }

            $faq->delete();

            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully!'
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
