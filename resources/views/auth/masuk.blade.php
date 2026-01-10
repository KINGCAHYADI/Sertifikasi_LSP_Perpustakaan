@extends('layouts.app', ['judul' => 'Masuk'])

@section('konten')
<h1>Masuk</h1>
<p class="small">Gunakan akun demo yang ada di tabel <span class="kbd">users</span>.</p>

<hr class="sep">

<form method="POST" action="{{ route('masuk.proses') }}">
  @csrf

  <div class="form-grid">
    <div>
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="staff@demo.com" required>
      @error('email') <div class="small" style="color:var(--danger)">{{ $message }}</div> @enderror
    </div>

    <div>
      <label>Password</label>
      <input type="password" name="password" placeholder="password123" required>
      @error('password') <div class="small" style="color:var(--danger)">{{ $message }}</div> @enderror
    </div>
  </div>

  <div style="margin-top:12px">
    <button class="btn primary w-100" type="submit">Masuk</button>
  </div>
</form>
@endsection
