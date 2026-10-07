<div class="template-grid">
    @foreach(\App\Http\Controllers\BuilderController::TEMPLATES as $key => $template)
        <label class="template-option">
            <input type="radio" name="template" value="{{ $key }}" @checked(old('template', $draft['template'] ?? 'minimalist') === $key) required>
            <span class="template-visual {{ $key }}" aria-hidden="true"><span class="mini-nav">YOUR NAME <span>ABOUT &nbsp; WORK</span></span><span class="mini-avatar">N</span><span class="mini-eyebrow">{{ $template['name'] }}</span><span class="mini-title">{{ $template['headline'] }}</span><span class="mini-description"></span><span class="mini-cta">Lihat karya &rarr;</span><span class="mini-projects"><i></i><i></i><i></i></span></span>
            <span class="template-label"><span class="template-category">{{ $template['category'] }}</span><strong>{{ $template['name'] }}</strong><span>{{ $template['description'] }}</span></span>
        </label>
    @endforeach
</div>
<div class="help-card"><strong>Pilih gaya yang cocok dengan bidang Anda.</strong><p>Rekomendasi bidang membantu Anda memilih, tetapi semua template bebas digunakan untuk profesi apa pun. Data tetap tersimpan saat Anda mengganti template. Semua tampilan menyesuaikan desktop maupun ponsel.</p></div>
