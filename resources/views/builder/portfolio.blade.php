<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $draft['personal']['name'] }} - {{ $draft['personal']['role'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($draft['personal']['bio'] ?? '', 160) }}">
    <style>{!! file_get_contents(public_path('css/portfolio.css')) !!}</style>
</head>
<body class="theme-{{ $draft['template'] ?? 'minimalist' }}">
@php($person = $draft['personal'])
<div class="portfolio-container">
    <header class="portfolio-header"><a class="wordmark" href="#about">{{ $person['name'] }}<span>.</span></a><nav aria-label="Navigasi portofolio"><a href="#about">Tentang</a>@if(!empty($draft['projects']))<a href="#projects">Proyek</a>@endif<a href="#contact">Kontak</a></nav></header>
    <main>
        <section class="portfolio-hero" id="about"><span class="avatar" aria-hidden="true">{{ mb_substr($person['name'], 0, 1) }}</span><p class="eyebrow">HALO, SAYA {{ $person['name'] }}</p><h1>{{ $person['role'] }}</h1>@if(!empty($person['bio']))<p class="bio">{{ $person['bio'] }}</p>@endif
        @if(!empty($person['location']))<p class="location">{{ $person['location'] }}</p>@endif
        <a class="portfolio-button" href="{{ empty($draft['projects']) ? '#contact' : '#projects' }}">{{ empty($draft['projects']) ? 'Hubungi saya' : 'Lihat karya saya' }} &nearr;</a></section>
        @if(!empty($draft['projects']))
        <section id="projects" class="portfolio-section"><div class="section-title"><h2>Proyek pilihan</h2><span>{{ count($draft['projects']) }} karya</span></div><div class="project-grid">@foreach($draft['projects'] as $project)<article class="project-card"><span class="project-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $project['title'] }}</h3>@if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif @if(!empty($project['url']))<a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">Lihat proyek &nearr;</a>@endif</article>@endforeach</div></section>
        @endif
        @if(!empty($draft['experience']))
        <section class="portfolio-section"><h2>Pengalaman</h2><div class="timeline">@foreach($draft['experience'] as $experience)<article><span class="period">{{ $experience['period'] ?? '' }}</span><div><h3>{{ ($experience['role'] ?? '') ?: $experience['company'] }}</h3><strong>{{ $experience['company'] }}</strong>@if(!empty($experience['description']))<p>{{ $experience['description'] }}</p>@endif</div></article>@endforeach</div></section>
        @endif
        @if(!empty($draft['education']))
        <section class="portfolio-section"><h2>Pendidikan</h2><div class="timeline">@foreach($draft['education'] as $education)<article><span class="period">{{ $education['period'] ?? '' }}</span><div><h3>{{ $education['school'] }}</h3>@if(!empty($education['degree']))<p>{{ $education['degree'] }}</p>@endif</div></article>@endforeach</div></section>
        @endif
        @if(!empty($draft['skills']))<section class="portfolio-section"><h2>Keahlian</h2><div class="skill-tags">@foreach($draft['skills'] as $skill)<span>{{ $skill }}</span>@endforeach</div></section>@endif
        <section class="portfolio-section contact" id="contact"><p class="eyebrow">MARI TERHUBUNG</p><h2>Punya ide untuk<br>dikembangkan bersama?</h2><div class="contact-links">@if(!empty($person['email']))<a href="mailto:{{ $person['email'] }}">{{ $person['email'] }} &nearr;</a>@endif @if(!empty($person['phone']))<span>{{ $person['phone'] }}</span>@endif @foreach(['website' => 'Website', 'linkedin' => 'LinkedIn'] as $key => $label)@if(!empty($person[$key]))<a href="{{ $person[$key] }}" target="_blank" rel="noopener noreferrer">{{ $label }} &nearr;</a>@endif @endforeach @if(empty($person['email']) && empty($person['phone']) && empty($person['website']) && empty($person['linkedin']))<p>Informasi kontak belum ditambahkan.</p>@endif</div></section>
    </main>
    <footer class="portfolio-footer"><span>{{ $person['name'] }}</span><span>Dibuat dengan Portomatis</span></footer>
</div>
</body>
</html>
