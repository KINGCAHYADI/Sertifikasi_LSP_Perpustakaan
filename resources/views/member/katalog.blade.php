@extends('layouts.app', ['judul' => 'Katalog'])

@section('konten')
<h1>Katalog Buku</h1>
<p class="small">Member hanya dapat melihat katalog. Peminjaman dilakukan oleh staff.</p>

<table>
  <thead>
    <tr>
      <th>Judul</th>
      <th>Stok</th>
    </tr>
  </thead>
  <tbody>
    @forelse($buku as $b)
      <tr>
        <td>{{ $b->judul }}</td>
        <td>
          @if($b->stok > 0)
            <span class="pill green">tersedia ({{ $b->stok }})</span>
          @else
            <span class="pill red">habis</span>
          @endif
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="2">Tidak ada data.</td>
      </tr>
    @endforelse
  </tbody>
</table>
@endsection
