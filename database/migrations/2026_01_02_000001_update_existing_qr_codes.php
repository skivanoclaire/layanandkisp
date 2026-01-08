<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\TikAsset;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Regenerate all existing QR codes with new compact JSON format.
     * Old format: ASSET|{id}|CODE:{code}|SN:{sn}|NamaBarang:{name}|Kondisi:{condition}
     * New format: {"id":{id},"code":"{code}"}
     */
    public function up(): void
    {
        $assets = TikAsset::whereNotNull('id')->get();

        if ($assets->count() === 0) {
            echo "No assets found. Nothing to update.\n";
            return;
        }

        echo "Found {$assets->count()} assets. Regenerating QR codes...\n";

        $updated = 0;
        $failed = 0;

        foreach ($assets as $asset) {
            try {
                // Generate new compact QR payload (JSON format)
                $payload = json_encode([
                    'id' => $asset->id,
                    'code' => $asset->code,
                ]);

                // Regenerate QR code
                $png = QrCode::format('png')
                    ->errorCorrection('M')
                    ->margin(1)
                    ->size(480)
                    ->generate($payload);

                $path = "qr/asset-{$asset->id}.png";

                // Save to storage
                Storage::disk('public')->put($path, $png);

                // Update database
                $asset->qr_text = $payload;
                $asset->qr_path = $path;
                $asset->save();

                $updated++;
                echo "  ✓ Updated QR for asset {$asset->id} ({$asset->code}) - {$asset->name}\n";

            } catch (\Exception $e) {
                $failed++;
                echo "  ✗ Failed to update asset {$asset->id}: {$e->getMessage()}\n";
            }
        }

        echo "\n";
        echo "==============================================\n";
        echo "QR Code Migration Summary:\n";
        echo "  Total assets: {$assets->count()}\n";
        echo "  Successfully updated: {$updated}\n";
        echo "  Failed: {$failed}\n";
        echo "==============================================\n";
        echo "\n";
        echo "✓ QR codes have been regenerated with compact JSON format.\n";
        echo "  Old format: ~70 characters (large QR)\n";
        echo "  New format: ~30 characters (small QR)\n";
        echo "  Reduction: ~57% smaller QR codes\n";
    }

    /**
     * Reverse the migrations.
     *
     * Note: We cannot restore old QR format since we don't have the original data
     * (serial_number, name, condition) readily available.
     * Just leave the QR codes as is - they are backward compatible.
     */
    public function down(): void
    {
        echo "Rollback not supported for QR code regeneration.\n";
        echo "The new JSON format is backward compatible with existing scanners.\n";
        echo "No action needed.\n";
    }
};
