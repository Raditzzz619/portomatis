@php
    $personal = old('personal', $draft['personal'] ?? []);
    $domicileProvinces = \App\Support\DomicileRegions::provinces();
    $savedDomicile = $personal['location'] ?? '';
    $knownDomiciles = collect($domicileProvinces)->flatMap(fn ($province) => array_map(fn ($regency) => \App\Support\DomicileRegions::label($province, $regency), $province['regencies']));
    $hasLegacyDomicile = $savedDomicile !== '' && !$knownDomiciles->contains($savedDomicile);
@endphp
<section class="form-card">
    <div class="card-heading"><span class="section-icon" aria-hidden="true">01</span><div><h2>Perkenalkan diri Anda</h2><p>Kolom bertanda * wajib diisi. Tautan website dan LinkedIn opsional, boleh dikosongkan.</p></div></div>
    <div class="photo-upload" data-photo-upload>
        <div class="photo-preview">
            <img data-photo-preview src="{{ $draft['personal']['photo'] ?? '' }}" alt="Pratinjau foto profil" @if(empty($draft['personal']['photo'])) hidden @endif>
            <span data-photo-placeholder @if(!empty($draft['personal']['photo'])) hidden @endif aria-hidden="true">+</span>
        </div>
        <div class="field photo-field">
            <label for="profile-photo">Foto profil <span class="field-help">(opsional)</span></label>
            <span class="field-help" id="photo-help">Tambahkan foto agar portofolio lebih personal. JPG, PNG, atau WebP, maksimal 2 MB.</span>
            <input id="profile-photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help photo-error" @if($errors->has('profile_photo')) aria-invalid="true" @endif>
            <input type="hidden" name="remove_photo" value="{{ old('remove_photo', '0') }}" data-photo-remove-value>
            <span class="field-error" id="photo-error" data-photo-error role="status">{{ $errors->first('profile_photo') }}</span>
            <button type="button" class="remove-button photo-remove" data-photo-remove @if(empty($draft['personal']['photo'])) hidden @endif>Hapus foto</button>
        </div>
    </div>
    <div class="field-grid">
        @foreach(['name' => ['Nama lengkap', 'Nama Anda', true], 'role' => ['Profesi / bidang', 'Contoh: Product Designer', true], 'email' => ['Email', 'nama@email.com', false], 'phone' => ['Nomor telepon', 'Contoh: +62 812 3456 7890', false], 'website' => ['Website (opsional)', 'https://website-anda.com', false], 'linkedin' => ['LinkedIn (opsional)', 'https://www.linkedin.com/in/nama-anda', false]] as $key => [$label, $placeholder, $required])
            <div class="field"><label for="personal-{{ $key }}">{{ $label }}{{ $required ? ' *' : '' }}</label><input id="personal-{{ $key }}" name="personal[{{ $key }}]" type="{{ $key === 'email' ? 'email' : (in_array($key, ['website','linkedin']) ? 'url' : ($key === 'phone' ? 'tel' : 'text')) }}" value="{{ $personal[$key] ?? '' }}" placeholder="{{ $placeholder }}" maxlength="{{ $key === 'name' ? 100 : ($key === 'role' || $key === 'location' ? 120 : ($key === 'phone' ? 40 : ($key === 'email' ? 255 : 2048))) }}" @required($required) @if($errors->has('personal.'.$key)) aria-invalid="true" aria-describedby="error-{{ $key }}" @endif>
            @error('personal.'.$key)<span class="field-error" id="error-{{ $key }}">{{ $message }}</span>@enderror</div>
        @endforeach
        <fieldset class="domicile-picker field full" data-domicile>
            <legend>Domisili <span class="field-help">(opsional)</span></legend>
            <p class="field-help" id="domicile-help">Pilih provinsi dan kabupaten/kota tempat Anda berdomisili. Tidak perlu mengetik alamat lengkap.</p>
            <div class="domicile-grid">
                <div class="field" data-domicile-province-field hidden>
                    <label for="domicile-province">Provinsi</label>
                    <select id="domicile-province" data-domicile-province aria-describedby="domicile-help">
                        <option value="">Pilih provinsi</option>
                        @foreach($domicileProvinces as $province)
                            <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="personal-location">Kabupaten / kota</label>
                    <select id="personal-location" name="personal[location]" data-domicile-regency aria-describedby="domicile-help @if($errors->has('personal.location')) error-location @endif" @if($errors->has('personal.location')) aria-invalid="true" @endif>
                        <option value="">Tidak ditampilkan</option>
                        @if($hasLegacyDomicile)<option value="{{ $savedDomicile }}" selected data-legacy-domicile>Domisili tersimpan: {{ $savedDomicile }}</option>@endif
                        @foreach($domicileProvinces as $province)
                            <optgroup label="{{ $province['name'] }}" data-province="{{ $province['id'] }}">
                                @foreach($province['regencies'] as $regency)
                                    @php($domicileLabel = \App\Support\DomicileRegions::label($province, $regency))
                                    <option value="{{ $domicileLabel }}" data-regency="{{ $regency['id'] }}" @selected($savedDomicile === $domicileLabel)>{{ $regency['name'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('personal.location')<span class="field-error" id="error-location">{{ $message }}</span>@enderror
                </div>
            </div>
            @if($hasLegacyDomicile)<p class="field-help" data-domicile-legacy>Domisili dari draft lama tetap digunakan sampai Anda memilih wilayah pengganti atau mengosongkannya.</p>@endif
            <div class="domicile-gps" data-domicile-gps hidden>
                <button type="button" class="button secondary" data-domicile-detect aria-describedby="domicile-gps-help">Cari wilayah di dekat saya (GPS)</button>
                <p class="field-help" id="domicile-gps-help">GPS opsional dan memerlukan izin browser. Perkiraan dihitung di perangkat Anda; koordinat tidak dikirim atau disimpan dalam portofolio. Posisi saat ini belum tentu domisili Anda.</p>
                <div class="domicile-suggestion" data-domicile-suggestion hidden>
                    <label for="domicile-candidates">Periksa perkiraan wilayah terdekat</label>
                    <p class="field-help">Perkiraan memakai titik referensi wilayah, bukan batas administratif. Hasil dapat keliru, terutama dekat perbatasan. Pilih hanya jika sesuai domisili Anda, atau gunakan pilihan provinsi di atas.</p>
                    <select id="domicile-candidates" data-domicile-candidates></select>
                    <div class="domicile-suggestion-actions">
                        <button type="button" class="button secondary" data-domicile-accept disabled>Gunakan wilayah pilihan</button>
                        <button type="button" class="remove-button" data-domicile-dismiss>Abaikan perkiraan</button>
                    </div>
                </div>
                <p class="field-help" role="status" aria-live="polite" data-domicile-status></p>
            </div>
            <noscript><p class="field-help">Pilih kabupaten/kota dari daftar yang dikelompokkan menurut provinsi. GPS tersedia ketika JavaScript diaktifkan.</p></noscript>
            <script type="application/json" data-domicile-data>{!! json_encode($domicileProvinces, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        </fieldset>
        <div class="field full"><label for="personal-bio">Tentang Anda</label><textarea id="personal-bio" name="personal[bio]" rows="5" maxlength="2000" placeholder="Ceritakan bidang yang Anda tekuni, cara Anda bekerja, dan apa yang ingin Anda capai.">{{ $personal['bio'] ?? '' }}</textarea><span class="field-help">Tuliskan perkenalan singkat dengan bahasa Anda sendiri. Maksimal 2.000 karakter.</span></div>
    </div>
</section>
<div class="help-card"><strong>Bagikan informasi yang nyaman Anda tampilkan.</strong><p>Email, nomor telepon, dan informasi lain yang Anda isi akan terlihat di hasil unduhan dan tautan publik saat dipublikasikan.</p></div>
