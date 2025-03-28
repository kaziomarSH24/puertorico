<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // return $response;
        $query = Audio::query()->with(['category' => function ($query) {
            $query->select('id', 'title', 'artwork');
        }]);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if($request->has('language')){
            $query->where('language', $request->input('language'));
        }

        // if ($request->has('lat') && $request->has('lng')) {
        //     $lat = $request->input('lat');
        //     $lng = $request->input('lng');
        //     $radius = $request->input('radius', 10); // Default radius is 10 km

        //     $query->whereRaw(
        //         "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) < ?",
        //         [$lat, $lng, $lat, $radius]
        //     );
        // }

        $audios = $query->paginate($request->input('per_page', 10));


        if ($audios->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Audios not found'
            ], 404);
        }

        // $audios->transform(function ($audio) {
        //     $location = $this->findLocation($audio->lat, $audio->lng);
        //     $audio->location = $location;
        //     return $audio;
        // });

        return response()->json([
            'success' => true,
            'audios' => $audios
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
            'title' => 'required|string|max:255',
            'url' => 'required|file|mimes:mp3,wav,ogg,aac,flac,m4a,amr,opus',
            'artist' => 'nullable|string|max:255',
            'artwork' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'language' => 'required|in:english,spanish',
            'description' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        //has audio file
        $audioFile = $request->file('url');
        if($audioFile){
            $audioFilePath = $audioFile->store('audio', 'public');
        }
        //has image file
        $audioImage = $request->file('artwork');
        if($audioImage){
            $audioImgPath = $audioImage->store('audio/image', 'public');
        }

        $audio = new Audio();
        $audio->category_id = $request->category_id;
        $audio->title = $request->title;
        $audio->url = $audioFilePath;
        $audio->artist = $request->artist;
        $audio->artwork = $audioImgPath;
        $audio->language = $request->language;
        $audio->description = $request->description;
        $audio->lat = $request->lat;
        $audio->lng = $request->lng;
        $audio->save();

        return response()->json([
            'success' => true,
            'message' => 'Audio created successfully',
            'audio' => $audio
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $audio = Audio::with(['category' => function ($query) {
            $query->select('id', 'title', 'artwork');
        }])->find($id);
        if(!$audio){
            return response()->json([
                'success' => false,
                'message' => 'Audio not found'
            ], 404);
        }

        //update audio views
        if(Auth::user()->role !== 'admin'){
            $audio->views = $audio->views + 1;
            $audio->save();
        }

        // $location = $this->findLocation($audio->lat, $audio->lng);
        // $audio->location = $location;
        // $audio->save();
        $audioFilePath = getStorageFilePath($audio->url);
        $duration = getAudioDuration($audioFilePath);
        // return $duration;
        $audio->duration = $duration;

        return response()->json([
            'success' => true,
            'audio' => $audio
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
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'url' => 'sometimes|file',
            'artist' => 'nullable|string|max:255',
            'artwork' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'language' => 'required|in:english,spanish',
            'description' => 'nullable|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric'
        ]);

        Log::info('Audio update request: '.json_encode($request->all()));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $audio = Audio::find($id);
        if(!$audio){
            return response()->json([
                'success' => false,
                'message' => 'Audio not found'
            ], 404);
        }

        //has audio file
        $audioFile = $request->file('url');
        if($audioFile){
            $audioPath = str_replace('/storage/', '', parse_url($audio->url)['path']);
            // return $audioPath;
            //delete old audio file
            if(Storage::disk('public')->exists($audioPath)){
                Storage::disk('public')->delete($audioPath);
            }

            $audioFilePath = $audioFile->store('audio', 'public');
            $audio->url = $audioFilePath;
        }
        //has image file
        $audioImage = $request->file('artwork');
        if($audioImage){
            $ImgPath = str_replace('/storage/', '', parse_url($audio->artwork)['path']);
            if(Storage::disk('public')->exists($ImgPath)){
                Storage::disk('public')->delete($ImgPath);
            }
            $audioImgPath = $audioImage->store('audio/image', 'public');
            $audio->artwork = $audioImgPath;
        }

        $audio->category_id = $request->category_id;
        $audio->title = $request->title;
        $audio->language = $request->language;
        $audio->description = $request->description;
        $audio->lat = $request->lat;
        $audio->lng = $request->lng;
        $audio->save();

        return response()->json([
            'success' => true,
            'message' => 'Audio updated successfully',
            'audio' => $audio
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $audio = Audio::find($id);
        if(!$audio){
            return response()->json([
                'success' => false,
                'message' => 'Audio not found'
            ], 404);
        }
        // return $audio;
        $audioFilePath = str_replace('/storage/', '', parse_url($audio->url)['path']);
        $audioImgPath = str_replace('/storage/', '', parse_url($audio->artwork)['path']);

        //delete audio file
        if(Storage::disk('public')->exists($audioFilePath)){
            Storage::disk('public')->delete($audioFilePath);
        }
        //delete audio image
        if(Storage::disk('public')->exists($audioImgPath)){
            Storage::disk('public')->delete($audioImgPath);
        }

        $audio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Audio deleted successfully'
        ]);
    }

    // private function findLocation($lat, $lng){
    //     $response = Http::withHeaders([
    //         'User-Agent' => 'PuertoRico (contact@puertorico.com)'
    //     ])->get("https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lng}&format=json&accept-language=en")->json();



    //     return [
    //         'address' => $response['display_name'],
    //         'road' => $response['address']['road'] ?? null,
    //         'city' => $response['address']['city'] ?? null,
    //         'state' => $response['address']['state'],
    //         'suburb'=> $response['address']['suburb'],
    //         'country' => $response['address']['country'],
    //         'country_code' => $response['address']['country_code'],
    //         'postal_code' => $response['address']['postcode']
    //     ];
    // }
}
