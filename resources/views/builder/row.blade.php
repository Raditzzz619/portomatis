<div class="repeat-row" data-row>
    <div class="row-heading"><strong data-row-title>Entri {{ is_numeric($index) ? $index + 1 : '' }}</strong><button class="remove-button" type="button" data-remove aria-label="Hapus entri {{ $group }}">Hapus</button></div>
    <div class="field-grid">
        @foreach($fields as $key => [$label, $placeholder, $max])
            <div class="field {{ $key === 'description' ? 'full' : '' }}"><label for="{{ $group }}-{{ $index }}-{{ $key }}">{{ $label }}</label>
                @if($key === 'description')
                    <textarea id="{{ $group }}-{{ $index }}-{{ $key }}" name="{{ $group }}[{{ $index }}][{{ $key }}]" rows="4" maxlength="{{ $max }}" placeholder="{{ $placeholder }}">{{ $row[$key] ?? '' }}</textarea>
                @else
                    <input id="{{ $group }}-{{ $index }}-{{ $key }}" name="{{ $group }}[{{ $index }}][{{ $key }}]" type="{{ $key === 'url' ? 'url' : 'text' }}" maxlength="{{ $max }}" placeholder="{{ $placeholder }}" value="{{ $row[$key] ?? '' }}">
                @endif
                @error($group.'.'.$index.'.'.$key)<span class="field-error">{{ $message }}</span>@enderror
            </div>
        @endforeach
    </div>
</div>
