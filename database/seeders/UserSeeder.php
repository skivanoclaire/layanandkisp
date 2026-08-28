<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Password diambil dari env USER_PASSWORD, kalau tidak ada dibuat acak.
        $password = env('USER_PASSWORD') ?: Str::password(20);

        User::create([
            'name' => 'Bayu Adi Hartanto',
            'nik' => '647101',
            'phone' => '08122346',
            'email' => 'skivanoc@gmail.com',
            'password' => Hash::make($password),
            'role' => 'user',
        ]);

        $this->command?->warn('Password user skivanoc@gmail.com: '.$password);
        $this->command?->warn('Simpan sekarang, password ini tidak ditampilkan lagi.');
    }
}
