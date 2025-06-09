<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Loop buat 10 user dummy
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'User Dummy ' . $i,
                'username' => 'userdummy' . $i, // username unik
                'email' => 'userdummy' . $i . '@kaltaraprov.go.id',
                'password' => Hash::make('password'), // password semua = 'password'
                'role' => 'user',
            ]);
        }
    }
}
