<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portomatis - Buat Portofolio</title>
    <link rel="stylesheet" href="{{ asset('css/builder.css') }}">
</head>
<body>
<div class="builder-app">
<header class="topbar">
    <a href="/" class="brand"><span class="brand-icon">✣</span><span>Por<span>tomatis</span></span></a>
    <div class="top-actions">
        <a href="/" class="back-home">⌂ &nbsp;Kembali ke Beranda</a>
        <button class="outline-btn">▣ &nbsp; Copy Shareable Link</button>
        <button class="download-btn">⇩ &nbsp; Download Portfolio</button>
    </div>
</header>

<div class="builder-layout">
<aside class="sidebar">
    <div class="sidebar-heading"><span>◉ &nbsp; TAMPILAN</span><h2>Pilih template</h2></div>

    <div class="template-list">
        <button class="template-card active">
            <div class="template-preview minimalist-preview">
                <div class="mini-line line-short"></div><div class="mini-line line-dark"></div>
                <div class="mini-line line-medium"></div><div class="mini-circle"></div>
                <div class="mini-buttons"><i></i><i></i><i></i></div><b>✓</b>
            </div>
            <strong>Minimalist</strong><small>Bersih & elegan</small>
        </button>
        <button class="template-card">
            <div class="template-preview modern-preview">
                <div class="mini-line line-short"></div><div class="mini-line line-dark"></div>
                <div class="mini-line line-medium"></div><div class="mini-circle"></div>
                <div class="mini-buttons"><i></i><i></i><i></i></div>
            </div>
            <strong>Modern</strong><small>Berani & ekspresif</small>
        </button>
        <button class="template-card">
            <div class="template-preview tech-preview">
                <div class="mini-line line-short"></div><div class="mini-line line-light"></div>
                <div class="mini-line line-green"></div><div class="mini-circle dark"></div>
                <div class="mini-buttons"><i></i><i></i><i></i></div>
            </div>
            <strong>Tech</strong><small>Tajam & futuristik</small>
        </button>
    </div>

    <div class="sidebar-links">
        <button>Isi Info Pribadi <span>→</span></button>
        <button>Lanjut ke Pendidikan &amp;<br>Pengalaman <span>→</span></button>
    </div>

    <section class="score-box">
        <div class="score-title"><span>◉ &nbsp; SKOR PORTOFOLIO</span><strong>78/100</strong></div>
        <div class="score-progress"><span></span></div>
        <p>Portofolio Anda sudah terlihat kuat. Lengkapi detail untuk skor yang lebih tinggi.</p>
        <div class="score-item"><span>✓ &nbsp; Info pribadi</span><b>+20</b></div>
        <div class="score-item"><span>✓ &nbsp; Pendidikan</span><b>+15</b></div>
        <div class="score-item"><span>✓ &nbsp; Pengalaman kerja</span><b>+18</b></div>
        <div class="score-item"><span>✓ &nbsp; Proyek pilihan</span><b>+25</b></div>
    </section>

    <section class="info-box">
        <h3>♧ &nbsp; IDENTIFIKASI KEKURANGAN</h3>
        <strong>Portofolio Anda sudah kuat dan hampir siap dibagikan.</strong>
        <ul><li>Tambahkan hasil terukur pada deskripsi proyek.</li><li>Pastikan kontak dan tautan profesional mudah ditemukan.</li></ul>
    </section>

    <section class="analysis-box">
        <h3>♧ &nbsp; ANALISIS BIDANG PEKERJAAN</h3>
        <small>Bidang terdeteksi</small>
        <div class="job-row"><strong>Product<br>Design</strong><span>Permintaan<br>Tinggi</span></div>
        <div class="analysis-line"><span></span></div>
        <small>Keahlian yang paling dicari di bidang ini:</small>
        <div class="tags"><em>Figma</em><em>Riset pengguna</em><em>Desain Grafis</em></div>
        <hr><small>Rekomendasi</small>
        <p>Tampilkan proses berpikir dan dampak bisnis dari setiap studi kasus.</p>
    </section>

    <section class="relevance-box">
        <div class="relevance-title"><h3>◎ &nbsp; RELEVANSI<br>PORTOFOLIO</h3><strong>84<br><small>%</small></strong></div>
        <p>Portofolio Anda cukup relevan untuk bidang Product Design.</p>
        <div class="analysis-line"><span></span></div>
        <div class="check-line">✓ &nbsp; Keahlian sesuai bidang</div>
        <div class="check-line">✓ &nbsp; Struktur proyek jelas</div>
        <div class="check-line">✓ &nbsp; Template terlihat profesional</div>
    </section>
</aside>

<main class="workspace">
    <div class="workspace-header"><span>PRA TINJAU PORTOFOLIO</span><p><strong>Minimalist</strong> · Perubahan tersimpan otomatis</p></div>

    <div class="browser-frame">
        <div class="browser-bar">
            <div class="browser-dots"><i></i><i></i><i></i></div>
            <div class="address-bar">↗ &nbsp; portomatis.site/naoval</div>
        </div>

        <div class="portfolio-page">
            <nav class="portfolio-nav">
                <strong>NAOVAL.</strong>
                <div><a href="#">TENTANG</a><a href="#">PROYEK</a><a href="#">KONTAK</a></div>
            </nav>

            <section class="hero-preview">
                <div class="avatar">N</div>
                <p class="eyebrow">HALO, SAYA NAOVAL</p>
                <h1>Membangun<br>pengalaman digital yang<br>bermakna.</h1>
                <p class="description">Saya seorang desainer produk yang membantu brand dan tim membangun produk digital yang intuitif, indah, dan berdampak.</p>
                <button class="portfolio-cta">Lihat karya saya ↗</button>
            </section>

            <section class="projects-preview">
                <div class="projects-heading"><strong>Proyek pilihan</strong><span>01 — 03</span></div>
                <div class="project-grid">
                    <article class="project-card project-one"><div class="project-image"></div><strong>Aplikasi Ruang</strong></article>
                    <article class="project-card project-two"><div class="project-image"></div><strong>Kopi Senja</strong></article>
                    <article class="project-card project-three"><div class="project-image"></div><strong>Dompetku</strong></article>
                </div>
            </section>
        </div>
    </div>
</main>
</div>
</div>
<script src="{{ asset('js/builder.js') }}"></script>
</body>
</html>
