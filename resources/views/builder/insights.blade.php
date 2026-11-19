@php($assessment = $insights['assessment'])
<section class="insights-section" aria-labelledby="insights-title">
    <span class="eyebrow">KENALI POTENSI ANDA</span>
    <h2 id="insights-title">Analisis & penilaian portofolio</h2>
    <p>Hasil diperbarui dari data tersimpan. Gunakan sebagai panduan sebelum membagikan karya Anda.</p>
    <div class="insight-grid">
        <article class="insight-card" aria-labelledby="assessment-title">
            <div class="score-header"><div class="score-ring" style="--score:{{ $assessment['score'] }}"><span>{{ $assessment['score'] }}%<small>SKOR PORTOFOLIO</small></span></div><div><span class="eyebrow">PENILAIAN PORTOFOLIO PDF</span><h3 id="assessment-title">{{ $assessment['label'] }}</h3><p>Dinilai dari isi yang digunakan untuk membuat PDF Anda.</p></div></div>
            <div class="score-breakdown">@foreach($assessment['groups'] as $group)<div class="score-line"><span>{{ $group['name'] }} ({{ $group['weight'] * 100 }}%)</span><strong>{{ $group['raw'] }}/5</strong></div>@endforeach</div>
            <details class="assessment-details"><summary>Lihat perhitungan & kriteria SAW</summary>
                <p>Metode Simple Additive Weighting: <strong>r = x / max(x)</strong>, lalu <strong>skor = Σ(w × r) × 100%</strong>. Setiap kriteria bernilai 0–5 dan dibandingkan dengan profil ideal bernilai 5. Bobot berjumlah 100%.</p>
                <p>Rumus diadaptasi dari penelitian Wijaya & Marwan (2016), <cite>Sistem Penentu Penilaian Siswa pada Kurikulum 2013 Menggunakan Algoritma Simple Additive Weighting (SAW)</cite>, hlm. 122 dan 128. Kriteria portofolio, pemetaan bobot 20/40/40, dan batas kategori disusun untuk Portomatis; artikel membahas penilaian siswa.</p>
                @foreach($assessment['groups'] as $group)<h3>{{ $group['name'] }}</h3><p>{{ $group['raw'] }} ÷ 5 × {{ $group['weight'] }} × 100 = <strong>{{ $group['contribution'] }} poin</strong></p><ul class="rubric-list">@foreach($group['checks'] as $check)<li><span class="{{ $check['ok'] ? 'pass' : 'missing' }}">{{ $check['ok'] ? '✓' : '○' }}</span><span>{{ $check['label'] }} · {{ $check['ok'] ? 'terpenuhi' : 'belum terpenuhi' }}</span></li>@endforeach</ul>@endforeach
                <p>Kategori: 85–100% sangat baik; 70–&lt;85% baik; 50–&lt;70% cukup; di bawah 50% perlu dilengkapi. Skor menilai kelengkapan dan bukti tertulis, bukan keaslian karya atau mutu visual PDF.</p>
            </details>
        </article>
        <article class="insight-card"><span class="eyebrow">LANGKAH PERBAIKAN</span><h3>Saran untuk portofolio Anda</h3>
            @if($assessment['suggestions'])<ol class="improvement-list">@foreach(array_slice($assessment['suggestions'], 0, 5) as $suggestion)<li>{{ $suggestion }}</li>@endforeach</ol>
            @if(count($assessment['suggestions']) > 5)<details class="assessment-details"><summary>{{ count($assessment['suggestions']) - 5 }} saran lainnya</summary><ol class="improvement-list" start="6">@foreach(array_slice($assessment['suggestions'], 5) as $suggestion)<li>{{ $suggestion }}</li>@endforeach</ol></details>@endif
            @else<p>Semua kriteria kelengkapan sudah terpenuhi. Periksa kembali ejaan, akses tautan, dan kesesuaian isi dengan posisi yang Anda tuju.</p>@endif
            <a class="button secondary" data-builder-step="personal" href="{{ route('builder.step', 'personal') }}">Perbaiki data &rarr;</a>
        </article>
        <article class="insight-card"><span class="eyebrow">BERDASARKAN BUKTI TERTULIS</span><h3>Analisis kompetensi</h3>
            <p class="analysis-note">Pencocokan kata kunci membaca keahlian, pengalaman, dan proyek. Jumlah sumber menunjukkan dukungan data yang Anda isi.</p>
            @forelse($insights['competencies'] as $competency)<div class="competency-item"><strong>{{ $competency['name'] }}</strong><span class="source-count">Didukung {{ $competency['sources'] }} dari 3 sumber</span>@foreach($competency['evidence'] as $source => $words)<p><span class="evidence-label">{{ $source }}:</span> {{ implode(', ', $words) }}</p>@endforeach</div>
            @empty<p>Belum ada kompetensi yang dikenali. Isi keahlian spesifik, tanggung jawab pengalaman, dan deskripsi proyek. Bidang di luar kamus analisis dapat belum terdeteksi.</p>@endforelse
        </article>
        <article class="insight-card"><span class="eyebrow">ARAH KARIER ANDA</span><h3>Rekomendasi bidang pekerjaan</h3>
            <p class="analysis-note">Diurutkan menurut dukungan sumber dan kecocokan kata kunci. Ini panduan eksplorasi, bukan tes kompetensi atau jaminan diterima bekerja.</p>
            @forelse($insights['jobs'] as $job)<div class="job-item"><strong>{{ $loop->iteration }}. {{ $job['job'] }}</strong><span class="source-count">{{ $job['sources'] >= 2 ? 'Didukung beberapa sumber' : 'Eksplorasi awal · bukti masih terbatas' }}</span><p>Cocok berdasarkan {{ implode(', ', array_keys($job['evidence'])) }} yang menyebut {{ implode(', ', array_slice(array_values(array_unique(array_merge(...array_values($job['evidence'])))), 0, 6)) }}.</p><p>{{ $job['next'] }}</p></div>
            @empty<p>Belum cukup data yang dikenali untuk merekomendasikan bidang pekerjaan. Tambahkan pengalaman, keahlian, dan proyek yang relevan terlebih dahulu.</p>@endforelse
        </article>
    </div>
</section>
