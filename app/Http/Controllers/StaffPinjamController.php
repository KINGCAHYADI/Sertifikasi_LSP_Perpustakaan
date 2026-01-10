<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPinjamController extends Controller
{
    public function index()
    {
        $data = DB::table('peminjaman')
            ->join('users', 'peminjaman.id_member', '=', 'users.id')
            ->join('peminjaman_detail', 'peminjaman.id_peminjaman', '=', 'peminjaman_detail.id_peminjaman')
            ->join('buku', 'peminjaman_detail.id_buku', '=', 'buku.id_buku')
            ->select(
                'peminjaman.id_peminjaman',
                'users.nama as nama_member',
                'users.email as email_member',
                'buku.judul',
                'peminjaman.tanggal_pinjam',
                'peminjaman.tanggal_jatuh_tempo',
                'peminjaman.status'
            )
            ->orderByDesc('peminjaman.id_peminjaman')
            ->get();

        return view('staff.peminjaman_index', compact('data'));
    }

    // FORM TAMBAH PEMINJAMAN (staff pilih member + buku)
    public function tambah()
    {
        $member = DB::table('users')
            ->where('peran', 'member')
            ->orderBy('nama')
            ->get();

        $buku = DB::table('buku')
            ->orderBy('judul')
            ->get();

        return view('staff.peminjaman_tambah', compact('member', 'buku'));
    }

    // SIMPAN PEMINJAMAN (staff yang buat)
    public function simpan(Request $request)
    {
        $request->validate([
            'id_member' => ['required', 'integer'],
            'id_buku'   => ['required', 'integer'],
            'qty'       => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = (int)($request->qty ?? 1);
        $tgl_pinjam = date('Y-m-d');
        $tgl_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));

        DB::transaction(function () use ($request, $qty, $tgl_pinjam, $tgl_jatuh_tempo) {

            // pastikan member valid
            $m = DB::table('users')
                ->where('id', $request->id_member)
                ->where('peran', 'member')
                ->first();

            if (!$m) {
                abort(422, 'Member tidak valid.');
            }

            // cek buku + lock stok
            $b = DB::table('buku')
                ->where('id_buku', $request->id_buku)
                ->lockForUpdate()
                ->first();

            if (!$b) {
                abort(422, 'Buku tidak ditemukan.');
            }

            if ((int)$b->stok < $qty) {
                abort(422, 'Stok tidak cukup.');
            }

            // insert header
            $id_peminjaman = DB::table('peminjaman')->insertGetId([
                'id_member' => $request->id_member,
                'tanggal_pinjam' => $tgl_pinjam,
                'tanggal_jatuh_tempo' => $tgl_jatuh_tempo,
                'status' => 'dipinjam',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // insert detail (simple: 1 buku per transaksi)
            DB::table('peminjaman_detail')->insert([
                'id_peminjaman' => $id_peminjaman,
                'id_buku' => $request->id_buku,
                'qty' => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // kurangi stok
            DB::table('buku')
                ->where('id_buku', $request->id_buku)
                ->decrement('stok', $qty);

            DB::table('buku')
                ->where('id_buku', $request->id_buku)
                ->update(['updated_at' => now()]);
        });

        return redirect()->route('staff.pinjam.index')->with('sukses', 'Peminjaman berhasil dibuat.');
    }

    public function kembalikan(int $id_peminjaman)
    {
        DB::transaction(function () use ($id_peminjaman) {
            $pinjam = DB::table('peminjaman')
                ->where('id_peminjaman', $id_peminjaman)
                ->lockForUpdate()
                ->first();

            if (!$pinjam || $pinjam->status === 'dikembalikan') return;

            $detail = DB::table('peminjaman_detail')->where('id_peminjaman', $id_peminjaman)->get();

            foreach ($detail as $d) {
                DB::table('buku')->where('id_buku', $d->id_buku)->increment('stok', (int)$d->qty);
                DB::table('buku')->where('id_buku', $d->id_buku)->update(['updated_at' => now()]);
            }

            DB::table('peminjaman')->where('id_peminjaman', $id_peminjaman)->update([
                'status' => 'dikembalikan',
                'updated_at' => now(),
            ]);
        });

        return back()->with('sukses', 'Peminjaman dikembalikan, stok bertambah.');
    }
}