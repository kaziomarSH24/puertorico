<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $audios = [
            [
                'category_id' => 1,
                'url' => 'audio/JFwPfN797d33rKSl3u8BP1SQfiu66EHtXEFJoC8t.mp3',
                'artwork' => 'audio/image/Uw7pSdxd0KAdYShUzn7qKQvp6bIy94LjX0Yeo9E.jpg',
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 1,
                'url' => 'audio/xUAxS9ImyjdYf3INuTQUxIPhl27GqAgrqxw7bqFa.mp3',
                'artwork' => 'audio/image/RcCQU5U5uvGmsrsN8E6QyPVX0LhuASLqEiJ0e1ZK.jpg',
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 2,
                'url' => 'audio/OZLUgZ1eXPAvV6GGUTp4vZwhFATZx0pbU21GTvl7.mp3',
                'artwork' => 'audio/image/pQ19CAX7rYlHoWY1zJGZOUNEL76WNUkojGSvA40.jpg',
                'language' => 'spanish',
                'lat' => 23.77449350,
                'lng' => 90.41615670,
            ],
            [
                'category_id' => 2,
                'url' => 'audio/zgrq30oNSoXeqGze5BVzjTMk58UtUwElgZAcFgj.mp3',
                'artwork' => 'audio/image/QbgYuywrfs2vGWXnoqwZVeBkYDz2hTcNWJ2PBk3.png',
                'language' => 'spanish',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
            [
                'category_id' => 3,
                'url' => 'audio/zJjIbOo9pyjIlwcgmBWaQ4FZMdVgApcSux0Oexfa.mp3',
                'artwork' => 'audio/image/p4CURcmS1Zap5M1UOcy85r5XzCNWErclJcIv3.png',
                'language' => 'english',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
            [
                'category_id' => 3,
                'url' => 'audio/Rbg1T3vQgKmdBMgLEP8ZECD6SqLZM9aOMvNZjfCD.mp3',
                'artwork' => 'audio/image/voUiNXe7LTPBAgZvFyj4oVUNqLaapWTrOP8Tqx6.png',
                'language' => 'english',
                'lat' => 23.75883110,
                'lng' => 90.42934430,
            ],
        ];

        foreach ($audios as $audio) {
            DB::table('audios')->insert([
                'category_id' => $audio['category_id'],
                'title' => $faker->sentence(3),
                'url' => $audio['url'],
                'artist' => $faker->optional()->name,
                'artwork' => $audio['artwork'],
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
