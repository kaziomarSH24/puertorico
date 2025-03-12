<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function getPageContent($type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found!',
            ], 404);
        }

        return response()->json(['page' => $page]);
    }


    public function updateOrCreatePage(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'type' => 'required|string',
                'content' => 'required|string'
            ]);

            if($validated->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validated->errors()
                ], 400);
            }

            $page = Page::updateOrCreate(
                ['type' => $request->type],
                ['content' => $request->content]
            );

            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully!',
                'page'    => $page
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
