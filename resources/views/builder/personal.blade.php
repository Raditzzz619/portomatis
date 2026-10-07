@php($personal = old('personal', $draft['personal'] ?? []))
<section class="form-card">
    <div class="card-heading"><span class="section-icon" aria-hidden="true">01</span><div><h2>Perkenalkan diri Anda</h2><p>Kolom bertanda * wajib diisi.</p></div></div>
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
        @foreach(['name' => ['Nama lengkap', 'Nama Anda', true], 'role' => ['Profesi / bidang', 'Contoh: Product Designer', true], 'email' => ['Email', 'nama@email.com', false], 'phone' => ['Nomor telepon', 'Contoh: +62 812 3456 7890', false], 'location' => ['Lokasi', 'Contoh: Jakarta, Indonesia', false], 'website' => ['Website', 'https://website-anda.com', false], 'linkedin' => ['LinkedIn', 'https://www.linkedin.com/in/nama-anda', false]] as $key => [$label, $placeholder, $required])
            <div class="field"><label for="personal-{{ $key }}">{{ $label }}{{ $required ? ' *' : '' }}</label><input id="personal-{{ $key }}" name="personal[{{ $key }}]" type="{{ $key === 'email' ? 'email' : (in_array($key, ['website','linkedin']) ? 'url' : ($key === 'phone' ? 'tel' : 'text')) }}" value="{{ $personal[$key] ?? '' }}" placeholder="{{ $placeholder }}" maxlength="{{ $key === 'name' ? 100 : ($key === 'role' || $key === 'location' ? 120 : ($key === 'phone' ? 40 : ($key === 'email' ? 255 : 2048))) }}" @required($required) @if($errors->has('personal.'.$key)) aria-invalid="true" aria-describedby="error-{{ $key }}" @endif>
            @error('personal.'.$key)<span class="field-error" id="error-{{ $key }}">{{ $message }}</span>@enderror</div>
        @endforeach
        <div class="field full"><label for="personal-bio">Tentang Anda</label><textarea id="personal-bio" name="personal[bio]" rows="5" maxlength="2000" placeholder="Ceritakan bidang yang Anda tekuni, cara Anda bekerja, dan apa yang ingin Anda capai.">{{ $personal['bio'] ?? '' }}</textarea><span class="field-help">Tuliskan perkenalan singkat dengan bahasa Anda sendiri. Maksimal 2.000 karakter.</span></div>
    </div>
</section>
<div class="help-card"><strong>Bagikan informasi yang nyaman Anda tampilkan.</strong><p>Email, nomor telepon, dan informasi lain yang Anda isi akan terlihat di hasil unduhan dan tautan publik saat dipublikasikan.</p></div>
