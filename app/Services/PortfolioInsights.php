<?php

namespace App\Services;

class PortfolioInsights
{
    /** Keywords describe evidence in submitted text, never measured proficiency. */
    private const FIELDS = [
        'frontend' => ['name' => 'Pengembangan antarmuka web', 'job' => 'Front-end Developer',
            'keywords' => ['html', 'css', 'javascript', 'typescript', 'react', 'vue', 'tailwind', 'bootstrap', 'frontend', 'front-end'],
            'next' => 'Tambahkan proyek web responsif, tautan demo, dan penjelasan fitur yang Anda bangun.'],
        'web' => ['name' => 'Pengelolaan website', 'job' => 'Web Administrator / Web Developer',
            'keywords' => ['website', 'web', 'wordpress', 'cms', 'hosting', 'seo', 'mengelola website', 'pengelolaan website'],
            'next' => 'Jelaskan pemeliharaan, pembaruan konten, atau peningkatan kinerja website beserta hasilnya.'],
        'ux' => ['name' => 'Desain antarmuka dan pengalaman pengguna', 'job' => 'UI/UX Designer',
            'keywords' => ['figma', 'ui/ux', 'ui ux', 'wireframe', 'prototype', 'prototipe', 'usability', 'riset pengguna', 'user research', 'design system'],
            'next' => 'Buat studi kasus berisi masalah pengguna, proses desain, prototipe, dan hasil pengujian.'],
        'graphic' => ['name' => 'Desain visual', 'job' => 'Graphic Designer',
            'keywords' => ['canva', 'photoshop', 'illustrator', 'coreldraw', 'branding', 'desain grafis', 'poster', 'logo', 'ilustrasi'],
            'next' => 'Tampilkan karya visual dan jelaskan brief, peran Anda, serta alasan pemilihan desain.'],
        'backend' => ['name' => 'Pengembangan layanan dan database', 'job' => 'Back-end Developer',
            'keywords' => ['php', 'laravel', 'node.js', 'nodejs', 'express', 'django', 'sql', 'mysql', 'postgresql', 'rest api', 'backend', 'back-end'],
            'next' => 'Tambahkan dokumentasi API, rancangan database, serta contoh pengujian atau fitur keamanan.'],
        'mobile' => ['name' => 'Pengembangan aplikasi mobile', 'job' => 'Mobile App Developer',
            'keywords' => ['android', 'kotlin', 'flutter', 'dart', 'swift', 'react native', 'aplikasi mobile', 'jetpack'],
            'next' => 'Sertakan demo aplikasi, fitur yang Anda kerjakan, dan tautan repositori atau publikasi.'],
        'data' => ['name' => 'Pengolahan dan analisis data', 'job' => 'Data Analyst',
            'keywords' => ['excel', 'python', 'pandas', 'sql', 'power bi', 'tableau', 'statistik', 'analisis data', 'data analysis', 'visualisasi data'],
            'next' => 'Sertakan studi kasus dengan sumber data, proses analisis, visualisasi, dan kesimpulan.'],
        'photo' => ['name' => 'Fotografi dan pengolahan foto', 'job' => 'Photographer / Photo Editor',
            'keywords' => ['fotografi', 'photography', 'fotografer', 'lightroom', 'pemotretan', 'retouch', 'foto produk'],
            'next' => 'Susun seri foto dan jelaskan konsep, proses pemotretan, serta penyuntingan yang Anda lakukan.'],
        'marketing' => ['name' => 'Konten dan pemasaran digital', 'job' => 'Digital Marketing / Content Specialist',
            'keywords' => ['digital marketing', 'copywriting', 'social media', 'media sosial', 'google ads', 'meta ads', 'kampanye', 'content marketing'],
            'next' => 'Tambahkan contoh konten atau kampanye dengan target audiens dan metrik hasilnya.'],
    ];

