<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SurveiDigitalService;
use Illuminate\Http\Request;

/**
 * Halaman "Beri Penilaian" generik untuk seluruh layanan berbasis permohonan.
 *
 * Slug layanan diinjeksikan lewat route ->defaults('service', '<slug>'),
 * sehingga satu method melayani semua layanan tanpa duplikasi controller.
 */
class SurveiDigitalController extends Controller
{
    public function show(Request $request, $id)
    {
        $slug = $request->route('service');
        $service = SurveiDigitalService::service($slug);

        abort_if(! $service || ! $service['model'], 404);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $service['model']::findOrFail($id);

        // Hanya pemilik permohonan yang boleh mengakses
        if ((int) $model->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // Penilaian hanya tersedia untuk permohonan yang sudah selesai
        if ($model->status !== 'selesai') {
            abort(403, 'Penilaian hanya tersedia untuk permohonan yang sudah selesai.');
        }

        $subtitle = $model->ticket_no
            ?? $model->nomor_tiket
            ?? ('#' . $model->getKey());

        return view('survei-digital.embed', [
            'heading'   => $service['heading'],
            'color'     => $service['color'],
            'subtitle'  => $subtitle,
            'backUrl'   => route($service['route_index']),
            'surveyUrl' => SurveiDigitalService::urlFor($slug),
        ]);
    }
}
