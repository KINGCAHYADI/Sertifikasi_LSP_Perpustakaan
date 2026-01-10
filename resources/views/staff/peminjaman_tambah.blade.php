@extends('layouts.app', ['judul' => 'Buat Peminjaman'])

@section('konten')
<h1>Staff • Buat Peminjaman</h1>
<p class="small">Pilih member & buku. Jatuh tempo otomatis 7 hari.</p>

@if($errors->any())
  <div class="alert bad">
    Ada input yang belum benar. Silakan cek kembali.
  </div>
@endif

<form method="POST" action="{{ route('staff.pinjam.simpan') }}">
  @csrf

  <div class="form-grid">
    <div>
      <label>Member</label>
      <select name="id_member" required style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;">
        <option value="">- Pilih Member -</option>
        @foreach($member as $m)
          <option value="{{ $m->id }}" @selected(old('id_member') == $m->id)>
            {{ $m->nama }} ({{ $m->email }})
          </option>
        @endforeach
      </select>
      @error('id_member') <div class="small" style="color:#dc2626">{{ $message }}</div> @enderror
    </div>

    <div>
      <label>Buku</label>
      <select name="id_buku" required style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;">
        <option value="">- Pilih Buku -</option>
        @foreach($buku as $b)
          <option value="{{ $b->id_buku }}" @selected(old('id_buku') == $b->id_buku)>
            {{ $b->judul }} (stok: {{ $b->stok }})
          </option>
        @endforeach
      </select>
      @error('id_buku') <div class="small" style="color:#dc2626">{{ $message }}</div> @enderror
    </div>
  </div>

  <div style="margin-top:12px;max-width:220px;">
    <label>Qty</label>
    <input type="number" name="qty" min="1" value="{{ old('qty', 1) }}" required>
    @error('qty') <div class="small" style="color:#dc2626">{{ $message }}</div> @enderror
    <div class="small">Default 1. Tidak boleh melebihi stok.</div>
  </div>

  <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
    <button class="btn primary" type="submit">Simpan</button>
    <a class="btn" href="{{ route('staff.pinjam.index') }}">Batal</a>
  </div>
</form>
@endsection