<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffBukuController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $buku = DB::table('buku')
            ->when($q !== '', fn($qr) => $qr->where('judul', 'like', "%{$q}%"))
            ->orderBy('judul')
            ->get();

        return view('staff.buku_index', compact('buku', 'q'));
    }

    public function tambah()
    {
        return view('staff.buku_form', ['mode' => 'tambah', 'buku' => null]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'judul' => ['required', 'max:200'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('buku')->insert([
            'judul' => $request->judul,
            'stok' => (int)$request->stok,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('staff.buku.index')->with('sukses', 'Buku ditambahkan.');
    }

    public function edit(int $id_buku)
    {
        $buku = DB::table('buku')->where('id_buku', $id_buku)->first();
        abort_if(!$buku, 404);

        return view('staff.buku_form', ['mode' => 'edit', 'buku' => $buku]);
    }

    public function update(Request $request, int $id_buku)
    {
        $request->validate([
            'judul' => ['required', 'max:200'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('buku')->where('id_buku', $id_buku)->update([
            'judul' => $request->judul,
            'stok' => (int)$request->stok,
            'updated_at' => now(),
        ]);

        return redirect()->route('staff.buku.index')->with('sukses', 'Buku diperbarui.');
    }

    public function hapus(int $id_buku)
    {
        try {
            DB::table('buku')->where('id_buku', $id_buku)->delete();
            return back()->with('sukses', 'Buku dihapus.');
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Tidak bisa hapus: buku pernah dipinjam.');
        }
    }
}
