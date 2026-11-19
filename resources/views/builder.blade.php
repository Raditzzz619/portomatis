<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $steps[$step] }} - Portomatis</title>
    <link rel="stylesheet" href="{{ asset('css/builder-flow.css') }}">
    <link rel="stylesheet" href="{{ asset('css/builder-refinements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/domicile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monochrome.css') }}?v={{ filemtime(public_path('css/monochrome.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}?v={{ filemtime(public_path('css/account.css')) }}">
    <script src="{{ asset('js/domicile.js') }}" defer></script>
    <script src="{{ asset('js/builder.js') }}?v={{ filemtime(public_path('js/builder.js')) }}" defer></script>
</head>
<body>
<a class="skip-link" href="#form-content">Langsung ke konten</a>
<header class="topbar">
    <a href="{{ url('/') }}" class="brand"><span class="brand-icon" aria-hidden="true">P</span>Portomatis</a>
    @include('partials.account-nav')
</header>
<div class="builder-shell">
    <main id="form-content" class="flow-main" data-current-step="{{ $step }}" tabindex="-1">
        <div class="page-heading">
            <span class="eyebrow" data-step-count>LANGKAH {{ array_search($step, array_keys($steps)) + 1 }} DARI {{ count($steps) }}</span>
            <h1 data-step-title tabindex="-1">{{ $steps[$step] }}</h1>
            <p data-step-description>{{ match($step) {
                'template' => 'Pilih tampilan yang paling mencerminkan diri Anda. Bisa diubah kapan saja.',
                'personal' => 'Mulai dengan perkenalan singkat. Bantu orang mengenal Anda dan karya Anda.',
                'experience' => 'Ceritakan perjalanan belajar dan pengalaman profesional Anda.',
                'projects' => 'Tunjukkan keahlian dan karya yang paling Anda banggakan.',
                default => 'Periksa hasilnya, unduh, atau publikasikan saat Anda sudah siap.'
            } }}</p>
        </div>
        @if(session('notice'))<div class="notice" role="status">{{ session('notice') }}</div>@endif
        @if($savedPortfolios->isNotEmpty())
        <details class="saved-drafts"><summary>Portofolio tersimpan ({{ $savedPortfolios->count() }})</summary><ul>@foreach($savedPortfolios as $saved)<li><a href="{{ route('builder.resume', $saved->id) }}">{{ $saved->content['personal']['name'] ?? 'Draft tanpa nama' }} · #{{ $saved->id }}</a><small>Diperbarui {{ $saved->updated_at->timezone(config('app.analytics_timezone'))->format('d/m/Y H:i') }}</small></li>@endforeach</ul></details>
        @endif
        <div class="step-progress" aria-label="Progres pengisian">
            <span data-progress-bar></span>
        </div>
        <div class="step-carousel">
            <button class="step-arrow" type="button" data-carousel-prev aria-label="Kembali ke langkah sebelumnya" disabled>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
            </button>
            <div class="step-panels" data-step-viewport>
                <div class="step-track" data-step-track>
                    @foreach($steps as $key => $label)
                        <section class="step-panel {{ $step === $key ? 'active' : '' }}" data-step-panel="{{ $key }}" data-step-label="{{ $label }}" aria-label="{{ $label }}">
                            @if($key === 'preview')
                                @if(!empty($draft['personal']['name']))
                                    @include('builder.preview')
                                @else
                                    <section class="form-card">
                                        <span class="eyebrow">PRATINJAU PORTOFOLIO</span>
                                        <h2>Pratinjau siap setelah info pribadi diisi.</h2>
                                        <p>Lengkapi nama dan profesi Anda untuk melihat, mengunduh, atau memublikasikan portofolio.</p>
                                        <button class="button primary" type="button" data-go-to-personal>Isi info pribadi &rarr;</button>
                                    </section>
                                @endif
                            @else
                                <form action="{{ route('builder.save', $key) }}" method="POST" enctype="multipart/form-data" class="builder-form" data-form-step="{{ $key }}">
                                    @csrf
                                    @if($step === $key && $errors->any())
                                        <div class="error-summary" role="alert" tabindex="-1"><strong>Periksa kembali data Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                                    @endif
                                    @include('builder.'.$key)
                                    <p class="save-status" aria-live="polite"></p>
                                </form>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>
            <button class="step-arrow" type="button" data-carousel-next aria-label="Lanjut ke langkah berikutnya">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </button>
        </div>
        <footer class="flow-footer">Portomatis &middot; Ruang untuk potensi Anda.</footer>
    </main>
</div>
</body>
</html>
