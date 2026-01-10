<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Karena tidak pakai migration, kita bikin tabel untuk testing di sini
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('peran', 20)->default('member'); // sqlite aman pakai string
            $table->timestamps();
        });
    }

    private function csrfPost(string $uri, array $data = [])
    {
        // Web routes kamu kena CSRF middleware, jadi kita set token yg match session
        return $this->withSession(['_token' => 'testtoken'])
            ->post($uri, array_merge($data, ['_token' => 'testtoken']));
    }

    public function test_halaman_login_bisa_diakses()
    {
        $res = $this->get('/masuk');
        $res->assertStatus(200);
    }

    public function test_login_gagal_jika_password_salah()
    {
        DB::table('users')->insert([
            'nama' => 'Admin Petugas',
            'email' => 'staff@demo.com',
            'password' => Hash::make('password123'),
            'peran' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->csrfPost('/masuk', [
            'email' => 'staff@demo.com',
            'password' => 'salah',
        ]);

        // MasukController kamu: return back()->with('gagal', 'Email / password salah.')
        $res->assertStatus(302);
        $res->assertSessionHas('gagal');
        $this->assertNull(session('user')); // session user tidak boleh terisi
    }

    public function test_login_berhasil_set_session_user()
    {
        DB::table('users')->insert([
            'nama' => 'Budi Member',
            'email' => 'member@demo.com',
            'password' => Hash::make('password123'),
            'peran' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->csrfPost('/masuk', [
            'email' => 'member@demo.com',
            'password' => 'password123',
        ]);

        $res->assertStatus(302);

        // Pastikan session 'user' terisi
        $res->assertSessionHas('user');

        $user = session('user');
        $this->assertEquals('member', $user['peran']);
        $this->assertEquals('member@demo.com', $user['email']);
    }

    public function test_logout_menghapus_session_user()
    {
        // set session user dulu
        $this->withSession([
            'user' => [
                'id' => 1,
                'nama' => 'Budi',
                'email' => 'member@demo.com',
                'peran' => 'member',
            ],
            '_token' => 'testtoken',
        ]);

        $res = $this->post('/keluar', ['_token' => 'testtoken']);

        $res->assertStatus(302);
        $this->assertNull(session('user'));
    }
}
