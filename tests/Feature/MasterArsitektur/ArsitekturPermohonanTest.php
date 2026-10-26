<?php

namespace Tests\Feature\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use App\Models\MasterArsitektur\Permohonan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\AdminPendingCountsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pembaruan Data Arsitektur oleh pengguna instansi (usulan tambah/ubah)
 * dan persetujuannya oleh admin di Kelola Permohonan.
 */
class ArsitekturPermohonanTest extends TestCase
{
    use RefreshDatabase;

    private UnitKerja $dkisp;
    private UnitKerja $bapperida;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dkisp = UnitKerja::factory()->create(['nama' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN']);
        $this->bapperida = UnitKerja::factory()->create(['nama' => 'BADAN PERENCANAAN PEMBANGUNAN, RISET DAN INOVASI DAERAH']);
    }

    private function pengguna(string $role, ?UnitKerja $unit = null): User
    {
        $user = User::factory()->create(['is_verified' => true, 'unit_kerja_id' => $unit?->id]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', $role)->value('id'), 'user_id' => $user->id]);

        return $user;
    }

    private function layanan(UnitKerja $unit, array $ubah = []): Item
    {
        return Item::create($ubah + [
            'jenis' => 'layanan',
            'kode' => 'LYN-01',
            'nama' => 'Layanan Email Dinas',
            'status' => 'As-is',
            'unit_kerja_id' => $unit->id,
            'unit_kerja_teks' => $unit->nama,
            'atribut' => ['tujuan' => 'Surat menyurat elektronik', 'metode_layanan' => 'Elektronik'],
        ]);
    }

    public function test_permission_diberikan_ke_role_user_dan_admin(): void
    {
        foreach (['User' => 'Akses Pembaruan Data Arsitektur', 'Admin' => 'Kelola Permohonan Data Arsitektur'] as $role => $permission) {
            $this->assertTrue(
                DB::table('permission_role')
                    ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                    ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('roles.name', $role)->where('permissions.name', $permission)->exists(),
                "$permission belum diberikan ke role $role"
            );
        }
    }

    public function test_pengguna_hanya_melihat_data_instansinya(): void
    {
        $this->layanan($this->dkisp);
        $this->layanan($this->bapperida, ['kode' => 'LYN-02', 'nama' => 'Layanan Perencanaan']);

        $this->actingAs($this->pengguna('User', $this->dkisp))
            ->get(route('user.arsitektur.index', ['kategori' => 'layanan']))
            ->assertOk()
            ->assertSee('Layanan Email Dinas')
            ->assertSee('Usulkan Perubahan')
            ->assertDontSee('Layanan Perencanaan');
    }

    public function test_pengguna_tanpa_instansi_diminta_melengkapi_profil(): void
    {
        $user = $this->pengguna('User');

        $this->actingAs($user)->get(route('user.arsitektur.index'))
            ->assertOk()
            ->assertSee('belum terhubung ke instansi');

        $this->actingAs($user)->get(route('user.arsitektur.tambah', 'layanan'))->assertForbidden();
    }

    public function test_usulan_ubah_tidak_mengubah_data_sebelum_disetujui(): void
    {
        $item = $this->layanan($this->dkisp);
        $user = $this->pengguna('User', $this->dkisp);

        $this->actingAs($user)->get(route('user.arsitektur.ubah', $item))
            ->assertOk()
            ->assertSee('Layanan Email Dinas')
            ->assertSee('tidak dapat diubah');

        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), [
            'kode' => 'LYN-01',
            'nama' => 'Layanan Email Pemerintah',
            'status' => 'As-is',
            'atribut' => ['tujuan' => "Surat menyurat elektronik\r\n", 'metode_layanan' => 'Elektronik'],
            'catatan_pemohon' => 'Nama layanan berubah',
            // Upaya memindahkan entri ke instansi lain diabaikan
            'unit_kerja_id' => $this->bapperida->id,
        ])->assertSessionHas('success');

        $permohonan = Permohonan::sole();
        $this->assertSame('ubah', $permohonan->aksi);
        $this->assertSame('menunggu', $permohonan->status);
        $this->assertSame($this->dkisp->id, $permohonan->unit_kerja_id);
        $this->assertSame(['nama'], array_keys($permohonan->perubahan()));

        $item->refresh();
        $this->assertSame('Layanan Email Dinas', $item->nama);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);

