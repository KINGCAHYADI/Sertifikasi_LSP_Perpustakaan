<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MemberPinjamController extends Controller
{
    public function index()
    {
        $id_member = (int) session('user.id');

        $data = DB::table('peminjaman as p')
            ->join('peminjaman_detail as d', 'p.id_peminjaman', '=', 'd.id_peminjaman')
            ->join('buku as b', 'd.id_buku', '=', 'b.id_buku')
            ->where('p.id_member', $id_member)
            ->select(
                'p.id_peminjaman',
                'p.tanggal_pinjam',
                'p.tanggal_jatuh_tempo',
                'p.status',
                DB::raw('GROUP_CONCAT(b.judul SEPARATOR ", ") as daftar_buku')
            )
            ->groupBy('p.id_peminjaman', 'p.tanggal_pinjam', 'p.tanggal_jatuh_tempo', 'p.status')
            ->orderByDesc('p.id_peminjaman')
            ->get();

        return view('member.peminjaman_saya', compact('data'));
    }
}
