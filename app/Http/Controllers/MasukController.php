<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasukController extends Controller
{
    public function form()
    {
        return view('auth.masuk');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('gagal', 'Email / password salah.');
        }

        session([
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'peran' => $user->peran,
            ]
        ]);

        return $user->peran === 'staff'
            ? redirect()->route('staff.buku.index')
            : redirect()->route('member.katalog');
    }

    public function keluar()
    {
        session()->forget('user');
        return redirect('/masuk')->with('sukses', 'Berhasil logout.');
    }
}