        // Selama masih menunggu, entri yang sama tidak dapat diusulkan lagi
        $this->actingAs($user)->get(route('user.arsitektur.ubah', $item))
            ->assertRedirect(route('user.arsitektur.permohonan.show', $permohonan));
        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), ['nama' => 'Lain'])
            ->assertSessionHas('error');
        $this->assertSame(1, Permohonan::count());

        $this->actingAs($user)->get(route('user.arsitektur.index', ['kategori' => 'layanan']))
            ->assertSee('Menunggu persetujuan');
        $this->actingAs($user)->get(route('user.arsitektur.permohonan.show', $permohonan))
            ->assertOk()
            ->assertSee('Layanan Email Pemerintah')
            ->assertSee('Nama layanan berubah');
    }

    public function test_usulan_tanpa_perubahan_ditolak(): void
    {
        $item = $this->layanan($this->dkisp);

        $this->actingAs($this->pengguna('User', $this->dkisp))
            ->post(route('user.arsitektur.ubah.simpan', $item), [
                'kode' => 'LYN-01',
                'nama' => 'Layanan Email Dinas',
                'status' => 'As-is',
                'atribut' => ['tujuan' => 'Surat menyurat elektronik', 'metode_layanan' => 'Elektronik'],
            ])->assertSessionHas('error');

        $this->assertSame(0, Permohonan::count());
    }

    public function test_pengguna_tidak_dapat_mengusulkan_data_instansi_lain(): void
    {
        $item = $this->layanan($this->bapperida);
        $user = $this->pengguna('User', $this->dkisp);

        $this->actingAs($user)->get(route('user.arsitektur.ubah', $item))->assertForbidden();
        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), ['nama' => 'Diambil alih'])->assertForbidden();

        $permohonan = Permohonan::create([
            'user_id' => $this->pengguna('User', $this->bapperida)->id,
            'unit_kerja_id' => $this->bapperida->id,
            'item_id' => $item->id,
            'aksi' => 'ubah',
            'jenis' => 'layanan',
            'data' => ['nama' => 'X'],
            'data_lama' => ['nama' => 'Layanan Email Dinas'],
        ]);

        $this->actingAs($user)->get(route('user.arsitektur.permohonan.show', $permohonan))->assertForbidden();
        $this->actingAs($user)->delete(route('user.arsitektur.permohonan.batal', $permohonan))->assertForbidden();
        $this->assertSame(1, Permohonan::count());
    }

    public function test_admin_menyetujui_usulan_ubah_dan_hanya_kolom_yang_diusulkan_berubah(): void
    {
        $item = $this->layanan($this->dkisp);
        $user = $this->pengguna('User', $this->dkisp);
        $admin = $this->pengguna('Admin');

        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), [
            'kode' => 'LYN-01',
            'nama' => 'Layanan Email Pemerintah',
            'status' => 'As-is',
            'atribut' => ['tujuan' => 'Surat menyurat elektronik', 'metode_layanan' => 'Elektronik'],
        ]);
        $permohonan = Permohonan::sole();

        // Admin sempat mengubah kolom lain sebelum usulan diproses
        $item->update(['status' => 'Metadata Upgrade (to-be)']);

        $this->assertSame(1, app(AdminPendingCountsService::class)->countsFor($admin)['arsitektur_permohonan']);

        $this->actingAs($admin)->get(route('admin.arsitektur-permohonan.index'))
            ->assertOk()
            ->assertSee('Layanan Email Pemerintah')
            ->assertSee('DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN');
        $this->actingAs($admin)->get(route('admin.arsitektur-permohonan.show', $permohonan))
            ->assertOk()
            ->assertSee('Layanan Email Dinas')
            ->assertSee('Layanan Email Pemerintah')
            ->assertSee('Setujui dan Terapkan');

        $this->actingAs($admin)->post(route('admin.arsitektur-permohonan.setujui', $permohonan), ['catatan_admin' => 'Sesuai'])
            ->assertRedirect(route('admin.arsitektur-permohonan.show', $permohonan))
            ->assertSessionHas('success');

        $item->refresh();
        $this->assertSame('Layanan Email Pemerintah', $item->nama);
        $this->assertSame('Metadata Upgrade (to-be)', $item->status);
        $this->assertSame('Elektronik', $item->atribut['metode_layanan']);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);

        $permohonan->refresh();
        $this->assertSame('disetujui', $permohonan->status);
        $this->assertSame($admin->id, $permohonan->diproses_oleh);
        $this->assertNotNull($permohonan->diproses_pada);

        // Tidak dapat diproses dua kali
        $this->actingAs($admin)->post(route('admin.arsitektur-permohonan.tolak', $permohonan), ['catatan_admin' => 'x'])
            ->assertSessionHas('error');
        $this->assertSame('disetujui', $permohonan->fresh()->status);
        $this->assertSame(0, app(AdminPendingCountsService::class)->countsFor($admin)['arsitektur_permohonan']);
    }

    public function test_usulan_tambah_menjadi_entri_instansi_pemohon_setelah_disetujui(): void
    {
        $user = $this->pengguna('User', $this->dkisp);
        $admin = $this->pengguna('Admin');

        $this->actingAs($user)->get(route('user.arsitektur.tambah', 'layanan'))->assertOk()->assertSee('Nama Layanan');
        $this->actingAs($user)->get(route('user.arsitektur.tambah', 'keamanan'))->assertNotFound();

        $this->actingAs($user)->post(route('user.arsitektur.tambah.simpan', 'layanan'), ['jenis' => 'layanan'])
            ->assertSessionHasErrors('nama');

        $this->actingAs($user)->post(route('user.arsitektur.tambah.simpan', 'layanan'), [
            'jenis' => 'layanan',
            'nama' => 'Layanan Tanda Tangan Elektronik',
            'atribut' => ['metode_layanan' => 'Elektronik'],
            'unit_kerja_id' => $this->bapperida->id,
        ])->assertSessionHas('success');

        $permohonan = Permohonan::sole();
        $this->assertSame('tambah', $permohonan->aksi);
        $this->assertSame(0, Item::count());

        $this->actingAs($admin)->post(route('admin.arsitektur-permohonan.setujui', $permohonan))
            ->assertSessionHas('success');

        $item = Item::sole();
        $this->assertSame('layanan', $item->jenis);
        $this->assertSame('Layanan Tanda Tangan Elektronik', $item->nama);
        $this->assertSame('Elektronik', $item->atribut['metode_layanan']);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);
        $this->assertSame($this->dkisp->nama, $item->unit_kerja_teks);
        $this->assertSame($item->id, $permohonan->fresh()->item_id);
    }

    public function test_admin_menolak_dengan_alasan_dan_data_tidak_berubah(): void
    {
        $item = $this->layanan($this->dkisp);
        $user = $this->pengguna('User', $this->dkisp);
        $admin = $this->pengguna('Admin');

        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), ['kode' => 'LYN-01', 'nama' => 'Nama Keliru']);
        $permohonan = Permohonan::sole();

        $this->actingAs($admin)->post(route('admin.arsitektur-permohonan.tolak', $permohonan), [])
            ->assertSessionHasErrors('catatan_admin');

        $this->actingAs($admin)->post(route('admin.arsitektur-permohonan.tolak', $permohonan), ['catatan_admin' => 'Nama tidak sesuai SK'])
            ->assertSessionHas('success');

        $this->assertSame('ditolak', $permohonan->fresh()->status);
        $this->assertSame('Layanan Email Dinas', $item->fresh()->nama);

        $this->actingAs($user)->get(route('user.arsitektur.permohonan'))
            ->assertOk()
            ->assertSee('Ditolak');
        $this->actingAs($user)->get(route('user.arsitektur.permohonan.show', $permohonan))
            ->assertSee('Nama tidak sesuai SK');

        // Setelah ditolak, entri dapat diusulkan lagi
        $this->actingAs($user)->get(route('user.arsitektur.ubah', $item))->assertOk();
    }

    public function test_pemohon_dapat_membatalkan_usulan_yang_masih_menunggu(): void
    {
        $item = $this->layanan($this->dkisp);
        $user = $this->pengguna('User', $this->dkisp);

        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), ['kode' => 'LYN-01', 'nama' => 'Nama Baru']);
        $permohonan = Permohonan::sole();

        $this->actingAs($user)->delete(route('user.arsitektur.permohonan.batal', $permohonan))
            ->assertRedirect(route('user.arsitektur.permohonan'));

        $this->assertSame(0, Permohonan::count());
        $this->assertSame('Layanan Email Dinas', $item->fresh()->nama);
    }

    public function test_halaman_admin_tertutup_bagi_pengguna_biasa(): void
    {
        $item = $this->layanan($this->dkisp);
        $user = $this->pengguna('User', $this->dkisp);

        $this->actingAs($user)->post(route('user.arsitektur.ubah.simpan', $item), ['kode' => 'LYN-01', 'nama' => 'Nama Baru']);
        $permohonan = Permohonan::sole();

        $this->actingAs($user)->get(route('admin.arsitektur-permohonan.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.arsitektur-permohonan.setujui', $permohonan))->assertForbidden();

        $this->assertSame('menunggu', $permohonan->fresh()->status);
        $this->assertSame('Layanan Email Dinas', $item->fresh()->nama);

        $this->post('/logout');
        $this->get(route('user.arsitektur.index'))->assertRedirect();
    }
}
