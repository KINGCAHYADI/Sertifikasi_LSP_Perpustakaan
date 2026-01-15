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
