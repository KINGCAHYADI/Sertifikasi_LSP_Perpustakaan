@extends('layouts.app', ['judul' => 'Staff - Katalog'])

@section('konten')
<h1>Staff • Katalog Buku</h1>

<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
  <a class="btn primary" href="{{ route('staff.buku.tambah') }}">+ Tambah Buku</a>

  <form method="GET" action="{{ route('staff.buku.index') }}" style="display:flex;gap:8px;align-items:center;">
    <input name="q" placeholder="Cari judul..." value="{{ $q }}">
    <button class="btn" type="submit">Cari</button>
    <a class="btn" href="{{ route('staff.buku.index') }}">Reset</a>
  </form>
</div>

<table>
  <thead>
    <tr>
      <th>Judul</th>
      <th>Stok</th>
      <th style="width:260px">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse($buku as $b)
      <tr>
        <td>{{ $b->judul }}</td>
        <td>{{ $b->stok }}</td>
        <td style="display:flex;gap:8px;flex-wrap:wrap;">
          <a class="btn" href="{{ route('staff.buku.edit', $b->id_buku) }}">Edit</a>

          <form method="POST" action="{{ route('staff.buku.hapus', $b->id_buku) }}"
                onsubmit="return confirm('Hapus buku ini? (Kalau pernah dipinjam, akan ditolak)')">
            @csrf
            <button class="btn danger" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="3">Tidak ada data.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
