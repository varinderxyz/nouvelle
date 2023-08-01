<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesCategory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            DB::table('services_category')->insert([
            'name' => 'Services Category '. rand(1000,99999),
            'picture' => 'images/default-image.jpg'
        ]);
        }
    }
}
