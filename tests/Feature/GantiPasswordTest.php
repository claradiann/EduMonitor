<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Skenario USR-09 sampai USR-11 (KF-U06): ganti password sendiri. */
class GantiPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_usr_09_ganti_password_berhasil(): void
    {
        $clara = User::where('username', 'clara')->first();

        $this->actingAs($clara)->put('/akun/password', [
            'password_lama' => 'password',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('akun.password'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('rahasia123', $clara->fresh()->password));
    }

    public function test_usr_10_password_lama_salah(): void
    {
        $clara = User::where('username', 'clara')->first();

        $this->actingAs($clara)->from('/akun/password')->put('/akun/password', [
            'password_lama' => 'salah',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors(['password_lama' => 'Password lama salah.']);

        $this->assertTrue(Hash::check('password', $clara->fresh()->password));
    }

    public function test_usr_11_konfirmasi_tidak_sama_atau_terlalu_pendek(): void
    {
        $clara = User::where('username', 'clara')->first();

        $this->actingAs($clara)->from('/akun/password')->put('/akun/password', [
            'password_lama' => 'password',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors('password');

        $this->actingAs($clara)->from('/akun/password')->put('/akun/password', [
            'password_lama' => 'password',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors('password');
    }
}