<?php

use Illuminate\Database\Seeder;

class Locations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            DB::table('locations')->insert([
                'name' => 'Location ' . rand(1000, 99999),
                'type' => 'users'
            ]);
        }
        for ($i = 0; $i < 20; $i++) {
            DB::table('locations')->insert([
                'name' => 'Location ' . rand(1000, 99999),
                'type' => 'services'
            ]);
        }
    }
}
