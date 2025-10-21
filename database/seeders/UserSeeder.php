<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
            User::create([
                'name' => 'Bayu Adi Hartanto ',
                'nik' => '647101', 
                'phone' => '08122346', 
                'email' => 'skivanoc@gmail.com',
                'password' => Hash::make('BayuAdi1208!'),
                'role' => 'user',
            ]);
        
    }
}
