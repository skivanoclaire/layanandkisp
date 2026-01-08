<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with NIK using raw DB query to avoid model casts
        $users = \DB::table('users')->whereNotNull('nik')->get();

        foreach ($users as $user) {
            if ($user->nik && !str_starts_with($user->nik, 'eyJpdiI6')) {
                // Only encrypt if not already encrypted (check for Laravel encryption format)
                try {
                    // Encrypt using Crypt facade directly
                    $encryptedNik = \Illuminate\Support\Facades\Crypt::encryptString($user->nik);

                    // Update directly via DB to bypass model casts
                    \DB::table('users')
                        ->where('id', $user->id)
                        ->update(['nik' => $encryptedNik]);
                } catch (\Exception $e) {
                    // Skip if encryption fails
                    \Log::error("Failed to encrypt NIK for user {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Decrypt NIK back to plain text
        $users = \DB::table('users')->whereNotNull('nik')->get();

        foreach ($users as $user) {
            if ($user->nik) {
                try {
                    // Try to decrypt
                    $decryptedNik = decrypt($user->nik);

                    // Update with decrypted value
                    \DB::table('users')
                        ->where('id', $user->id)
                        ->update(['nik' => $decryptedNik]);
                } catch (\Exception $e) {
                    // If already decrypted or invalid, skip
                    continue;
                }
            }
        }
    }
};
