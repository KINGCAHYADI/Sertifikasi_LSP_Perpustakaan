<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasukController;
use App\Http\Controllers\MemberKatalogController;
use App\Http\Controllers\StaffBukuController;
use App\Http\Controllers\StaffPinjamController;
use App\Http\Controllers\MemberPinjamController;

Route::get('/', fn() => redirect('/masuk'));

Route::get('/masuk', [MasukController::class, 'form'])->name('masuk');
Route::post('/masuk', [MasukController::class, 'proses'])->name('masuk.proses');
Route::post('/keluar', [MasukController::class, 'keluar'])->name('keluar');

Route::middleware(['wajib_login', 'wajib_peran:member'])->group(function () {
    Route::get('/katalog', [MemberKatalogController::class, 'index'])->name('member.katalog');
    Route::get('/peminjaman-saya', [MemberPinjamController::class, 'index'])->name('member.peminjaman');
});

Route::middleware(['wajib_login', 'wajib_peran:staff'])->prefix('staff')->group(function () {

    Route::get('/buku', [StaffBukuController::class, 'index'])->name('staff.buku.index');
    Route::get('/buku/tambah', [StaffBukuController::class, 'tambah'])->name('staff.buku.tambah');
    Route::post('/buku/tambah', [StaffBukuController::class, 'simpan'])->name('staff.buku.simpan');

    Route::get('/buku/{id_buku}/edit', [StaffBukuController::class, 'edit'])->name('staff.buku.edit');
    Route::post('/buku/{id_buku}/edit', [StaffBukuController::class, 'update'])->name('staff.buku.update');

    Route::post('/buku/{id_buku}/hapus', [StaffBukuController::class, 'hapus'])->name('staff.buku.hapus');

    Route::get('/peminjaman', [StaffPinjamController::class, 'index'])->name('staff.pinjam.index');

    Route::get('/peminjaman/tambah', [StaffPinjamController::class, 'tambah'])->name('staff.pinjam.tambah');
    Route::post('/peminjaman/tambah', [StaffPinjamController::class, 'simpan'])->name('staff.pinjam.simpan');

    Route::post('/peminjaman/{id_peminjaman}/kembalikan', [StaffPinjamController::class, 'kembalikan'])->name('staff.pinjam.kembalikan');
});