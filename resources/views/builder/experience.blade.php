@foreach(['education' => ['Pendidikan', 'Tambahkan riwayat pendidikan atau pelatihan yang relevan.', ['school' => ['Institusi', 'Contoh: Universitas Indonesia', 180], 'degree' => ['Jurusan / program', 'Contoh: S1 Desain Komunikasi Visual', 180], 'period' => ['Periode', 'Contoh: 2020 - 2024', 80]]], 'experience' => ['Pengalaman', 'Ceritakan peran, kontribusi, dan dampak pekerjaan Anda.', ['company' => ['Perusahaan / organisasi', 'Contoh: Studio Kreatif', 180], 'role' => ['Posisi', 'Contoh: UI/UX Designer', 180], 'period' => ['Periode', 'Contoh: 2024 - Sekarang', 80], 'description' => ['Deskripsi', 'Tanggung jawab dan pencapaian Anda...', 2000]]]] as $group => [$title, $description, $fields])
<section class="form-card" data-repeat-group="{{ $group }}">
    <div class="card-heading"><span class="section-icon" aria-hidden="true">{{ $loop->iteration === 1 ? '02' : '03' }}</span><div><h2>{{ $title }}</h2><p>{{ $description }} Opsional.</p></div></div>
    <div data-rows>
        @foreach(old($group, $draft[$group] ?? [[]]) as $index => $row)
            @include('builder.row', ['group' => $group, 'index' => $index, 'row' => $row, 'fields' => $fields])
        @endforeach
    </div>
    <template data-row-template>@include('builder.row', ['group' => $group, 'index' => '__INDEX__', 'row' => [], 'fields' => $fields])</template>
    <button type="button" class="button add-button" data-add>+ Tambah {{ strtolower($title) }}</button>
</section>
@endforeach
