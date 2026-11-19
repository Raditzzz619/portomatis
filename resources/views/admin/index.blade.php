@extends('layouts.account')
@section('title', 'Dashboard admin')
@section('content')
<div class="admin-heading"><div><span class="account-eyebrow">RUANG ADMINISTRATOR</span><h1>Aktivitas & pengguna</h1><p>Memahami penggunaan Portomatis dan perkembangan portofolio pengguna.</p></div><a class="account-button secondary" href="{{ route('admin.index', request()->only('q')) }}">Perbarui data &circlearrowright;</a></div>
<div class="admin-summary"><span><strong>{{ $totals['users'] }}</strong> pengguna terdaftar</span><span><strong>{{ $totals['portfolios'] }}</strong> portofolio tersimpan</span><span><strong>{{ $totals['completed'] }}</strong> sudah mencapai pratinjau</span><span class="online-dot"><strong>{{ $online }}</strong> aktif dalam 5 menit terakhir</span></div>
<section aria-labelledby="stats-title"><h2 id="stats-title" class="section-title">Pengguna aktif unik</h2><div class="stats-grid">
    @foreach($stats as $label => $count)<article class="account-card stat"><span>{{ $label }}</span><strong>{{ $count }}</strong><small>pengguna</small></article>@endforeach
</div><p class="account-muted">Satu pengguna dihitung sekali di setiap periode kalender. Minggu dimulai Senin. Zona waktu {{ $timezone }}; akun admin tidak dihitung. Data dimulai sejak fitur aktivitas diaktifkan.</p></section>
<section class="account-card chart-card" aria-labelledby="chart-title"><h2 id="chart-title">Aktivitas 7 hari terakhir</h2><div class="daily-chart" role="list" aria-label="Jumlah pengguna aktif unik per hari">
    @foreach($daily as $day)<div class="chart-column" role="listitem" aria-label="{{ $day['date'] }}: {{ $day['count'] }} pengguna"><strong>{{ $day['count'] }}</strong><div class="chart-track"><span style="height:{{ $day['count'] ? max(4, $day['count'] / max(1, $daily->max('count')) * 100) : 0 }}%"></span></div><small>{{ $day['date'] }}</small></div>@endforeach
</div></section>
<section class="account-card users-card" aria-labelledby="users-title">
    <div class="section-heading"><div><span class="account-eyebrow">DIREKTORI PENGGUNA</span><h2 id="users-title">Akun & data portofolio</h2></div><span>{{ $users->total() }} pengguna{{ $search !== '' ? ' ditemukan' : '' }}</span></div>
    <form method="GET" action="{{ route('admin.index') }}" class="admin-search" role="search"><label for="user-search" class="sr-only">Cari nama, email akun, atau email portofolio</label><input id="user-search" name="q" value="{{ $search }}" placeholder="Cari nama, email akun, atau email portofolio…" maxlength="150"><button class="account-button" type="submit">Cari pengguna</button>@if($search !== '')<a href="{{ route('admin.index') }}">Reset</a>@endif</form>
    @error('q')<p class="account-errors">{{ $message }}</p>@enderror
    @if($users->isEmpty())<div class="empty-state" role="status"><span aria-hidden="true">&empty;</span><h3>{{ $search !== '' ? 'Pengguna yang dicari tidak ada.' : 'Belum ada pengguna terdaftar.' }}</h3><p>{{ $search !== '' ? 'Coba nama atau email lain untuk mencari pengguna.' : 'Akun pengguna akan muncul setelah registrasi.' }}</p></div>
    @else
    <div class="table-scroll"><table class="account-table"><thead><tr><th scope="col">Data login pengguna</th><th scope="col">Data setelah membuat portofolio</th><th scope="col">Aktivitas terakhir</th><th scope="col">Detail</th></tr></thead><tbody>
        @foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span><small>Daftar {{ $user->created_at->timezone($timezone)->format('d/m/Y') }}</small></td><td>
            @forelse($user->portfolios as $portfolio)<div class="portfolio-cell"><strong>{{ $portfolio->content['personal']['name'] ?? 'Draft tanpa nama' }}</strong><span>Email portofolio: {{ $portfolio->content['personal']['email'] ?? 'Belum diisi' }}</span><small>{{ $portfolio->completed_at ? 'Pratinjau tersedia' : 'Draft' }} · #{{ $portfolio->id }}</small></div>@empty<span>Belum membuat portofolio.</span>@endforelse
        </td><td>{{ $user->activities_max_created_at ? \Illuminate\Support\Carbon::parse($user->activities_max_created_at)->timezone($timezone)->format('d/m/Y H:i') : 'Belum ada aktivitas' }}</td><td><a class="table-link" href="{{ route('admin.users.show', $user) }}">Lihat data <span class="sr-only">{{ $user->name }}</span> &nearr;</a></td></tr>@endforeach
    </tbody></table></div>
    @if($users->hasPages())<nav class="simple-pagination" aria-label="Halaman pengguna">@if($users->previousPageUrl())<a href="{{ $users->previousPageUrl() }}">&larr; Sebelumnya</a>@endif<span>Halaman {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>@if($users->nextPageUrl())<a href="{{ $users->nextPageUrl() }}">Berikutnya &rarr;</a>@endif</nav>@endif
    @endif
</section>
<section class="account-card activity-card"><h2>Aktivitas terbaru</h2><p class="account-muted">Riwayat pendaftaran, penyimpanan, pratinjau, publikasi, dan unduhan seluruh pengguna.</p>
    @forelse($activities as $activity)<div class="activity-row"><div><a href="{{ route('admin.users.show', $activity->user) }}">{{ $activity->user->name }}</a><span>{{ \App\Models\UserActivity::LABELS[$activity->action] ?? $activity->action }}{{ $activity->step ? ' · '.(\App\Http\Controllers\BuilderController::STEPS[$activity->step] ?? $activity->step) : '' }}</span></div><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->timezone($timezone)->format('d/m/Y H:i') }}</time></div>@empty<p class="empty-state">Belum ada aktivitas pengguna.</p>@endforelse
</section>
@endsection
