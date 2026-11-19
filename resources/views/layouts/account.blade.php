<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Akun') · Portomatis</title>
    <link rel="stylesheet" href="{{ asset('css/account.css') }}?v={{ filemtime(public_path('css/account.css')) }}">
</head>
<body class="account-page">
<a class="skip-link" href="#account-content">Langsung ke konten</a>
<header class="account-header">
    <a class="account-brand" href="{{ route('home') }}"><span>P</span> Portomatis</a>
    @include('partials.account-nav')
</header>
<main id="account-content" class="account-main" tabindex="-1">
    @if(session('notice'))<div class="account-notice" role="status">{{ session('notice') }}</div>@endif
    @yield('content')
</main>
<footer class="account-footer">Portomatis · Ruang untuk potensi Anda.</footer>
</body>
</html>
