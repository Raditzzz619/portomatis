@extends('layouts.account')
@section('title', 'Data pengguna · '.$user->name)
@section('content')
@php($timezone = config('app.analytics_timezone'))
<a class="back-link" href="{{ route('admin.index') }}">&larr; Kembali ke dashboard</a>
<div class="admin-heading"><div><span class="account-eyebrow">DETAIL PENGGUNA</span><h1>{{ $user->name }}</h1><p>Data akun dan isi portofolio disajikan terpisah.</p></div></div>
<section class="account-card"><h2>Data login pengguna</h2><dl class="detail-grid"><div><dt>Nama akun</dt><dd>{{ $user->name }}</dd></div><div><dt>Email login</dt><dd>{{ $user->email }}</dd></div><div><dt>Tanggal registrasi</dt><dd>{{ $user->created_at->timezone($timezone)->format('d/m/Y H:i') }}</dd></div><div><dt>Jumlah portofolio</dt><dd>{{ $portfolios->count() }}</dd></div></dl></section>
<h2 class="section-title">Data pengguna setelah membuat portofolio</h2>
@forelse($portfolios as $portfolio)
@php($data = $portfolio->content)
@php($personal = $data['personal'] ?? [])
<section class="account-card portfolio-detail"><div class="section-heading"><div><span class="account-eyebrow">PORTOFOLIO #{{ $portfolio->id }}</span><h2>{{ $personal['name'] ?? 'Draft tanpa nama' }}</h2></div><span class="status-badge">{{ $portfolio->completed_at ? 'Sudah mencapai pratinjau' : 'Draft' }}</span></div>
<p class="account-muted">Diperbarui {{ $portfolio->updated_at->timezone($timezone)->format('d/m/Y H:i') }} · Template {{ \App\Http\Controllers\BuilderController::TEMPLATES[$data['template'] ?? 'minimalist']['name'] ?? 'Minimalist' }}</p>
@if(!empty($personal['photo']))<img class="admin-photo" src="{{ $personal['photo'] }}" alt="Foto portofolio {{ $personal['name'] ?? '' }}">@endif
<dl class="detail-grid">@foreach(['name' => 'Nama portofolio', 'role' => 'Profesi', 'email' => 'Email portofolio', 'phone' => 'Telepon', 'location' => 'Domisili', 'website' => 'Website', 'linkedin' => 'LinkedIn', 'bio' => 'Tentang diri'] as $field => $label)<div><dt>{{ $label }}</dt><dd>{{ $personal[$field] ?? 'Belum diisi' }}</dd></div>@endforeach</dl>
<h3>Keahlian</h3><div class="skill-tags">@forelse($data['skills'] ?? [] as $skill)<span>{{ $skill }}</span>@empty<p>Belum diisi.</p>@endforelse</div>
@foreach(['education' => 'Pendidikan', 'experience' => 'Pengalaman', 'projects' => 'Proyek'] as $group => $label)<h3>{{ $label }}</h3>
    @forelse($data[$group] ?? [] as $row)<dl class="detail-grid record-row">@foreach($row as $key => $value)<div><dt>{{ ['school' => 'Institusi', 'degree' => 'Program studi', 'period' => 'Periode', 'company' => 'Perusahaan / organisasi', 'role' => 'Posisi', 'description' => 'Deskripsi', 'title' => 'Judul proyek', 'url' => 'Tautan proyek'][$key] ?? $key }}</dt><dd>{{ $value ?: 'Belum diisi' }}</dd></div>@endforeach</dl>@empty<p class="account-muted">Belum diisi.</p>@endforelse
@endforeach
<div class="admin-assessment"><strong>Penilaian portofolio: {{ $reports[$portfolio->id]['assessment']['score'] }}% · {{ $reports[$portfolio->id]['assessment']['label'] }}</strong><p>Rekomendasi bidang: {{ collect($reports[$portfolio->id]['jobs'])->pluck('job')->join(', ') ?: 'Belum cukup data yang dikenali.' }}</p></div>
@if($portfolio->published_token)<a class="table-link" href="{{ route('builder.public', $portfolio->published_token) }}" target="_blank" rel="noopener">Buka portofolio publik &nearr;</a>@endif
</section>
@empty<div class="account-card empty-state">Pengguna ini belum membuat portofolio.</div>@endforelse
<section class="account-card activity-card"><h2>Riwayat aktivitas pengguna</h2><p class="account-muted">Waktu dalam {{ $timezone }}.</p>
@forelse($activities as $activity)<div class="activity-row"><span>{{ \App\Models\UserActivity::LABELS[$activity->action] ?? $activity->action }}{{ $activity->step ? ' · '.(\App\Http\Controllers\BuilderController::STEPS[$activity->step] ?? $activity->step) : '' }}</span><time>{{ $activity->created_at->timezone($timezone)->format('d/m/Y H:i:s') }}</time></div>@empty<p>Belum ada aktivitas.</p>@endforelse
@if($activities->hasPages())<nav class="simple-pagination" aria-label="Halaman aktivitas">@if($activities->previousPageUrl())<a href="{{ $activities->previousPageUrl() }}">&larr; Sebelumnya</a>@endif<span>Halaman {{ $activities->currentPage() }} / {{ $activities->lastPage() }}</span>@if($activities->nextPageUrl())<a href="{{ $activities->nextPageUrl() }}">Berikutnya &rarr;</a>@endif</nav>@endif
</section>
@endsection
