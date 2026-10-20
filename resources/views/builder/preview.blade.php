<div class="preview-actions"><a class="button secondary" href="{{ route('builder.step', 'template') }}">Ganti template</a><a class="button secondary" href="{{ route('builder.step', 'personal') }}">Edit data</a><a class="button secondary" href="{{ route('builder.download') }}">Unduh HTML <span aria-hidden="true">&darr;</span></a><a class="button primary" href="{{ route('builder.download.pdf') }}">Unduh PDF <span aria-hidden="true">&darr;</span></a></div>
<div class="preview-tabs" role="tablist" aria-label="Format pratinjau portofolio">
    <button class="preview-tab" type="button" role="tab" id="tab-website" aria-controls="preview-website" aria-selected="true" data-preview-tab="website">Website</button>
    <button class="preview-tab" type="button" role="tab" id="tab-pdf" aria-controls="preview-pdf" aria-selected="false" tabindex="-1" data-preview-tab="pdf">PDF · A4</button>
</div>
<section id="preview-website" role="tabpanel" aria-labelledby="tab-website" tabindex="0" data-preview-panel="website">
    <p class="format-note">Tampilan interaktif dengan navigasi dan tombol menuju proyek, website, serta LinkedIn Anda.</p>
    <div class="preview-frame"><div class="browser-bar"><span class="browser-dots" aria-hidden="true"><i></i><i></i><i></i></span><span>Portofolio {{ $draft['personal']['name'] }} &middot; {{ \App\Http\Controllers\BuilderController::TEMPLATES[$draft['template'] ?? 'minimalist']['name'] ?? 'Minimalist' }}</span></div><iframe title="Pratinjau website portofolio {{ $draft['personal']['name'] }}" srcdoc="{{ view('builder.portfolio', ['draft' => $draft])->render() }}" sandbox="allow-popups allow-popups-to-escape-sandbox"></iframe></div>
</section>
<section id="preview-pdf" role="tabpanel" aria-labelledby="tab-pdf" tabindex="0" data-preview-panel="pdf" hidden>
    <p class="format-note">Dokumen A4 dengan teks yang mudah dibaca, ringkasan karya, dan alamat tautan tertulis. Tautan tetap dapat diklik pada pembaca PDF yang mendukungnya.</p>
    <div class="pdf-fallback"><span>Jika pratinjau PDF tidak muncul di browser Anda,</span> <a href="{{ route('builder.pdf') }}" target="_blank" rel="noopener">buka PDF di tab baru &nearr;</a> atau <a href="{{ route('builder.download.pdf') }}">unduh dokumennya</a>.</div>
    <div class="preview-frame pdf-frame"><div class="browser-bar">Portofolio {{ $draft['personal']['name'] }} · Dokumen PDF A4</div><iframe title="Pratinjau PDF portofolio {{ $draft['personal']['name'] }}" data-pdf-src="{{ route('builder.pdf') }}" loading="lazy"></iframe></div>
</section>
<noscript><p class="form-hint">Lihat versi PDF melalui <a href="{{ route('builder.pdf') }}" target="_blank" rel="noopener">pratinjau PDF</a> atau tombol Unduh PDF.</p></noscript>
<section class="form-card publish-card"><div><span class="eyebrow">SIAP DIBAGIKAN?</span><h2>Beri karya Anda ruang untuk dikenal.</h2><p>Publikasikan untuk membuat tautan yang dapat dibuka siapa saja. Perubahan draft baru terlihat di tautan publik setelah Anda mempublikasikan ulang.</p></div>
    <form method="POST" action="{{ route('builder.publish') }}">@csrf<button class="button primary" type="submit">{{ $shareUrl ? 'Publikasikan ulang' : 'Publikasikan portofolio' }} &nearr;</button></form>
    @if($shareUrl)<div class="share-box"><label for="share-url">Tautan publik Anda</label><div><input id="share-url" readonly value="{{ $shareUrl }}"><button class="button secondary" type="button" data-copy>Salin tautan</button></div><a href="{{ $shareUrl }}" target="_blank" rel="noopener">Buka portofolio &nearr;</a><span data-copy-status role="status"></span></div>@endif
</section>
<p class="form-hint">HTML adalah website mandiri yang dapat dibuka di browser atau diunggah ke hosting. PDF siap dikirim sebagai lampiran lamaran atau dicetak. Keduanya memakai data draft yang sama.</p>
<form method="POST" action="{{ route('builder.new') }}">@csrf
    <p class="form-hint">Ingin membuat portofolio untuk data yang berbeda? Mulai baru akan mengosongkan draft ini. Portofolio yang sudah dipublikasikan tetap tersedia.</p>
    <button class="button secondary" type="submit">Buat portofolio baru</button>
</form>
