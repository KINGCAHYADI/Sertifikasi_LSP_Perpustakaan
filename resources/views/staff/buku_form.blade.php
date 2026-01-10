@extends('layouts.app', ['judul' => 'Staff - Form Buku'])

@section('konten')
<h1>Staff • {{ $mode === 'tambah' ? 'Tambah Buku' : 'Edit Buku' }}</h1>

<form method="POST" action="{{ $mode === 'tambah'
    ? route('staff.buku.simpan')
    : route('staff.buku.update', $buku->id_buku) }}">
  @csrf

  <div class="row">
    <div>
      <label>Judul</label><br>
      <input name="judul" value="{{ old('judul', $buku->judul ?? '') }}" required>
      @error('judul') <div class="small" style="color:var(--danger)">{{ $message }}</div> @enderror
    </div>

    <div>
      <label>Stok</label><br>
      <input type="number" min="0" name="stok" value="{{ old('stok', $buku->stok ?? 0) }}" required>
      @error('stok') <div class="small" style="color:var(--danger)">{{ $message }}</div> @enderror
    </div>
  </div>

  <div style="margin-top:12px;display:flex;gap:8px;">
    <button class="btn primary" type="submit">Simpan</button>
    <a class="btn" href="{{ route('staff.buku.index') }}">Kembali</a>
  </div>
</form>
@endsection
