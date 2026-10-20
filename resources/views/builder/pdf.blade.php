@php
    $personal = $draft['personal'] ?? [];
    $accents = ['web' => '#1d4ed8', 'android' => '#166534', 'graphic' => '#be123c', 'product' => '#6d28d9', 'photography' => '#292524', 'data' => '#1e40af', 'minimalist' => '#334155', 'modern' => '#9a3412', 'tech' => '#047857'];
    $accent = $accents[$draft['template'] ?? 'minimalist'] ?? $accents['minimalist'];
    $website = $safeUrl($personal['website'] ?? null);
    $linkedin = $safeUrl($personal['linkedin'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portofolio {{ $personal['name'] ?? '' }}</title>
    <style>
        @page { margin: 40pt 44pt 48pt; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11pt; line-height: 1.55; color: #1e293b; }
        * { box-sizing: border-box; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 25pt; line-height: 1.2; color: #0f172a; margin-bottom: 6pt; overflow-wrap: break-word; }
        h2 { font-size: 13pt; color: {{ $accent }}; margin: 22pt 0 10pt; padding-bottom: 5pt; border-bottom: 1pt solid #cbd5e1; page-break-after: avoid; }
        h3 { font-size: 11.5pt; color: #0f172a; page-break-after: avoid; }
        p { margin-bottom: 6pt; white-space: pre-line; overflow-wrap: break-word; }
        a { color: {{ $accent }}; text-decoration: underline; overflow-wrap: break-word; }
        .identity { border-bottom: 3pt solid {{ $accent }}; padding-bottom: 15pt; }
        .with-photo { min-height: 82pt; padding-right: 100pt; }
        .photo { float: right; width: 80pt; height: 80pt; object-fit: cover; border-radius: 6pt; }
        .role { font-size: 15pt; line-height: 1.4; color: {{ $accent }}; margin-bottom: 8pt; }
        .contact { font-size: 10pt; margin: 0; }
        .meta { color: #475569; font-size: 10pt; margin: 2pt 0 6pt; page-break-after: avoid; }
        .entry { margin-bottom: 14pt; }
        .reference { font-size: 9.5pt; color: #475569; margin-top: 4pt; }
        .skills { line-height: 1.9; }
        .skill { display: inline; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="identity">
        @if($photo)<img class="photo" src="{{ $photo }}" alt="Foto {{ $personal['name'] ?? '' }}">@endif
        <div @class(['with-photo' => $photo])>
            <h1>{{ $personal['name'] ?? '' }}</h1>
            <p class="role">{{ $personal['role'] ?? '' }}</p>
            @if(!empty($personal['location']))<p class="contact">Domisili: {{ $personal['location'] }}</p>@endif
            @if(!empty($personal['email']))<p class="contact">Email: {{ $personal['email'] }}</p>@endif
            @if(!empty($personal['phone']))<p class="contact">Telepon: {{ $personal['phone'] }}</p>@endif
        </div>
        <div class="clear"></div>
        @if($website)<p class="contact">Website: <a href="{{ $website }}">{{ $website }}</a></p>@endif
        @if($linkedin)<p class="contact">LinkedIn: <a href="{{ $linkedin }}">{{ $linkedin }}</a></p>@endif
    </div>

    @if(!empty($personal['bio']))
        <h2>Profil</h2>
        <p>{{ $personal['bio'] }}</p>
    @endif

    @if(!empty($draft['experience']))
        <h2>Pengalaman</h2>
        @foreach($draft['experience'] as $experience)
            <div class="entry">
                <h3>{{ $experience['role'] ?? '' }}@if(!empty($experience['role']) && !empty($experience['company'])) &middot; @endif{{ $experience['company'] ?? '' }}</h3>
                @if(!empty($experience['period']))<p class="meta">{{ $experience['period'] }}</p>@endif
                @if(!empty($experience['description']))<p>{{ $experience['description'] }}</p>@endif
            </div>
        @endforeach
    @endif

    @if(!empty($draft['education']))
        <h2>Pendidikan</h2>
        @foreach($draft['education'] as $education)
            <div class="entry">
                <h3>{{ $education['school'] ?? '' }}</h3>
                @if(!empty($education['degree']))<p>{{ $education['degree'] }}</p>@endif
                @if(!empty($education['period']))<p class="meta">{{ $education['period'] }}</p>@endif
            </div>
        @endforeach
    @endif

    @if(!empty($draft['skills']))
        <h2>Keahlian</h2>
        <p class="skills">{{ implode('  ·  ', $draft['skills']) }}</p>
    @endif

    @if(!empty($draft['projects']))
        <h2>Proyek pilihan</h2>
        @foreach($draft['projects'] as $project)
            <div class="entry">
                <h3>{{ $project['title'] ?? '' }}</h3>
                @if(!empty($project['description']))<p>{{ $project['description'] }}</p>@endif
                @php($projectUrl = $safeUrl($project['url'] ?? null))
                @if($projectUrl)<p class="reference">Referensi karya: <a href="{{ $projectUrl }}">{{ $projectUrl }}</a></p>@endif
            </div>
        @endforeach
    @endif
</body>
</html>
