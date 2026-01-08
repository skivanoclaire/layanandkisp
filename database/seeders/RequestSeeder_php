<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\User;

class RequestSeeder extends Seeder
{
    public function run()
    {
        // Cari semua user biasa (role = user)
        $users = User::where('role', 'user')->get();

        // Kalau belum ada user, kita stop dulu
        if ($users->isEmpty()) {
            echo "Tidak ada user untuk diassign permohonan.\n";
            return;
        }

        foreach ($users as $user) {
            // Setiap user dapat beberapa permohonan dummy
            for ($i = 1; $i <= rand(1, 3); $i++) {
                Request::create([
                    'user_id' => $user->id,
                    'service' => $this->randomService(),
                    'file' => null, // anggap belum upload surat
                    'status' => $this->randomStatus(),
                ]);
            }
        }
    }

    private function randomService()
    {
        $services = [
            'Subdomain', 'Email', 'Hosting', 'Cloud Storage', 'VPN', 
            'Wifi Publik', 'Videotron', 'Peliputan', 'Helpdesk TIK', 'TTE'
        ];

        return $services[array_rand($services)];
    }

    private function randomStatus()
    {
        $statuses = ['Menunggu', 'Dalam Proses', 'Ditolak', 'Selesai'];

        return $statuses[array_rand($statuses)];
    }
}
