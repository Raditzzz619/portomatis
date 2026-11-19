@extends('layouts.account')
@section('title', $register ? 'Daftar akun' : 'Masuk')
@section('content')
<div class="auth-layout">
    <section class="auth-intro">
        <span class="account-eyebrow">PORTOFOLIO ANDA, LANGKAH BERIKUTNYA</span>
        <h1>Karya Anda punya cerita.<br><span>Mulai tunjukkan.</span></h1>
        <p>Simpan perjalanan Anda, kenali kompetensi, dan buat portofolio yang siap membuka peluang.</p>
        <div class="auth-feature"><span>01</span><div><strong>Draft tersimpan di akun</strong><p>Lanjutkan dari perangkat lain kapan saja.</p></div></div>
        <div class="auth-feature"><span>02</span><div><strong>Kenali bidang yang sesuai</strong><p>Analisis dari pengalaman, keahlian, dan proyek Anda.</p></div></div>
        <div class="auth-feature"><span>03</span><div><strong>Perbaikan yang terarah</strong><p>Lihat penilaian portofolio dan saran sebelum mengunduh PDF.</p></div></div>
    </section>
    <section class="account-card auth-card">
        <span class="account-eyebrow">{{ $register ? 'MULAI PERJALANAN ANDA' : 'SELAMAT DATANG KEMBALI' }}</span>
        <h2>{{ $register ? 'Buat akun Portomatis' : 'Masuk ke akun Anda' }}</h2>
        @if($register)
            <p>Satu akun untuk menyimpan karya dan melihat potensi Anda.</p>
        @endif
        @if($errors->any())<div class="account-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route($register ? 'register.store' : 'login.store') }}" class="auth-form">
            @csrf
            @if($register)
                <label for="name">Nama akun</label>
                <input id="name" name="name" autocomplete="name" value="{{ old('name') }}" maxlength="100" required>
            @endif
            <label for="email">Email akun</label>
            <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" maxlength="255" required>
            <label for="password">Kata sandi</label>
            <div class="password-field">
                <input id="password" name="password" type="password" autocomplete="{{ $register ? 'new-password' : 'current-password' }}" @if($register) minlength="8" @endif maxlength="255" required>
                <button class="password-toggle" type="button" aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" hidden>
                    <svg class="password-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                        <circle cx="12" cy="12" r="3" />
                        <path class="password-eye-slash" d="m3 3 18 18" />
                    </svg>
                </button>
            </div>
            @if($register)
                <small>Gunakan minimal 8 karakter.</small>
                <label for="password_confirmation">Ulangi kata sandi</label>
                <div class="password-field">
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="255" required>
                    <button class="password-toggle" type="button" aria-controls="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" hidden>
                        <svg class="password-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                            <circle cx="12" cy="12" r="3" />
                            <path class="password-eye-slash" d="m3 3 18 18" />
                        </svg>
                    </button>
                </div>
                <label class="consent"><input name="consent" type="checkbox" value="1" @checked(old('consent')) required><span>Saya setuju data akun, aktivitas penggunaan, dan isi portofolio disimpan serta dapat dilihat admin untuk pengelolaan Portomatis. Isi portofolio menjadi publik hanya jika saya memublikasikannya.</span></label>
            @endif
            <button class="account-button" type="submit">{{ $register ? 'Buat akun' : 'Masuk' }} <span aria-hidden="true">&rarr;</span></button>
        </form>
        <p class="auth-switch">{{ $register ? 'Sudah punya akun?' : 'Belum punya akun?' }} <a href="{{ route($register ? 'login' : 'register') }}">{{ $register ? 'Masuk di sini' : 'Daftar sekarang' }}</a></p>
    </section>
</div>
<script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}" defer></script>
@endsection
