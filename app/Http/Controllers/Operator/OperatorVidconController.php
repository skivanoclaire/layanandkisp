<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\VidconData;
use App\Models\VidconDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OperatorVidconController extends Controller
{
    /**
     * Display a list of vidcon tasks assigned to the current operator
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get vidcon data where current user is assigned as operator
        $query = VidconData::with(['unitKerja', 'operators', 'documentations'])
            ->whereHas('operators', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->where('tanggal_mulai', '>=', now()->format('Y-m-d'));
            } elseif ($request->status === 'completed') {
                $query->where('tanggal_selesai', '<', now()->format('Y-m-d'));
            }
        }

        $vidconTasks = $query->orderBy('tanggal_mulai', 'asc')->paginate(15);

        return view('operator.vidcon.index', compact('vidconTasks'));
    }

    /**
     * Show the form for uploading documentation
     */
    public function show($id)
    {
        $vidconData = VidconData::with(['unitKerja', 'operators', 'documentations.uploader'])
            ->findOrFail($id);

        // Check if current user is assigned to this task
        $user = auth()->user();
        if (!$vidconData->operators->contains($user)) {
            abort(403, 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        return view('operator.vidcon.show', compact('vidconData'));
    }

    /**
     * Store documentation photos
     */
    public function storeDocumentation(Request $request, $id)
    {
        $vidconData = VidconData::findOrFail($id);

        // Check if current user is assigned to this task
        $user = auth()->user();
        if (!$vidconData->operators->contains($user)) {
            abort(403, 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,jpg,png,heic,heif|max:10240', // Max 10MB per photo, support HEIC
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $uploadedFiles = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $originalExtension = strtolower($photo->getClientOriginalExtension());
                $isHeic = in_array($originalExtension, ['heic', 'heif']);

                // Convert HEIC to JPEG or process with EXIF orientation fix
                if ($isHeic) {
                    // HEIC image - convert to JPEG
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($photo);
                    $image->orient(); // Fix EXIF orientation
                    $fileName = time() . '_' . $index . '_' . pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';

                    // Save as JPEG with compression
                    $filePath = 'vidcon_documentations/' . $fileName;
                    $fullPath = storage_path('app/public/' . $filePath);
                    $image->save($fullPath, quality: 85);
                    $filePath = str_replace('\\', '/', $filePath);
                } else {
                    // Regular images (JPEG, PNG) - process with orientation fix
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($photo);
                    $image->orient(); // Fix EXIF orientation for all images
                    $fileName = time() . '_' . $index . '_' . $photo->getClientOriginalName();
                    $filePath = 'vidcon_documentations/' . $fileName;
                    $fullPath = storage_path('app/public/' . $filePath);
                    $image->save($fullPath, quality: 90);
                    $filePath = str_replace('\\', '/', $filePath);
                }

                $documentation = VidconDocumentation::create([
                    'vidcon_data_id' => $vidconData->id,
                    'uploaded_by' => $user->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'caption' => $request->captions[$index] ?? null,
                    'keterangan' => $request->keterangan,
                ]);

                $uploadedFiles[] = $documentation;
            }
        }

        return redirect()->route('operator.vidcon.show', $vidconData->id)
            ->with('success', count($uploadedFiles) . ' foto dokumentasi berhasil diupload.');
    }

    /**
     * Delete a documentation photo
     */
    public function deleteDocumentation($id)
    {
        $documentation = VidconDocumentation::findOrFail($id);

        // Check if current user uploaded this or is assigned to the task
        $user = auth()->user();
        $vidconData = $documentation->vidconData;

        if ($documentation->uploaded_by !== $user->id && !$vidconData->operators->contains($user)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus dokumentasi ini.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($documentation->file_path)) {
            Storage::disk('public')->delete($documentation->file_path);
        }

        $documentation->delete();

        return redirect()->back()->with('success', 'Foto dokumentasi berhasil dihapus.');
    }
}
