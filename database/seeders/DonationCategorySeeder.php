<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('donation_categories')->insert([
            [
                'title' => 'Food'
            ],
            [
                'title' => 'Clothing'
            ],
            [
                'title' => 'Education'
            ],
            [
                'title' => 'Sport'
            ],
            [
                'title' => 'Entertainment'
            ],
            [
                'title' => 'Electronic'
            ],
            [
                'title' => 'Others'
            ]
        ]);

        DB::table('donation_categories')->insert([
            [
                'parent_id' => 6,
                'title' => 'Smartphone'
            ],
            [
                'parent_id' => 6,
                'title' => 'Laptop'
            ],
            [
                'parent_id' => 6,
                'title' => 'TV'
            ]
        ]);
    }
}
