<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //Admin account 
        $admin = User::firstOrNew(['email' => 'admin@freshlaundry.test']);
        $admin->fill([
            'name' => 'Admin',
            'phone'=> '0500000000',
            'password' => 'Admin12345',
        ]);
        $admin->role = 'admin'; //set it directly
        $admin->save();

        //test customer 
        $customer = User::firstOrNew(['email' => 'customer@freshlaundry.test']);
        $customer->fill([//firstornew == no duplicate
            'name'=> 'Test Customer',
            'phone' => '0511111111',
            'password' => 'Customer12345',
        ]);
        $customer->save();
    }
}
