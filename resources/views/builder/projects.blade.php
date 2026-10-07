<section class="form-card">
    <div class="card-heading"><span class="section-icon" aria-hidden="true">04</span><div><h2>Keahlian Anda</h2><p>Pilih keahlian yang mendukung bidang Anda. Opsional.</p></div></div>
    <div class="field"><label for="skills">Daftar keahlian</label><textarea id="skills" name="skills" rows="3" maxlength="1000" placeholder="Figma, Riset pengguna, HTML, CSS">{{ old('skills', implode(', ', $draft['skills'] ?? [])) }}</textarea><span class="field-help">Pisahkan setiap keahlian dengan koma.</span></div>
</section>
@php($fields = ['title' => ['Nama proyek', 'Contoh: Redesign Aplikasi Ruang', 180], 'url' => ['Tautan proyek', 'https://...', 2048], 'description' => ['Deskripsi proyek', 'Ceritakan tantangan, solusi, peran Anda, dan hasilnya.', 2000]])
<section class="form-card" data-repeat-group="projects">
    <div class="card-heading"><span class="section-icon" aria-hidden="true">05</span><div><h2>Proyek pilihan</h2><p>Tampilkan karya terbaik Anda. Opsional.</p></div></div>
    <div data-rows>@foreach(old('projects', $draft['projects'] ?? [[]]) as $index => $row)@include('builder.row', ['group' => 'projects', 'index' => $index, 'row' => $row, 'fields' => $fields])@endforeach</div>
    <template data-row-template>@include('builder.row', ['group' => 'projects', 'index' => '__INDEX__', 'row' => [], 'fields' => $fields])</template>
    <button type="button" class="button add-button" data-add>+ Tambah proyek</button>
</section>
