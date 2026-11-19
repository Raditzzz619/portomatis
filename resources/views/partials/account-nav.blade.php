<nav class="account-nav" aria-label="Navigasi akun">
    @auth
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.index') : route('builder') }}">{{ auth()->user()->role === 'admin' ? 'Dashboard admin' : 'Portofolio saya' }}</a>
        <span class="account-name">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="account-logout">Keluar</button></form>
        @if(auth()->user()->role === 'user')
            <script src="{{ asset('js/activity.js') }}" defer data-heartbeat="{{ route('activity.heartbeat') }}" data-token="{{ csrf_token() }}"></script>
        @endif
    @else
        <a href="{{ route('login') }}">Masuk</a>
        <a href="{{ route('register') }}">Daftar</a>
    @endauth
</nav>
