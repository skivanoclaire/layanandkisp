<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TikCategory;

class TikCategoryCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Generate codes for categories that don't have one.
     * This is useful for existing data that was created before the code column was added.
     */
    public function run(): void
    {
        $categories = TikCategory::whereNull('code')
                                  ->orWhere('code', '')
                                  ->get();

        if ($categories->count() === 0) {
            $this->command->info('All categories already have codes. Nothing to do.');
            return;
        }

        $this->command->info("Found {$categories->count()} categories without codes.");

        foreach ($categories as $category) {
            // Generate code from first 3 letters of name (uppercase, letters only)
            $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $category->name), 0, 3));

            // If name is too short or has no letters, use generic code
            if (strlen($baseCode) < 2) {
                $baseCode = 'CAT';
            }

            // Ensure uniqueness
            $code = $baseCode;
            $counter = 1;
            while (TikCategory::where('code', $code)->where('id', '!=', $category->id)->exists()) {
                $code = $baseCode . $counter;
                $counter++;
            }

            $category->code = $code;
            $category->save();

            $this->command->info("Generated code '{$code}' for category '{$category->name}' (ID: {$category->id})");
        }

        $this->command->info("\n✓ Successfully generated codes for {$categories->count()} categories.");
    }
}
