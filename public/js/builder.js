document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.builder-form');
    let dirty = false;
    form?.addEventListener('input', () => {
        dirty = true;
        document.querySelector('.save-status').textContent = 'Ada perubahan yang belum disimpan. Tekan Simpan & lanjut.';
    });
    form?.addEventListener('submit', () => { dirty = false; });
    const photoUpload = document.querySelector('[data-photo-upload]');
    if (photoUpload) {
        const input = photoUpload.querySelector('input[type="file"]');
        const preview = photoUpload.querySelector('[data-photo-preview]');
        const placeholder = photoUpload.querySelector('[data-photo-placeholder]');
        const remove = photoUpload.querySelector('[data-photo-remove]');
        const removeValue = photoUpload.querySelector('[data-photo-remove-value]');
        const error = photoUpload.querySelector('[data-photo-error]');
        const savedPhoto = preview.getAttribute('src');
        let objectUrl;
        const showPhoto = src => {
            preview.src = src;
            preview.hidden = !src;
            placeholder.hidden = Boolean(src);
            remove.hidden = !src;
        };
        if (removeValue.value === '1') showPhoto('');
        input.addEventListener('change', () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            const file = input.files[0];
            error.textContent = '';
            input.removeAttribute('aria-invalid');
            if (file && (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024)) {
                error.textContent = 'Pilih gambar JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.';
                input.value = '';
                input.setAttribute('aria-invalid', 'true');
                showPhoto(removeValue.value === '1' ? '' : savedPhoto);
                return;
            }
            if (file) {
                objectUrl = URL.createObjectURL(file);
                removeValue.value = '0';
                showPhoto(objectUrl);
            } else {
                showPhoto(removeValue.value === '1' ? '' : savedPhoto);
            }
        });
        remove.addEventListener('click', () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            input.value = '';
            removeValue.value = '1';
            error.textContent = '';
            input.removeAttribute('aria-invalid');
            showPhoto('');
            input.focus();
            form?.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
    window.addEventListener('beforeunload', event => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });

    document.querySelectorAll('[data-repeat-group]').forEach(group => {
        const rows = group.querySelector('[data-rows]');
        const addButton = group.querySelector('[data-add]');
        let next = Math.max(-1, ...Array.from(rows.querySelectorAll('[name]'), input => {
            const index = input.name.match(/\[(\d+)\]/);
            return index ? Number(index[1]) : -1;
        })) + 1;
        const refresh = () => {
            rows.querySelectorAll('[data-row-title]').forEach((title, index) => { title.textContent = `Entri ${index + 1}`; });
            addButton.disabled = rows.children.length >= 20;
        };
        addButton.addEventListener('click', () => {
            if (rows.children.length >= 20) return;
            const template = group.querySelector('[data-row-template]').content.cloneNode(true);
            template.querySelectorAll('[name], [id], [for]').forEach(element => {
                for (const attribute of ['name', 'id', 'for']) {
                    if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', String(next)));
                }
            });
            next++;
            rows.appendChild(template);
            refresh();
            rows.lastElementChild.querySelector('input, textarea')?.focus();
            form?.dispatchEvent(new Event('input', { bubbles: true }));
        });
        rows.addEventListener('click', event => {
            const remove = event.target.closest('[data-remove]');
            if (!remove) return;
            remove.closest('[data-row]').remove();
            refresh();
            addButton.focus();
            form?.dispatchEvent(new Event('input', { bubbles: true }));
        });
        refresh();
    });

    document.querySelector('[data-copy]')?.addEventListener('click', async () => {
        const input = document.querySelector('#share-url');
        const status = document.querySelector('[data-copy-status]');
        try {
            if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
            await navigator.clipboard.writeText(input.value);
            status.textContent = 'Tautan berhasil disalin.';
        } catch {
            input.focus();
            input.select();
            status.textContent = 'Pilih Salin dari menu browser, atau tekan Ctrl+C / Command+C untuk menyalin tautan.';
        }
    });
    document.querySelector('.error-summary')?.focus();
    document.querySelector('[aria-current="step"]')?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
});
