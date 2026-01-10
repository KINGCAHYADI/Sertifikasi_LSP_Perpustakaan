<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberKatalogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $buku = DB::table('buku')
            ->when($q !== '', fn($qr) => $qr->where('judul', 'like', "%{$q}%"))
            ->orderBy('judul')
            ->get();

        return view('member.katalog', compact('buku', 'q'));
    }
}
