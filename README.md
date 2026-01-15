# Aplikasi Perpustakaan Sederhana (Laravel + MySQL)

Aplikasi Perpustakaan Sederhana  berbasis **Laravel** dengan database **MySQL** untuk mensimulasikan proses peminjaman buku secara basic. Sistem menggunakan **2 peran (role)**: **staff** dan **member**.

- **Staff** bertugas mengelola data buku serta mencatat transaksi peminjaman dan pengembalian.
- **Member** hanya dapat melihat katalog buku dan melihat daftar peminjaman miliknya.


---

## Teknologi yang Digunakan

- Framework: **Laravel**
- Database: **MySQL** (dibuat via MySQL Workbench / SQL script, **tanpa migration**)
- Frontend: **Blade + HTML/CSS (flat simple)**
- Auth: **Login custom (session-based)** menggunakan bcrypt untuk password

---

## Fitur Utama

### Member
1. **Login & Logout**
2. **Lihat Katalog Buku** (judul dan stok)
3. **Lihat Peminjaman Saya** (daftar buku yang dipinjam/dikembalikan)

> Catatan: Member **tidak bisa melakukan aksi pinjam**, peminjaman dicatat oleh staff.

### Staff
1. **Login & Logout**
2. **Kelola Buku (CRUD)**
   - Lihat list buku
   - Tambah buku
   - Edit buku
   - Hapus buku (tergantung constraint FK)
3. **Kelola Peminjaman**
   - Buat peminjaman (pilih member + pilih buku + qty)
   - Pengembalian buku (status berubah + stok bertambah)
   - Lihat daftar peminjaman

---

## Struktur Database (Ringkas)

Database: `perpustakaan_lsp`

### Tabel
1. **users**
   - `id`, `nama`, `email`, `password`, `peran` (member/staff), `created_at`, `updated_at`

2. **buku**
   - `id_buku`, `judul`, `stok`, `created_at`, `updated_at`

3. **peminjaman** (header transaksi)
   - `id_peminjaman`, `id_member`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `status`, `created_at`, `updated_at`

4. **peminjaman_detail** (detail transaksi)
   - `id_detail`, `id_peminjaman`, `id_buku`, `qty`, `created_at`, `updated_at`
   - UNIQUE: (`id_peminjaman`, `id_buku`)

### Relasi
- `users (member)` **1..N** `peminjaman`
- `peminjaman` **1..N** `peminjaman_detail`
- `buku` **1..N** `peminjaman_detail`

---

## Alur Sistem (Workflow)

### Login
User login menggunakan email dan password. Jika valid, data user disimpan ke session:
- `id`, `nama`, `email`, `peran`

### Peminjaman (oleh Staff)
1. Staff pilih **member**
2. Staff pilih **buku** dan **qty**
3. Sistem cek stok buku
4. Sistem insert:
   - `peminjaman` (status: `dipinjam`, jatuh tempo +7 hari)
   - `peminjaman_detail`
5. Sistem **kurangi stok** buku

### Pengembalian (oleh Staff)
1. Staff klik tombol **Kembalikan**
2. Sistem update status peminjaman menjadi `dikembalikan`
3. Sistem **tambah stok** buku sesuai qty detail

### Member melihat peminjaman
Member dapat melihat daftar peminjaman miliknya berdasarkan `peminjaman.id_member`.

---

## Cara Menjalankan Project (Local)

### 1) Install dependency
### 2) Buat file .env
### 3) Buat database & tabel (MySQL Workbench)
### 4) Jalankan aplikasi

---

## Unit Testing

<img width="719" height="439" alt="Image" src="https://github.com/user-attachments/assets/f6d81b34-36c9-452f-9896-b1e99e8d9a35" />

### Member Katalog Test

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class MemberKatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // ====== TABEL USERS ======
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('peran', 20)->default('member');
            $table->timestamps();
        });

        // ====== TABEL BUKU ======
        Schema::dropIfExists('buku');
        Schema::create('buku', function (Blueprint $table) {
            $table->id('id_buku');
            $table->string('judul', 150);
            $table->integer('stok');
        });
    }

    public function test_member_dapat_melihat_halaman_katalog()
    {
        // ====== DATA BUKU ======
        DB::table('buku')->insert([
            [
                'judul' => 'Laravel Dasar',
                'stok' => 5,
            ],
            [
                'judul' => 'PHP Lanjut',
                'stok' => 0,
            ],
        ]);

        // ====== SIMULASI LOGIN MEMBER ======
        $this->withSession([
            'user' => [
                'id' => 1,
                'nama' => 'Budi Member',
                'email' => 'member@demo.com',
                'peran' => 'member',
            ]
        ]);

        // ====== AKSES HALAMAN KATALOG ======
        $res = $this->get('/katalog');

        // ====== ASSERT ======
        $res->assertStatus(200);
        $res->assertSee('Katalog Buku');
        $res->assertSee('Laravel Dasar');
        $res->assertSee('PHP Lanjut');
    }

    public function test_user_tanpa_login_tidak_bisa_mengakses_katalog()
    {
        $res = $this->get('/katalog');

        $res->assertStatus(302);
        $res->assertRedirect('/masuk');
    }
}


### Login Test

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


