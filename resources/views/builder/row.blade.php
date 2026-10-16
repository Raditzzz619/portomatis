<details class="repeat-row" data-row open>
    <summary class="row-heading"><span class="row-summary"><strong data-row-title>{{ ($row['school'] ?? $row['company'] ?? $row['title'] ?? '') ?: 'Entri baru' }}</strong><span class="field-help" data-row-subtitle>{{ $row['degree'] ?? $row['role'] ?? $row['url'] ?? '' }}</span></span><span class="row-toggle">Edit / tutup</span></summary>
    <div class="field-grid">
        @foreach($fields as $key => [$label, $placeholder, $max])
            <div class="field {{ $key === 'description' ? 'full' : '' }}"><label for="{{ $group }}-{{ $index }}-{{ $key }}">{{ $label }}</label>
                @if($key === 'description')
                    <textarea id="{{ $group }}-{{ $index }}-{{ $key }}" name="{{ $group }}[{{ $index }}][{{ $key }}]" rows="3" maxlength="{{ $max }}" placeholder="{{ $placeholder }}">{{ $row[$key] ?? '' }}</textarea>
                @else
                    <input id="{{ $group }}-{{ $index }}-{{ $key }}" name="{{ $group }}[{{ $index }}][{{ $key }}]" type="{{ $key === 'url' ? 'url' : 'text' }}" maxlength="{{ $max }}" placeholder="{{ $placeholder }}" value="{{ $row[$key] ?? '' }}">
                @endif
                @error($group.'.'.$index.'.'.$key)<span class="field-error">{{ $message }}</span>@enderror
            </div>
        @endforeach
    </div>
    <div class="row-actions"><button class="button secondary" type="button" data-collapse>Selesai mengisi</button><button class="remove-button" type="button" data-remove aria-label="Hapus entri {{ $group }}">Hapus entri</button></div>
</details>
