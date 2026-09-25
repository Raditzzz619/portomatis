<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Portomatis — Buat Portofolio Profesional</title>
  <meta name="description" content="Buat portofolio profesional dalam hitungan detik dengan Portomatis." />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  
</head>
<body>

  <header class="nav">
    <div class="nav-inner">
      <a class="brand" href="#" aria-label="Portomatis">
        <span class="brand-mark">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3v5M12 16v5M3 12h5M16 12h5"/>
            <path d="M8.1 8.1 5 5M15.9 8.1 19 5M8.1 15.9 5 19M15.9 15.9 19 19"/>
            <circle cx="12" cy="12" r="2.4"/>
          </svg>
        </span>
        <span class="brand-name">Portomatis</span>
      </a>
      <button class="nav-cta" onclick="goToCreate()">Mulai Membuat</button>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="hero-content">
        <h1>
          Buat Portofolio Profesional<br>
          dalam <span class="accent">Hitungan Detik</span>
        </h1>
        <button class="hero-cta" onclick="goToCreate()">
          Buat Portofolio Saya Sekarang <span class="arrow">→</span>
        </button>
      </div>
    </section>

    <section class="steps" id="cara-kerja">
      <div class="section-head">
        <h2>Dari halaman kosong menjadi<br>portofolio siap online</h2>
        <p>Tiga langkah sederhana tidak perlu keahlian desain, dan gratis.</p>
      </div>

      <div class="cards">
        <article class="card">
          <span class="card-number">01</span>
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 18.5V20h1.5L17.8 7.7l-1.5-1.5L4 18.5Z"/>
              <path d="m14.8 7.1 1.5 1.5"/>
              <path d="M15 3.5h4.5V8"/>
            </svg>
          </div>
          <h3>Isi data Anda</h3>
          <p>Tambahkan proyek, pengalaman, dan keahlian Anda melalui formulir sederhana. Tidak perlu keahlian desain.</p>
        </article>

        <article class="card">
          <span class="card-number">02</span>
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3.5" y="4" width="17" height="16" rx="2"/>
              <path d="M3.5 9h17"/>
              <path d="M7 13h4v4H7zM14 13h3"/>
            </svg>
          </div>
          <h3>Pilih template</h3>
          <p>Pilih dari berbagai template profesional yang menarik dan lihat pratinjau konten Anda secara langsung.</p>
        </article>

        <article class="card">
          <span class="card-number">03</span>
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
              <path d="M9 12h6"/>
              <path d="M7.5 16.5h-1A3.5 3.5 0 0 1 3 13v-2a3.5 3.5 0 0 1 3.5-3.5h3"/>
              <path d="M16.5 7.5h1A3.5 3.5 0 0 1 21 11v2a3.5 3.5 0 0 1-3.5 3.5h-3"/>
            </svg>
          </div>
          <h3>Unduh dan dapatkan Portofolio anda</h3>
          <p>1 klik, dan portofolio anda akan langsung selesai</p>
        </article>
      </div>

      <button class="steps-cta" onclick="goToCreate()">
        Buat Portofolio Saya Sekarang <span class="arrow">→</span>
      </button>
    </section>
  </main>

  <footer>
    <div class="footer-brand">
      <span class="brand-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3v5M12 16v5M3 12h5M16 12h5"/>
          <path d="M8.1 8.1 5 5M15.9 8.1 19 5M8.1 15.9 5 19M15.9 15.9 19 19"/>
          <circle cx="12" cy="12" r="2.4"/>
        </svg>
      </span>
      <span>Portomatis</span>
    </div>
  </footer>

  <script>
    function goToCreate(){
      // Ubah URL ini jika tombol "Mulai Membuat" harus menuju halaman tertentu.
      const target = "#cara-kerja";
      document.querySelector(target).scrollIntoView({behavior:"smooth"});
    }
  </script>
</body>
</html>