    public function analyze(array $draft): array
    {
        $sources = [
            'Keahlian' => implode(' ', $draft['skills'] ?? []),
            'Pengalaman' => $this->text($draft['experience'] ?? [], ['role', 'description']),
            'Proyek' => $this->text($draft['projects'] ?? [], ['title', 'description']),
        ];
        $competencies = [];
        foreach (self::FIELDS as $key => $field) {
            $evidence = [];
            $rank = 0;
            foreach ($sources as $source => $text) {
                $matches = array_values(array_filter($field['keywords'], fn ($word) => $this->contains($text, $word)));
                if ($matches) {
                    $evidence[$source] = $matches;
                    $rank += min(5, count($matches)) * ($source === 'Keahlian' ? 3 : 2);
                }
            }
            if ($evidence) {
                $competencies[] = $field + ['key' => $key, 'evidence' => $evidence, 'sources' => count($evidence), 'rank' => $rank];
            }
        }
        usort($competencies, fn ($a, $b) => ($b['sources'] <=> $a['sources']) ?: ($b['rank'] <=> $a['rank']) ?: strcmp($a['key'], $b['key']));

        return ['competencies' => $competencies, 'jobs' => array_slice($competencies, 0, 3), 'assessment' => $this->assessment($draft)];
    }

    private function assessment(array $draft): array
    {
        $p = $draft['personal'] ?? [];
        $education = $draft['education'] ?? [];
        $experience = $draft['experience'] ?? [];
        $projects = $draft['projects'] ?? [];
        $skills = array_values(array_unique(array_filter(array_map(fn ($s) => mb_strtolower(trim($s)), $draft['skills'] ?? []))));
        $evidence = $this->text($experience, ['description']).' '.$this->text($projects, ['description']);
        $groups = [
            ['name' => 'Profil dan kontak', 'weight' => 0.2, 'checks' => [
                ['ok' => !empty($p['name']) && !empty($p['role']), 'label' => 'Nama dan profesi terisi', 'tip' => 'Lengkapi nama dan profesi pada info pribadi.'],
                ['ok' => mb_strlen(trim($p['bio'] ?? '')) >= 80, 'label' => 'Ringkasan diri minimal 80 karakter', 'tip' => 'Tulis ringkasan diri minimal 80 karakter tentang fokus, pengalaman, dan nilai yang Anda tawarkan.'],
                ['ok' => (bool) filter_var($p['email'] ?? '', FILTER_VALIDATE_EMAIL), 'label' => 'Email portofolio valid', 'tip' => 'Isi email kontak portofolio yang valid agar perekrut dapat menghubungi Anda.'],
                ['ok' => !empty($p['phone']) || !empty($p['website']) || !empty($p['linkedin']), 'label' => 'Kontak tambahan tersedia', 'tip' => 'Tambahkan nomor telepon, website, atau LinkedIn sebagai kontak tambahan.'],
                ['ok' => !empty($p['location']), 'label' => 'Domisili terisi', 'tip' => 'Tambahkan domisili pada info pribadi.'],
            ]],
            ['name' => 'Kompetensi dan pengalaman', 'weight' => 0.4, 'checks' => [
                ['ok' => count($skills) >= 3, 'label' => 'Minimal 3 keahlian berbeda', 'tip' => 'Cantumkan minimal 3 keahlian berbeda yang benar-benar Anda kuasai.'],
                ['ok' => collect($education)->contains(fn ($r) => !empty($r['school']) && !empty($r['degree']) && !empty($r['period'])), 'label' => 'Pendidikan lengkap dengan periode', 'tip' => 'Lengkapi institusi pendidikan, program studi, dan periode belajar.'],
                ['ok' => collect($experience)->contains(fn ($r) => !empty($r['company']) && !empty($r['role']) && !empty($r['period'])), 'label' => 'Pengalaman lengkap dengan posisi dan periode', 'tip' => 'Isi pengalaman kerja, magang, organisasi, atau sukarela beserta posisi dan periodenya.'],
                ['ok' => collect($experience)->contains(fn ($r) => mb_strlen(trim($r['description'] ?? '')) >= 80), 'label' => 'Deskripsi pengalaman minimal 80 karakter', 'tip' => 'Perjelas tanggung jawab dan hasil pada pengalaman Anda dalam minimal 80 karakter.'],
                ['ok' => collect($skills)->contains(fn ($skill) => $this->contains($evidence, $skill)), 'label' => 'Keahlian didukung deskripsi pengalaman/proyek', 'tip' => 'Sebutkan keahlian yang digunakan dalam deskripsi pengalaman atau proyek agar ada bukti penerapannya.'],
            ]],
            ['name' => 'Karya dan bukti proyek', 'weight' => 0.4, 'checks' => [
                ['ok' => count($projects) > 0, 'label' => 'Minimal 1 proyek tersedia', 'tip' => 'Tambahkan minimal satu proyek, termasuk proyek kuliah atau proyek pribadi.'],
                ['ok' => $this->every($projects, fn ($r) => mb_strlen(trim($r['description'] ?? '')) >= 80), 'label' => 'Setiap proyek dideskripsikan minimal 80 karakter', 'tip' => 'Lengkapi deskripsi setiap proyek minimal 80 karakter agar konteks karya dapat dipahami.'],
                ['ok' => $this->every($projects, fn ($r) => preg_match('~^https?://~i', $r['url'] ?? '') && filter_var($r['url'], FILTER_VALIDATE_URL)), 'label' => 'Setiap proyek memiliki tautan bukti', 'tip' => 'Tambahkan tautan demo, repositori, atau dokumentasi pada setiap proyek.'],
                ['ok' => $this->every($projects, fn ($r) => preg_match('/\b(peran|saya|mengembangkan|membangun|merancang|mendesain|menganalisis|mengelola|developed|designed|built)\b/ui', $r['description'] ?? '')), 'label' => 'Kontribusi pribadi dijelaskan di setiap proyek', 'tip' => 'Jelaskan peran atau kontribusi Anda pada setiap proyek, misalnya “Saya merancang antarmuka…”.'],
                ['ok' => $this->every($projects, fn ($r) => preg_match('/\b(hasil|meningkatkan|mengurangi|mempercepat|dampak|berhasil|result|improved|reduced)\b/ui', $r['description'] ?? '')), 'label' => 'Hasil atau dampak dijelaskan di setiap proyek', 'tip' => 'Jelaskan hasil atau dampak setiap proyek; gunakan ukuran nyata jika tersedia.'],
            ]],
        ];
        $score = 0;
        $suggestions = [];
        foreach ($groups as &$group) {
            $group['raw'] = count(array_filter($group['checks'], fn ($c) => $c['ok']));
            // Include an explicit ideal alternative (5) in each benefit column.
            $group['max'] = 5;
            $group['normalized'] = $group['raw'] / $group['max'];
            $group['contribution'] = round($group['weight'] * $group['normalized'] * 100, 1);
            $score += $group['contribution'];
            foreach ($group['checks'] as $check) {
                if (!$check['ok']) $suggestions[] = $check['tip'];
            }
        }
        unset($group);
        $score = round($score, 1);

        return ['score' => $score, 'label' => match (true) {
            $score >= 85 => 'Sangat baik', $score >= 70 => 'Baik', $score >= 50 => 'Cukup', default => 'Perlu dilengkapi',
        }, 'groups' => $groups, 'suggestions' => $suggestions];
    }

    private function every(array $rows, callable $condition): bool
    {
        return count($rows) > 0 && collect($rows)->every($condition);
    }

    private function text(array $rows, array $fields): string
    {
        return implode(' ', array_map(fn ($row) => implode(' ', array_map(fn ($field) => $row[$field] ?? '', $fields)), $rows));
    }

    private function contains(string $text, string $word): bool
    {
        return (bool) preg_match('/(?<![\pL\pN])'.preg_quote($word, '/').'(?![\pL\pN])/ui', $text);
    }
}
