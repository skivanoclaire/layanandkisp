<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Password diambil dari env ADMIN_PASSWORD, kalau tidak ada dibuat acak.
        $password = env('ADMIN_PASSWORD') ?: Str::password(20);

        User::create([
            'name' => 'Super Admin',
            'nik'=> '1001',
            'phone'=> '1001',
            'email' => 'admin@kaltaraprov.go.id',
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->command?->warn('Password Super Admin: '.$password);
        $this->command?->warn('Simpan sekarang, password ini tidak ditampilkan lagi.');
    }
}
