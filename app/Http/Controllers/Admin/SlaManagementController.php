<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaHoliday;
use App\Models\SlaSetting;
use App\Models\SlaWorkingHourSetting;
use App\Services\Sla\NationalHolidayImporter;
use App\Services\Sla\SlaCalculationService;
use App\Services\Sla\SlaServiceRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class SlaManagementController extends Controller
{
    public function __construct(private SlaCalculationService $sla)
    {
    }

    /**
     * Dashboard pencapaian SLA seluruh layanan.
     */
    public function index(Request $request)
    {
        SlaSetting::ensureDefaults();

        [$from, $to, $bulan, $tahun] = $this->resolvePeriod($request);

        $result = $this->sla->summary($from, $to);

        return view('admin.sla.index', [
            'services' => $result['services'],
            'totals' => $result['totals'],
            'groups' => SlaServiceRegistry::groups(),
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Detail permohonan 1 layanan beserta durasi & capaian SLA.
     */
    public function show(Request $request, string $serviceKey)
    {
        if (! SlaServiceRegistry::find($serviceKey)) {
            abort(404, 'Layanan SLA tidak ditemukan.');
        }

        [$from, $to, $bulan, $tahun] = $this->resolvePeriod($request);

        $summary = $this->sla->summaryFor($serviceKey, $from, $to);
        $rows = $this->sla->detail($serviceKey, $from, $to, 25, (int) $request->get('page', 1));
        $rows->appends($request->query());

        return view('admin.sla.show', [
            'serviceKey' => $serviceKey,
            'summary' => $summary,
            'rows' => $rows,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Halaman pengaturan: target SLA per layanan, jam kerja, dan daftar libur.
     */
    public function settings()
    {
        SlaSetting::ensureDefaults();

        $settings = SlaSetting::orderBy('label')->get()->groupBy(function (SlaSetting $s) {
            return SlaServiceRegistry::find($s->service_key)['group'] ?? 'Lainnya';
        });

        $workingHours = SlaWorkingHourSetting::current();
        $holidays = SlaHoliday::orderBy('tanggal')->get();

        return view('admin.sla.settings', compact('settings', 'workingHours', 'holidays'));
    }

    public function updateSetting(Request $request, string $serviceKey)
    {
        if (! SlaServiceRegistry::find($serviceKey)) {
            abort(404, 'Layanan SLA tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'target_value' => 'required|integer|min:1|max:999',
            'target_unit' => 'required|in:jam,hari_kerja',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sla.pengaturan')->withErrors($validator)->withInput();
        }

        SlaSetting::where('service_key', $serviceKey)->update([
            'target_value' => $request->target_value,
            'target_unit' => $request->target_unit,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.sla.pengaturan')
            ->with('success', 'Target SLA berhasil diperbarui.');
    }

    public function updateWorkingHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'hari_kerja' => 'required|array|min:1',
            'hari_kerja.*' => 'integer|between:1,7',
        ], [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'hari_kerja.required' => 'Pilih minimal satu hari kerja.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sla.pengaturan')->withErrors($validator)->withInput();
        }

        $setting = SlaWorkingHourSetting::current();
        $setting->update([
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'hari_kerja' => array_values(array_map('intval', $request->hari_kerja)),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.sla.pengaturan')
            ->with('success', 'Pengaturan jam kerja berhasil diperbarui.');
    }

    public function storeHoliday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|unique:sla_holidays,tanggal',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal.unique' => 'Tanggal libur ini sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sla.pengaturan')->withErrors($validator)->withInput();
        }

        SlaHoliday::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'sumber' => SlaHoliday::SUMBER_MANUAL,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.sla.pengaturan')
            ->with('success', 'Tanggal libur berhasil ditambahkan.');
    }

    /**
     * Impor libur nasional & cuti bersama satu tahun dari API publik.
     * Tanggal yang diinput manual oleh admin tidak ikut tersentuh.
     */
    public function importHolidays(Request $request, NationalHolidayImporter $importer)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required|integer|min:2020|max:2100',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sla.pengaturan')->withErrors($validator);
        }

        try {
            $hasil = $importer->import((int) $request->tahun, $request->user()->id);
        } catch (RuntimeException $e) {
            return redirect()->route('admin.sla.pengaturan')
                ->with('error', 'Impor gagal: '.$e->getMessage());
        }

        return redirect()->route('admin.sla.pengaturan')->with('success', sprintf(
            'Impor libur %d selesai: %d ditambah, %d diperbarui, %d dihapus, %d dilewati (input manual).',
            $request->tahun,
            $hasil['ditambah'],
            $hasil['diperbarui'],
            $hasil['dihapus'],
            $hasil['dilewati'],
        ));
    }

    public function destroyHoliday(SlaHoliday $slaHoliday)
    {
        $slaHoliday->delete();

        return redirect()->route('admin.sla.pengaturan')
            ->with('success', 'Tanggal libur berhasil dihapus.');
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: int, 3: int}
     */
    private function resolvePeriod(Request $request): array
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : now()->month;
        $tahun = $tahun >= 2020 && $tahun <= 2100 ? $tahun : now()->year;

        $from = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return [$from, $to, $bulan, $tahun];
    }
}
