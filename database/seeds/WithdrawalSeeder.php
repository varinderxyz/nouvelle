<?php

use Illuminate\Database\Seeder;
use App\User;
use App\ServicesCategory;
use App\Services;
use Carbon\Carbon;
use App\SwapServices;
use App\Accounts;
use App\HireServices;
use Faker\Generator as Faker;

class WithdrawalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        // Accounts
        for ($o = 1; $o <= 5; $o++) {
            DB::table('accounts')->insert([
                'user_id' => User::all()->random()->id,
                'account_type' => "accountno",
                'account_value' => rand(1111111111,9999999999)
            ]);
        }
        // Accounts
        for ($o = 1; $o <= 5; $o++) {
            DB::table('accounts')->insert([
                'user_id' => User::all()->random()->id,
                'account_type' => "paypal",
                'account_value' => $faker->unique()->safeEmail
            ]);
        }
        // Withdrawal
        $users_count = 10; 
        for ($o = 1; $o <= $users_count - 5; $o++) {
            DB::table('withdrawals')->insert([
                'user_id' => User::all()->random()->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "user". $o,
                'amount' => "$".rand(100, 10000),
                'payment_method_id' => Accounts::all()->random()->id,
                'payment_method_type' => "paypal",
                'payment_method_value' => $faker->unique()->safeEmail,
                'status' => 'pending'
            ]);
        }

        for ($o = 1; $o <= $users_count; $o++) {
            DB::table('withdrawals')->insert([
                'user_id' => User::all()->random()->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "user". $o,
                'amount' => "$".rand(100, 10000),
                'payment_method_id' => Accounts::all()->random()->id,
                'payment_method_type' => "accountno",
                'payment_method_value' => rand(1111111111,9999999999),
                'status' => 'pending'
            ]);
        }

        // Swap Services Dispute
        $users_count = DB::table('users')->count(); 
        for ($o = 1; $o <= $users_count; $o++) {
            DB::table('swap_services_dispute')->insert([
                'user_id' => User::all()->random()->id,
                'against_user_id' => User::all()->random()->id,
                'service_id' => SwapServices::all()->random()->id,
                'reason' => 'Random xyz Reason',
                'dispute_description' => 'It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages',
                'comments' => 'some random comment',
                'status' => 'pending'
            ]);
        }
    }
}
