<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Skenario AUT di docs/07-pengujian.md. Data dari MonitoringDemoSeeder, semua password: "password". */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_aut_01_login_dengan_username(): void
    {
        $this->post('/login', ['login_id' => 'thomas', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('username', 'thomas')->first());
    }

    public function test_aut_02_login_dengan_email(): void
    {
        $email = User::where('username', 'thomas')->value('email');

        $this->post('/login', ['login_id' => $email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_aut_03_password_salah(): void
    {
        $this->from('/')->post('/login', ['login_id' => 'thomas', 'password' => 'salah123'])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['login_id' => 'Username/email atau password salah.'])
            ->assertSessionHasInput('login_id', 'thomas');
        $this->assertGuest();
    }

    public function test_aut_04_isian_kosong(): void
    {
        $this->from('/')->post('/login', ['login_id' => '', 'password' => ''])
            ->assertSessionHasErrors(['login_id', 'password']);
        $this->assertGuest();
    }

    public function test_aut_05_dashboard_sesuai_peran(): void
    {
        foreach (['admin', 'thomas', 'clara', 'budi'] as $username) {
            $this->actingAs(User::where('username', $username)->first())
                ->get('/dashboard')
                ->assertOk();
        }
    }

    public function test_aut_06_logout_menghapus_sesi(): void
    {
        $this->actingAs(User::where('username', 'thomas')->first());

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_aut_07_halaman_tanpa_login_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
    }

    public function test_aut_08_login_demo_tidak_tersedia_di_luar_local(): void
    {
        // Pengujian berjalan dengan APP_ENV=testing, jadi route demo tidak terdaftar (BUG-02, BR-04).
        $this->get('/demo-login/admin')->assertNotFound();
        $this->assertGuest();
    }
}