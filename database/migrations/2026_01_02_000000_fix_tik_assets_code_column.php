<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TikCategory;
use App\Models\TikAsset;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Generate codes for categories that don't have one
        $categories = TikCategory::all();

        foreach ($categories as $cat) {
            if (empty($cat->code)) {
                // Generate code from first 3 letters of name (uppercase)
                $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $cat->name), 0, 3));

                // If name is too short or has no letters, use generic code
                if (strlen($baseCode) < 2) {
                    $baseCode = 'CAT';
                }

                // Ensure uniqueness
                $code = $baseCode;
                $counter = 1;
                while (TikCategory::where('code', $code)->where('id', '!=', $cat->id)->exists()) {
                    $code = $baseCode . $counter;
                    $counter++;
                }

                $cat->code = $code;
                $cat->save();

                echo "Generated code '{$code}' for category '{$cat->name}'\n";
            }
        }

        // Step 2: Generate codes for assets that have NULL codes
        $categories = TikCategory::all();

        foreach ($categories as $cat) {
            $assets = TikAsset::where('tik_category_id', $cat->id)
                              ->whereNull('code')
                              ->orWhere('code', '')
                              ->get();

            if ($assets->count() > 0) {
                $prefix = $cat->code;
                $len = strlen($prefix);

                // Find the highest sequence number for this category
                $maxRow = TikAsset::where('code', 'like', $prefix.'%')
                    ->where('code', '!=', '')
                    ->whereNotNull('code')
                    ->selectRaw('CAST(SUBSTRING(code, '.($len+1).') AS UNSIGNED) AS seq')
                    ->orderByDesc('seq')
                    ->first();

                $seq = ($maxRow->seq ?? 0) + 1;

                foreach ($assets as $asset) {
                    $asset->code = $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
                    $asset->save();

                    echo "Generated code '{$asset->code}' for asset '{$asset->name}'\n";
                    $seq++;
                }
            }
        }

        // Step 3: Alter columns to NOT NULL (only if no NULL values remain)
        $nullAssets = TikAsset::whereNull('code')->orWhere('code', '')->count();
        $nullCategories = TikCategory::whereNull('code')->orWhere('code', '')->count();

        if ($nullAssets === 0 && $nullCategories === 0) {
            // Modify tik_assets.code to NOT NULL
            Schema::table('tik_assets', function (Blueprint $table) {
                $table->string('code')->nullable(false)->change();
            });

            // Modify tik_categories.code to NOT NULL
            Schema::table('tik_categories', function (Blueprint $table) {
                $table->string('code', 10)->nullable(false)->change();
            });

            echo "Columns altered to NOT NULL successfully\n";
        } else {
            echo "WARNING: Skipped altering columns - NULL values still exist:\n";
            echo "  - Assets with NULL code: {$nullAssets}\n";
            echo "  - Categories with NULL code: {$nullCategories}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert columns back to nullable
        Schema::table('tik_assets', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
        });

        Schema::table('tik_categories', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->change();
        });
    }
};
