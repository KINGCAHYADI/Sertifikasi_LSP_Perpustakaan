@extends('layouts.app', ['judul' => 'List Peminjaman'])

@section('konten')
<h1>Staff • List Peminjaman</h1>
<p class="small">Staff membuat peminjaman dan dapat mengembalikan buku.</p>

<div class="toolbar">
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <a class="btn primary btn-sm" href="{{ route('staff.pinjam.tambah') }}">+ Buat Peminjaman</a>
    <a class="btn btn-sm" href="{{ route('staff.buku.index') }}">Kelola Buku</a>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Member</th>
      <th>Buku</th>
      <th>Pinjam</th>
      <th>Jatuh Tempo</th>
      <th>Status</th>
      <th style="width:160px">Aksi</th>
    </tr>
  </thead>

  <tbody>
    @forelse($data as $d)
      <tr>
        <td>#{{ $d->id_peminjaman }}</td>
        <td>
          <div>{{ $d->nama_member }}</div>
          <div class="small">{{ $d->email_member }}</div>
        </td>
        <td>{{ $d->judul }}</td>
        <td>{{ $d->tanggal_pinjam }}</td>
        <td>{{ $d->tanggal_jatuh_tempo }}</td>
        <td>
          @if($d->status === 'dipinjam')
            <span class="pill blue">dipinjam</span>
          @else
            <span class="pill green">dikembalikan</span>
          @endif
        </td>
        <td>
          @if($d->status === 'dipinjam')
            <form method="POST" action="{{ route('staff.pinjam.kembalikan', $d->id_peminjaman) }}">
              @csrf
              <button class="btn btn-sm" type="submit">Kembalikan</button>
            </form>
          @else
            <span class="small">—</span>
          @endif
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7">Belum ada data peminjaman.</td>
      </tr>
    @endforelse
  </tbody>
</table>
@endsection