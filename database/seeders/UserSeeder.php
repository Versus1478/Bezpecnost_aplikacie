<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::query()->delete();

        DB::table('users')->insert([
            [
                'first_name' => 'Pán',
                'last_name' => 'Admin',
                'email' => 'admin@ukf.sk',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Dávid',
                'last_name' => 'Držík',
                'email' => 'ddrzik@ukf.sk',
                'password' => Hash::make('456'),
                'role' => 'user',
                'premium_until' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Jozef',
                'last_name' => 'Kapusta',
                'email' => 'jkapusta@ukf.sk',
                'password' => Hash::make('789'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name'=>'Anna',
                'last_name'=>'Novakova',
                'email'=>'anna@ukf.sk',
                'password'=>Hash::make('123'),
                'role'=>'user',
                'premium_until' => now()->addDays(30),
                'created_at'=>now(),
                'updated_at'=>now()
            ],
            [
                'first_name'=>'Peter',
                'last_name'=>'Hrasko',
                'email'=>'peter@ukf.sk',
                'password'=>Hash::make('123'),
                'role'=>'user',
                'premium_until' => null,
                'created_at'=>now(),
                'updated_at'=>now()
            ]
        ]);
    }
}
