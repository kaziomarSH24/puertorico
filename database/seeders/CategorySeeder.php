<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 10; $i++) {
            // Fake Image Generate
            $fakeImage = UploadedFile::fake()->image('category_' . $i . '.jpg');

            // Store the file in storage/app/public/category
            $path = $fakeImage->store('category', 'public');

            Category::create([
                'title' => $faker->word(),
                'artwork' => $path,
                'description' => $faker->text(),
                'is_featured' => $faker->boolean(30),
            ]);
        }
    }
}


