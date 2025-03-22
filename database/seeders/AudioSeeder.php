<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all image files from the public/img directory
        $imageFiles = File::files(public_path('img'));

        // Ensure the storage directory exists
        Storage::makeDirectory('public/audio/image');

        // Copy images to storage/app/public/audio/image
        $imgPaths = [];
        foreach ($imageFiles as $imageFile) {
            $destinationPath = 'public/audio/image/' . $imageFile->getFilename();
            Storage::put($destinationPath, File::get($imageFile));
            $imgPaths[] = $destinationPath;
        }

        // Get all audio files from the public/audio directory
        $audioFiles = File::files(public_path('audio'));

        // Ensure the storage directory exists
        Storage::makeDirectory('public/audio');

        // Copy audio files to storage/app/public/audio
        $audioPaths = [];
        foreach ($audioFiles as $audioFile) {
            $destinationPath = 'public/audio/' . $audioFile->getFilename();
            Storage::put($destinationPath, File::get($audioFile));
            $audioPaths[] = $destinationPath;
        }

        $audios = [
            [
                'category_id' => 1,
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 1,
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 2,
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 2,
                'language' => 'spanish',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
            [
                'category_id' => 3,
                'language' => 'english',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
            [
                'category_id' => 3,
                'language' => 'english',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
        ];

        foreach ($audios as $audio) {
            // Ensure there are image files to select from
            if (empty($imgPaths)) {
                throw new \Exception('No image files found in public/img directory');
            }

            // Ensure there are audio files to select from
            if (empty($audioPaths)) {
                throw new \Exception('No audio files found in public/audio directory');
            }

            // Select a random image from the copied images
            $randomImage = $imgPaths[array_rand($imgPaths)];

            // Select a random audio file from the copied audio files
            $randomAudio = $audioPaths[array_rand($audioPaths)];

            DB::table('audios')->insert([
                'category_id' => $audio['category_id'],
                'title' => $faker->sentence(3),
                'url' => Storage::url($randomAudio),
                'artist' => $faker->optional()->name,
                'artwork' => Storage::url($randomImage),
                'language' => $audio['language'],
                'description' => $faker->paragraph(),
                'views' => $faker->numberBetween(0, 500),
                'lat' => $audio['lat'],
                'lng' => $audio['lng'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
