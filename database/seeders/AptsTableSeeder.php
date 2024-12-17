<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AptsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('apts')->insert([
            [
                'title' => 'Greenview Apartments',
                'description' => 'Spacious 2-bedroom apartment in downtown area.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Sunnybrook Apartments',
                'description' => 'Modern apartment with rooftop pool and amenities.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Riverdale Apartments',
                'description' => 'Cozy riverside apartment with a beautiful view.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Parkside Apartments',
                'description' => '1-bedroom apartment next to the central park.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Hilltop Apartments',
                'description' => 'Luxury apartment with panoramic city views.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
