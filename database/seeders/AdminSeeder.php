<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'nik'=> '1001',
            'phone'=> '1001',
            'email' => 'admin@kaltaraprov.go.id', // kalau tetap email
            'password' => Hash::make('Kaltara2024!@#'), // password = password
            'role' => 'admin',
        ]);
    }
}
