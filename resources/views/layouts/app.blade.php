<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $judul ?? 'Perpustakaan' }}</title>

  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">
        <div class="logo"></div>
        <strong>Perpustakaan</strong>

        @if(session('user'))
          <span class="badge">{{ session('user.nama') }} • {{ session('user.peran') }}</span>
        @else
          <span class="badge">Belum login</span>
        @endif
      </div>

      <div class="nav-actions">
        @if(session('user') && session('user.peran') === 'member')
          <a class="btn btn-sm" href="{{ route('member.katalog') }}">Katalog</a>
          <a class="btn btn-sm" href="{{ route('member.peminjaman') }}">Peminjaman Saya</a>
        @endif

        @if(session('user') && session('user.peran') === 'staff')
          <a class="btn btn-sm" href="{{ route('staff.buku.index') }}">Buku</a>
          <a class="btn btn-sm" href="{{ route('staff.pinjam.index') }}">Peminjaman</a>
        @endif

        @if(session('user'))
          <form method="POST" action="{{ route('keluar') }}">
            @csrf
            <button class="btn btn-sm danger" type="submit">Keluar</button>
          </form>
        @endif
      </div>
    </div>

    <div class="card">
      @if(session('sukses'))
        <div class="alert ok">{{ session('sukses') }}</div>
      @endif

      @if(session('gagal'))
        <div class="alert bad">{{ session('gagal') }}</div>
      @endif

      @yield('konten')
    </div>
  </div>
</body>
</html>