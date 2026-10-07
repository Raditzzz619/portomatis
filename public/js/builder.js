document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.builder-form');
    let dirty = false;
    form?.addEventListener('input', () => {
        dirty = true;
        document.querySelector('.save-status').textContent = 'Ada perubahan yang belum disimpan. Tekan Simpan & lanjut.';
    });
    form?.addEventListener('submit', () => { dirty = false; });
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
