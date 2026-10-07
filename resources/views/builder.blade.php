<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $steps[$step] }} - Portomatis</title>
    <link rel="stylesheet" href="{{ asset('css/builder-flow.css') }}">
    <script src="{{ asset('js/builder.js') }}" defer></script>
</head>
<body>
<a class="skip-link" href="#form-content">Langsung ke konten</a>
<header class="topbar">
    <a href="{{ url('/') }}" class="brand"><span class="brand-icon" aria-hidden="true">P</span>Portomatis</a>
    <a class="home-link" href="{{ url('/') }}">Kembali ke beranda <span aria-hidden="true">&nearr;</span></a>
</header>
<div class="builder-shell">
    <aside class="sidebar" aria-label="Tahapan pembuatan portofolio">
        <span class="eyebrow">PORTOFOLIO ANDA</span>
        <h2>Dari cerita Anda,<br>menjadi karya.</h2>
        <nav class="step-list">
            @foreach ($steps as $key => $label)
                <a href="{{ route('builder.step', $key) }}" class="step-link {{ $step === $key ? 'active' : '' }}" @if($step === $key) aria-current="step" @endif>
                    <span class="step-number">{{ $loop->iteration }}</span><span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="draft-note"><strong>Satu langkah lebih dekat.</strong><p>Data disimpan saat Anda menekan Simpan & lanjut. Draft tersedia selama sesi browser ini.</p></div>
        <a class="sidebar-home" href="{{ route('builder.step', 'preview') }}">Lihat pratinjau &rarr;</a>
    </aside>
    <main id="form-content" class="flow-main">
        <div class="page-heading"><span class="eyebrow">LANGKAH {{ array_search($step, array_keys($steps)) + 1 }} DARI 5</span><h1>{{ $steps[$step] }}</h1>
            <p>{{ match($step) {
                'template' => 'Pilih tampilan yang paling mencerminkan diri Anda. Bisa diubah kapan saja.',
                'personal' => 'Mulai dengan perkenalan singkat. Bantu orang mengenal Anda dan karya Anda.',
                'experience' => 'Ceritakan perjalanan belajar dan pengalaman profesional Anda.',
                'projects' => 'Tunjukkan keahlian dan karya yang paling Anda banggakan.',
                default => 'Periksa hasilnya, unduh, atau publikasikan saat Anda sudah siap.'
            } }}</p>
        </div>
        @if(session('notice'))<div class="notice" role="status">{{ session('notice') }}</div>@endif
        @if($errors->any())
            <div class="error-summary" role="alert" tabindex="-1"><strong>Periksa kembali data Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if($step !== 'preview')
            <form action="{{ route('builder.save', $step) }}" method="POST" enctype="multipart/form-data" class="builder-form">
                @csrf
                @include('builder.'.$step)
                <div class="form-actions">
                    @if($step !== 'template')<a class="button secondary" href="{{ route('builder.step', array_keys($steps)[array_search($step, array_keys($steps)) - 1]) }}">&larr; Kembali</a>@else<span class="form-hint">Tanpa coding. Tanpa ribet.</span>@endif
                    <button class="button primary" type="submit">Simpan & lanjut <span aria-hidden="true">&rarr;</span></button>
                </div>
                <p class="save-status" aria-live="polite"></p>
            </form>
        @else
            @include('builder.preview')
        @endif
        <footer class="flow-footer">Portomatis &middot; Ruang untuk potensi Anda.</footer>
    </main>
</div>
</body>
</html>
