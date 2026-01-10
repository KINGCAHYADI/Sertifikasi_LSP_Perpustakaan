@extends('layouts.app', ['judul' => 'Peminjaman Saya'])

@section('konten')
<h1>Peminjaman Saya</h1>
<p class="small">Ini daftar buku yang kamu pinjam. Peminjaman dibuat oleh staff.</p>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Buku</th>
      <th>Tanggal Pinjam</th>
      <th>Jatuh Tempo</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse($data as $d)
      <tr>
        <td>#{{ $d->id_peminjaman }}</td>
        <td>{{ $d->daftar_buku }}</td>
        <td>{{ $d->tanggal_pinjam }}</td>
        <td>{{ $d->tanggal_jatuh_tempo }}</td>
        <td>
          @if($d->status === 'dipinjam')
            <span class="pill blue">dipinjam</span>
          @else
            <span class="pill green">dikembalikan</span>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="5">Belum ada peminjaman.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
