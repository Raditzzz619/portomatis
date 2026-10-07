<div class="template-grid">
    @foreach(['minimalist' => ['Minimalist', 'Bersih, sederhana, dan elegan.'], 'modern' => ['Modern', 'Ekspresif dengan aksen hangat.'], 'tech' => ['Tech', 'Tajam dengan nuansa gelap.']] as $key => [$name, $description])
        <label class="template-option">
            <input type="radio" name="template" value="{{ $key }}" @checked(old('template', $draft['template'] ?? 'minimalist') === $key) required>
            <span class="template-visual {{ $key }}" aria-hidden="true"><span class="mini-nav">YOUR NAME <span>ABOUT &nbsp; WORK</span></span><span class="mini-avatar">N</span><span class="mini-eyebrow">HELLO, I'M A CREATOR</span><span class="mini-title">Ide besar.<br>Karya bermakna.</span><span class="mini-description"></span><span class="mini-cta">Lihat karya &rarr;</span><span class="mini-projects"><i></i><i></i><i></i></span></span>
            <span class="template-label"><strong>{{ $name }}</strong><span>{{ $description }}</span></span>
        </label>
    @endforeach
</div>
<div class="help-card"><strong>Desain yang mengikuti cerita Anda.</strong><p>Semua template menampilkan info pribadi, pendidikan, pengalaman, keahlian, dan proyek Anda. Tampilannya menyesuaikan desktop maupun ponsel.</p></div>
