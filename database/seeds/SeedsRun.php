<?php

use Illuminate\Database\Seeder;
use App\User;
use App\ServicesCategory;
use App\Services;
use Carbon\Carbon;
use App\SwapServices;
use App\HireServices;
use Faker\Generator as Faker;

class SeedsRun extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        // DB::table('services_category')->truncate();

        // SERVICES CATEGORY
        for ($i = 0; $i < 20; $i++) {
            DB::table('services_category')->insert([
                'name' => 'Services Category ' . rand(1000, 99999),
                'picture' => 'images/default-image.jpg'
            ]);
        }

        // DB::table('locations')->truncate();

      

        // CREATE USERS
        
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@seesaw.com',
            'password' => bcrypt('admin746#'),
            'phone' => '8054142276',
            'picture' => 'images/default-image.jpg',
            'zip_code' => '180010',
            'hourly_rate' => rand(100, 500),
            'payment_customer_id' => Str::random(10),
            'willing_to_travel' => rand(100, 999),
            'about' => 'I Am Bla Bla ' . rand(1000, 99999),
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10)
        ]);
        
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin2@seesaw.com',
            'password' => bcrypt('admin746#'),
            'phone' => '9877658521',
            'picture' => 'images/default-image.jpg',
            'zip_code' => '180010',
            'hourly_rate' => rand(100, 500),
            'payment_customer_id' => Str::random(10),
            'willing_to_travel' => rand(100, 999),
            'about' => 'I Am Bla Bla ' . rand(1000, 99999),
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Kanwar one',
            'email' => 'kanwar1@gmail.com',
            'password' => bcrypt('mind@123'),
            'phone' => '2345678904',
            'picture' => 'images/profile_pictures/20200203_186699/6397165026G6183.png',
            'zip_code' => '1234562',
            'hourly_rate' => rand(100, 500),
            'payment_customer_id' => '257639706',
            'willing_to_travel' => rand(100, 999),
            'about' => 'info test',
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10),
        ]);



        DB::table('users')->insert([
            'name' => 'Kanwar two',
            'email' => 'kanwar2@gmail.com',
            'password' => bcrypt('mind@123'),
            'phone' => '2345678905',
            'picture' => 'images/default-image.jpg',
            'zip_code' => '180010',
            'hourly_rate' => rand(100, 500),
            'payment_customer_id' => '858862206',
            'willing_to_travel' => rand(100, 999),
            'about' => 'info test',
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10),
        ]);


        // kanwar one service 1
        // kanwar one service 1 desc

        // kanwar one service 2
        // kanwar one service 2 desc

        // /////////////

        // kanwar two service 1
        // kanwar two service 1 desc

        // kanwar two service 2
        // kanwar two service 2 desc


        // DB::table('users')->truncate();

        // CREATE USERS 
        DB::table('users')->insert([
            'name' => 'administrator',
            'email' => 'administrator@seesaw.com',
            'password' => bcrypt('admin746#'),
            'phone' => Str::random(10),
            'picture' => 'images/default-image.jpg',
            'zip_code' => rand(1000, 99999),
            'payment_customer_id' => Str::random(10),
            'willing_to_travel' => rand(100, 999),
            'about' => 'I Am Bla Bla ' . rand(1000, 99999),
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'role' => 'admin',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10)
        ]);
        for ($i = 0; $i < 10; $i++) {
           
            DB::table('users')->insert([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => bcrypt('admin746#'),
            'phone' => Str::random(10),
            'picture' => 'images/default-image.jpg',
            'zip_code' => rand(1000, 99999),
            'hourly_rate' => rand(100, 500),
            'payment_customer_id' => Str::random(10),
            'willing_to_travel' => rand(100, 999),
            'about' => 'I Am Bla Bla ' . rand(1000, 99999),
            'longitude' => '76.73604',
            'latitude' => '30.65195',
            'role' => 'user',
            'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
            'remember_token' => Str::random(10)
        ]);

            // CERTIFICATIONS
            for ($l = 1; $l < 4; $l++) {
                DB::table('certifications')->insert([
                'certification_name' => 'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999),
                'university_name' => 'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999),
                'month_year' => 'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999),
                'user_id' => User::all()->random()->id
            ]);
            }

            // CREATE USER CATEGORIES FOR EVERY USER
            for ($u = 0; $u < 4; $u++) {
                DB::table('users_categories')->insert([
                    'services_category_id' => ServicesCategory::all()->random()->id,
                    'user_id' => User::all()->random()->id
                ]);
            }

        }


        // CREATE SERVICES
        for ($i = 0; $i < 50; $i++) {

            DB::table('services')->insert([
                'user_id' => User::all()->random()->id,
                'picture' => 'images/default-image.jpg',
                'zip_code' => rand(1000, 99999),
                'service_name' => 'Service ' . rand(1000, 99999),
                'services_category_id' => ServicesCategory::all()->random()->id,
                'willing_to_travel' => rand(100, 999),
                'longitude' => '76.73604',
                'latitude' => '30.65195',
                'geo_address' => '277 Bedford Ave, Brooklyn, NY 11211, USA',
                'hourly_rate' => rand(10, 99),
                'swap' => 1,
                'hire' => 1,
                'cancellation_terms_hour' => rand(12, 50),
                'service_descp' => 'Bla Bla ' . rand(1000, 99999)
            ]);

        }



        // SERVICES RATING
        for ($l = 1; $l < 80; $l++) {
            DB::table('services_rating')->insert([
                'service_id' => Services::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'time' =>  rand(1, 5),
                'communication' =>  rand(1, 5),
                'skills' =>  rand(1, 5),
                'quality_of_work' =>  rand(1, 5),
                'professionalism' =>  rand(1, 5),
                'star_rating' =>  rand(1, 5),
                
            ]);
        }

        // SERVICES RATING
        for ($l = 1; $l < 80; $l++) {
            DB::table('users_rating')->insert([
                'user_id' => User::all()->random()->id,
                'time' => rand(1, 5),
                'communication' => rand(1, 5),
                'skills' => rand(1, 5),
                'quality_of_work' => rand(1, 5),
                'professionalism' => rand(1, 5),
                'star_rating' => rand(1, 5),
                'sender_user_id' => User::all()->random()->id,
                'feedback' =>  'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999)
            ]);
        }



        // SERVICES REVIEWS
        for ($l = 1; $l < 80; $l++) {
            DB::table('services_reviews')->insert([
                'service_id' => Services::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'review' =>  'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999)
            ]);
        }


        // SWAP SERVICES
        for ($m = 1; $m < 50; $m++) {
            $user_id_const = User::all()->random()->id;
            $hour_want = rand(10, 100);
            DB::table('swap_services')->insert([
                'service_hours_swap' => rand(10, 100),
                'service_hours_want' => $hour_want,
                'user_sender_id' =>  $user_id_const,
                'service_sender_id' => Services::all()->random()->id,
                'user_receiver_id' => User::all()->random()->id,
                'service_receiver_id' => Services::all()->random()->id,
                'offer_instructions' =>  'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999),
                'boot_calculate' => rand(100, 1000),
                'boot_assign_person' =>  $user_id_const
            ]);

        }

        // HIRE SERVICES
        for ($n = 1; $n < 50; $n++) {
            $user_id_const = User::all()->random()->id;
            $hour_want = rand(10, 100);
            DB::table('hire_services')->insert([
                'service_hours_want' => $hour_want,
                'user_sender_id' =>  $user_id_const,
                'user_receiver_id' => User::all()->random()->id,
                'service_receiver_id' => Services::all()->random()->id,
                'offer_instructions' =>  'Bla Bla ' . rand(1000, 99999) . ' Bla Bla ' . rand(1000, 99999),
                'amount_to_be_paid' => rand(100, 10000)
            ]);

        }

      
        // USER TRANSACTIONS
        for ($o = 1; $o < 50; $o++) {
            DB::table('users_transactions')->insert([
                'sender_user_id' => User::all()->random()->id,
                'receiver_user_id' => User::all()->random()->id,
                'amount_paid' => rand(100, 10000),
            ]);
        }



        // Users Wallet
        $users_count = DB::table('users')->count();
        for ($o = 1; $o <= $users_count; $o++) {
            DB::table('users_wallet')->insert([
                'user_id' => $o,
                'wallet_balance' => rand(100, 10000),
            ]);
        }
        
    }
}