### Staff Buku Test

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class StaffBukuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // ===== USERS =====
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('peran', 20);
            $table->timestamps();
        });

        // ===== BUKU =====
        Schema::dropIfExists('buku');
        Schema::create('buku', function (Blueprint $table) {
            $table->id('id_buku');
            $table->string('judul', 200);
            $table->integer('stok');
            $table->timestamps();
        });

        // ===== LOGIN STAFF =====
        $this->withSession([
            'user' => [
                'id' => 1,
                'nama' => 'Admin Staff',
                'email' => 'staff@demo.com',
                'peran' => 'staff',
            ]
        ]);
    }

    public function test_staff_dapat_melihat_daftar_buku()
    {
        DB::table('buku')->insert([
            ['judul' => 'Laravel Dasar', 'stok' => 5],
            ['judul' => 'PHP Lanjut', 'stok' => 3],
        ]);

        $res = $this->get('/staff/buku');

        $res->assertStatus(200);
        $res->assertSee('Laravel Dasar');
        $res->assertSee('PHP Lanjut');
    }

    public function test_staff_dapat_mencari_buku()
    {
        DB::table('buku')->insert([
            ['judul' => 'Laravel Dasar', 'stok' => 5],
            ['judul' => 'Java Spring', 'stok' => 2],
        ]);

        $res = $this->get('/staff/buku?q=Laravel');

        $res->assertStatus(200);
        $res->assertSee('Laravel Dasar');
        $res->assertDontSee('Java Spring');
    }

    public function test_form_tambah_buku_bisa_diakses()
    {
        $res = $this->get('/staff/buku/tambah');

        $res->assertStatus(200);
    }

    public function test_staff_dapat_menambah_buku()
    {
        $res = $this->post('/staff/buku/tambah', [
            'judul' => 'Buku Baru',
            'stok' => 10,
        ]);

        $res->assertStatus(302);
        $res->assertSessionHas('sukses');

        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Baru',
            'stok' => 10,
        ]);
    }

    public function test_form_edit_buku_bisa_diakses()
    {
        DB::table('buku')->insert([
            'id_buku' => 1,
            'judul' => 'Buku Lama',
            'stok' => 2,
        ]);

        $res = $this->get('/staff/buku/1/edit');

        $res->assertStatus(200);
        $res->assertSee('Buku Lama');
    }

    public function test_staff_dapat_update_buku()
    {
        DB::table('buku')->insert([
            'id_buku' => 1,
            'judul' => 'Buku Lama',
            'stok' => 2,
        ]);

        $res = $this->post('/staff/buku/1/edit', [
            'judul' => 'Buku Update',
            'stok' => 8,
        ]);

        $res->assertStatus(302);
        $res->assertSessionHas('sukses');

        $this->assertDatabaseHas('buku', [
            'id_buku' => 1,
            'judul' => 'Buku Update',
            'stok' => 8,
        ]);
    }

    public function test_staff_dapat_menghapus_buku()
    {
        DB::table('buku')->insert([
            'id_buku' => 1,
            'judul' => 'Buku Dihapus',
            'stok' => 1,
        ]);

        $res = $this->post('/staff/buku/1/hapus');

        $res->assertStatus(302);
        $res->assertSessionHas('sukses');

        $this->assertDatabaseMissing('buku', [
            'id_buku' => 1,
        ]);
    }
}


### Staff Pinjam Test

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class StaffPinjamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // seed data minimal
        DB::table('users')->insert([
            'id' => 1,
            'nama' => 'Staff Demo',
            'email' => 'staff@demo.com',
            'peran' => 'staff',
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'nama' => 'Member Demo',
            'email' => 'member@demo.com',
            'peran' => 'member',
        ]);

        DB::table('buku')->insert([
            'id_buku' => 1,
            'judul' => 'Buku Testing',
            'stok' => 5,
        ]);

        // login staff (session manual)
        session([
            'user' => (object)[
                'id' => 1,
                'nama' => 'Staff Demo',
                'peran' => 'staff',
            ]
        ]);
    }

    /** @test */
    public function staff_bisa_melihat_list_peminjaman()
    {
        $response = $this->get(route('staff.pinjam.index'));

        $response->assertStatus(200);
        $response->assertViewIs('staff.peminjaman_index');
    }

    /** @test */
    public function staff_bisa_membuat_peminjaman()
    {
        $response = $this->post(route('staff.pinjam.simpan'), [
            'id_member' => 2,
            'id_buku'   => 1,
            'qty'       => 1,
        ]);

        $response->assertRedirect(route('staff.pinjam.index'));

        $this->assertDatabaseHas('peminjaman', [
            'id_member' => 2,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('peminjaman_detail', [
            'id_buku' => 1,
            'qty' => 1,
        ]);

        $this->assertDatabaseHas('buku', [
            'id_buku' => 1,
            'stok' => 4, // stok berkurang
        ]);
    }

    /** @test */
    public function staff_bisa_mengembalikan_buku()
    {
        // buat peminjaman dulu
        $id = DB::table('peminjaman')->insertGetId([
            'id_member' => 2,
            'tanggal_pinjam' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'dipinjam',
        ]);

        DB::table('peminjaman_detail')->insert([
            'id_peminjaman' => $id,
            'id_buku' => 1,
            'qty' => 1,
        ]);

        DB::table('buku')->where('id_buku', 1)->update(['stok' => 4]);

        $response = $this->post(route('staff.pinjam.kembalikan', $id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $id,
            'status' => 'dikembalikan',
        ]);

        $this->assertDatabaseHas('buku', [
            'id_buku' => 1,
            'stok' => 5, // stok kembali
        ]);
    }
}
