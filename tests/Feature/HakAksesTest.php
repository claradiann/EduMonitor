<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\LaporanSiswa;
use App\Models\Notifikasi;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Skenario AKS di docs/07-pengujian.md. Semua akses yang tidak berhak harus ditolak 403 (BR-02, BR-03). */
class HakAksesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function sebagai(string $username): static
    {
        return $this->actingAs(User::where('username', $username)->firstOrFail());
    }

    public function test_aks_01_03_selain_admin_tidak_bisa_membuka_kelola_pengguna(): void
    {
        foreach (['clara', 'thomas', 'budi'] as $username) {
            $this->sebagai($username)->get('/admin/users')->assertForbidden();
        }
    }

    public function test_aks_01b_selain_admin_tidak_bisa_menambah_mengubah_menghapus_pengguna(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->sebagai('clara')->post('/admin/users', [
            'name' => 'Penyusup', 'username' => 'penyusup', 'role' => 'admin', 'password' => '123456',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['username' => 'penyusup']);

        $this->sebagai('clara')->put("/admin/users/{$admin->id}", ['name' => 'Diubah', 'username' => 'admin'])->assertForbidden();
        $this->sebagai('clara')->delete("/admin/users/{$admin->id}")->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => $admin->name]);
    }

    public function test_aks_admin_tetap_bisa_membuka_kelola_pengguna(): void
    {
        $this->sebagai('admin')->get('/admin/users')->assertOk();
    }

    public function test_aks_04_siswa_membuka_rekap_kinerja(): void
    {
        $this->sebagai('clara')->get('/admin/recap')->assertForbidden();
    }

    public function test_aks_05_guru_membuka_halaman_admin(): void
    {
        foreach (['/admin/jadwal', '/admin/akses', '/admin/pengaturan', '/admin/laporan-perkembangan'] as $url) {
            $this->sebagai('thomas')->get($url)->assertForbidden();
        }
    }

    public function test_aks_06_orang_tua_membuka_halaman_guru(): void
    {
        $this->sebagai('budi')->get('/guru/penilaian')->assertForbidden();
    }

    public function test_aks_07_siswa_membuka_input_nilai(): void
    {
        $this->sebagai('clara')->get('/guru/penilaian/' . Assessment::first()->id)->assertForbidden();
    }

    public function test_aks_08_admin_membuka_lapor_siswa(): void
    {
        $this->sebagai('admin')->get('/siswa/lapor')->assertForbidden();
    }

    public function test_aks_09_orang_tua_membuka_evaluasi(): void
    {
        $this->sebagai('budi')->get('/siswa/evaluasi')->assertForbidden();
    }

    public function test_aks_10_admin_mengekspor_nilai_guru(): void
    {
        $this->sebagai('admin')->get('/guru/ekspor/' . SubjectTeacher::first()->id)->assertForbidden();
    }

    public function test_aks_11_guru_mengisi_evaluasi(): void
    {
        $this->sebagai('thomas')->get('/evaluation/fill/' . SubjectTeacher::first()->id)->assertForbidden();
    }

    public function test_aks_12_siswa_membuka_profil_orang_tua(): void
    {
        $this->sebagai('clara')->get('/orangtua/profile')->assertForbidden();
    }

    public function test_aks_13_guru_membuka_penilaian_guru_lain(): void
    {
        $thomas = User::where('username', 'thomas')->first();
        $milikLain = Assessment::whereHas('subjectTeacher', fn ($q) => $q->where('teacher_id', '!=', $thomas->id))->firstOrFail();

        $this->sebagai('thomas')->get("/guru/penilaian/{$milikLain->id}")->assertForbidden();
    }

    public function test_aks_14_orang_tua_membuka_laporan_anak_lain(): void
    {
        $budi = User::where('username', 'budi')->first();
        $bukanAnak = LaporanSiswa::whereNotIn('student_id', $budi->children->pluck('id'))->firstOrFail();

        $this->sebagai('budi')->get("/laporan/{$bukanAnak->id}")->assertForbidden();
    }

    public function test_aks_15_orang_tua_memilih_anak_orang_lain(): void
    {
        $budi = User::where('username', 'budi')->first();
        $bukanAnak = User::where('role', 'siswa')->whereNotIn('id', $budi->children->pluck('id'))->firstOrFail();

        $this->sebagai('budi')->post('/pilih-anak', ['anak_id' => $bukanAnak->id])->assertForbidden();
    }

    public function test_aks_16_siswa_mengisi_evaluasi_guru_kelas_lain(): void
    {
        $clara = User::where('username', 'clara')->first();
        $kelasLain = SubjectTeacher::where('kelas_id', '!=', $clara->kelas_id)->firstOrFail();

        $this->sebagai('clara')->get("/evaluation/fill/{$kelasLain->id}")->assertForbidden();
    }

    public function test_aks_17_membuka_notifikasi_milik_orang_lain(): void
    {
        $clara = User::where('username', 'clara')->first();
        $milikLain = Notifikasi::where('user_id', '!=', $clara->id)->firstOrFail();

        $this->sebagai('clara')->get("/notifikasi/{$milikLain->id}")->assertForbidden();
    }

    public function test_aks_18_guru_bukan_pembina_membuka_ekskul(): void
    {
        $thomas = User::where('username', 'thomas')->first();
        $guruLain = User::where('role', 'guru')->where('id', '!=', $thomas->id)->firstOrFail();
        $ekskulThomas = \App\Models\Ekstrakurikuler::where('pembina_id', $thomas->id)->firstOrFail();

        $this->actingAs($guruLain)->get("/ekskul/{$ekskulThomas->id}")->assertForbidden();
        $this->sebagai('clara')->get("/ekskul/{$ekskulThomas->id}")->assertForbidden();
    }

    /** Setiap halaman GET yang memang hak suatu peran harus tetap terbuka (memastikan grup route tidak terlalu ketat). */
    public function test_aks_halaman_milik_peran_tetap_terbuka(): void
    {
        $daftar = [
            'admin' => ['/admin/users', '/admin/recap', '/admin/laporan', '/admin/laporan-perkembangan', '/admin/pengaturan', '/admin/jadwal', '/admin/akses', '/admin/ekskul', '/admin/profile', '/akun/password'],
            'thomas' => ['/guru/penilaian', '/guru/feedback', '/guru/laporan', '/guru/ekskul', '/guru/profile', '/akun/password'],
            'clara' => ['/nilai', '/jadwal', '/laporan', '/siswa/evaluasi', '/siswa/lapor', '/siswa/profile', '/akun/password'],
            'budi' => ['/nilai', '/jadwal', '/laporan', '/orangtua/profile', '/akun/password'],
        ];

        foreach ($daftar as $username => $urls) {
            foreach ($urls as $url) {
                $this->sebagai($username)->get($url)->assertOk();
            }
        }
    }
}