document.addEventListener('DOMContentLoaded', () => {
    const main = document.querySelector('#form-content');
    if (!main) return;
    const stepOrder = ['template', 'personal', 'experience', 'projects', 'preview'];
    const stepPaths = new Set(Array.from(document.querySelectorAll('[data-builder-step]'), link => new URL(link.href).pathname));
    let dirty = false;
    let busy = false;
    let currentUrl = location.href;
    let historyIndex = history.state?.builderIndex ?? 0;
    let restoringHistory = false;
    let disposePhoto = () => {};

    const markDirty = () => {
        dirty = true;
        const status = main.querySelector('.save-status');
        if (status) status.textContent = main.querySelector('.template-grid')
            ? 'Tekan tombol pada template pilihan Anda untuk melanjutkan.'
            : 'Perubahan akan disimpan saat melanjutkan atau berpindah langkah.';
    };
    const showError = (message, errors = {}) => {
        main.querySelector('.error-summary')?.remove();
        main.querySelectorAll('[data-validation-error]').forEach(element => {
            main.querySelectorAll('[aria-describedby]').forEach(field => {
                const ids = field.getAttribute('aria-describedby').split(/\s+/).filter(id => id && id !== element.id);
                if (ids.length) field.setAttribute('aria-describedby', ids.join(' '));
                else field.removeAttribute('aria-describedby');
            });
            element.remove();
        });
        main.querySelectorAll('[aria-invalid="true"]').forEach(element => element.removeAttribute('aria-invalid'));
        const summary = document.createElement('div');
        summary.className = 'error-summary';
        summary.setAttribute('role', 'alert');
        summary.tabIndex = -1;
        const title = document.createElement('strong');
        title.textContent = message;
        summary.append(title);
        const list = document.createElement('ul');
        Object.entries(errors).forEach(([key, messages], index) => {
            const name = key.split('.').reduce((name, part, i) => i ? `${name}[${part}]` : part, '');
            const field = Array.from(main.querySelectorAll('[name]')).find(input => input.name === name);
            const fieldMessage = Array.isArray(messages) ? messages[0] : messages;
            const item = document.createElement('li');
            item.textContent = fieldMessage;
            list.append(item);
            if (!field) return;
            const error = document.createElement('span');
            error.className = 'field-error';
            error.id = `validation-error-${index}`;
            error.dataset.validationError = '';
            error.textContent = fieldMessage;
            field.setAttribute('aria-invalid', 'true');
            field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
            field.insertAdjacentElement('afterend', error);
            const row = field.closest('[data-row]');
            if (row) row.open = true;
        });
        if (list.children.length) summary.append(list);
        main.querySelector('.page-heading').insertAdjacentElement('afterend', summary);
        summary.focus();
    };
    const initializePhoto = () => {
        const upload = main.querySelector('[data-photo-upload]');
        if (!upload) return;
        const input = upload.querySelector('input[type="file"]');
        const preview = upload.querySelector('[data-photo-preview]');
        const placeholder = upload.querySelector('[data-photo-placeholder]');
        const remove = upload.querySelector('[data-photo-remove]');
        const removeValue = upload.querySelector('[data-photo-remove-value]');
        const error = upload.querySelector('[data-photo-error]');
        const savedPhoto = preview.getAttribute('src');
        let objectUrl;
        disposePhoto = () => { if (objectUrl) URL.revokeObjectURL(objectUrl); };
        const showPhoto = src => {
            preview.src = src;
            preview.hidden = !src;
            placeholder.hidden = Boolean(src);
            remove.hidden = !src;
        };
        if (removeValue.value === '1') showPhoto('');
        input.addEventListener('change', () => {
            disposePhoto();
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
            } else showPhoto(removeValue.value === '1' ? '' : savedPhoto);
        });
        remove.addEventListener('click', () => {
            disposePhoto();
            input.value = '';
            removeValue.value = '1';
            error.textContent = '';
            input.removeAttribute('aria-invalid');
            showPhoto('');
            input.focus();
            markDirty();
        });
    };
    const initializeRows = () => {
        main.querySelectorAll('[data-repeat-group]').forEach(group => {
            const rows = group.querySelector('[data-rows]');
            const add = group.querySelector('[data-add]');
            let next = Math.max(-1, ...Array.from(rows.querySelectorAll('[name]'), input => Number(input.name.match(/\[(\d+)\]/)?.[1] ?? -1))) + 1;
            const refresh = () => {
                rows.querySelectorAll('[data-row]').forEach((row, index) => {
                    const inputs = row.querySelectorAll('input, textarea');
                    row.querySelector('[data-row-title]').textContent = inputs[0]?.value.trim() || `Entri ${index + 1}`;
                    row.querySelector('[data-row-subtitle]').textContent = [inputs[1]?.value.trim(), row.querySelector('[name$="[period]"]')?.value.trim()].filter(Boolean).join(' / ');
                    const toggle = row.querySelector('[data-row-toggle]');
                    if (toggle) toggle.textContent = row.open ? 'Tutup' : 'Edit';
                });
                add.disabled = rows.children.length >= 20;
            };
            add.addEventListener('click', () => {
                if (rows.children.length >= 20) return;
                const template = group.querySelector('[data-row-template]').content.cloneNode(true);
                template.querySelectorAll('[name], [id], [for]').forEach(element => {
                    for (const attribute of ['name', 'id', 'for']) {
                        if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', String(next)));
                    }
                });
                next++;
                rows.querySelectorAll('[data-row]').forEach(row => { row.open = false; });
                rows.appendChild(template);
                refresh();
                rows.lastElementChild.querySelector('input, textarea')?.focus();
                markDirty();
            });
            rows.addEventListener('click', event => {
                const remove = event.target.closest('[data-remove]');
                if (!remove) return;
                remove.closest('[data-row]').remove();
                refresh();
                add.focus();
                markDirty();
            });
            rows.addEventListener('input', refresh);
            rows.addEventListener('toggle', event => {
                if (!event.target.matches('[data-row]')) return;
                if (event.target.open) rows.querySelectorAll('[data-row]').forEach(row => {
                    if (row !== event.target && !row.querySelector('.field-error')) row.open = false;
                });
                refresh();
            }, true);
            rows.querySelectorAll('[data-row]').forEach((row, index) => {
                row.open = Boolean(row.querySelector('.field-error')) || (index === 0 && !row.querySelector('input')?.value);
            });
            refresh();
        });
    };
    const initializePreview = () => {
        const tabs = Array.from(main.querySelectorAll('[data-preview-tab]'));
        const selectTab = selected => {
            tabs.forEach(tab => {
                const active = tab === selected;
                tab.setAttribute('aria-selected', String(active));
                tab.tabIndex = active ? 0 : -1;
                const panel = main.querySelector(`[data-preview-panel="${tab.dataset.previewTab}"]`);
                panel.hidden = !active;
                if (active) panel.querySelectorAll('[data-pdf-src]').forEach(frame => {
                    if (!frame.getAttribute('src')) frame.src = frame.dataset.pdfSrc;
                });
            });
        };
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => selectTab(tab));
            tab.addEventListener('keydown', event => {
                const offset = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
                const next = event.key === 'Home' ? tabs[0] : event.key === 'End' ? tabs.at(-1) : offset ? tabs[(index + offset + tabs.length) % tabs.length] : null;
                if (next) { event.preventDefault(); selectTab(next); next.focus(); }
            });
        });
        main.querySelector('[data-copy]')?.addEventListener('click', async () => {
            const input = main.querySelector('#share-url');
            const status = main.querySelector('[data-copy-status]');
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
    };
    const initializeStep = () => {
        dirty = false;
        initializePhoto();
        initializeRows();
        initializePreview();
        window.PortomatisDomicile?.init(main);
        document.dispatchEvent(new CustomEvent('portomatis:step-ready', { detail: { root: main } }));
        main.querySelector('.error-summary')?.focus();
        document.querySelector('[aria-current="step"]')?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    };
    const saveForm = async (form, submitter) => {
        if (!form.reportValidity()) return null;
        const data = new FormData(form);
        if (submitter?.name) data.set(submitter.name, submitter.value);
        if (form.querySelector('.template-grid') && !data.has('template')) data.set('template', data.get('template_selection') || 'minimalist');
        form.inert = true;
        const response = await fetch(form.action, {
            method: 'POST', body: data, credentials: 'same-origin',
            headers: { 'Accept': 'application/json, text/html;q=0.9', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (response.status === 422) {
            const result = await response.json();
            showError('Periksa kembali data Anda.', result.errors);
            return null;
        }
        if (!response.ok) throw new Error(response.status === 419 ? 'Sesi formulir telah berakhir. Buka ulang halaman setelah menyalin data yang belum disimpan.' : 'Data belum berhasil disimpan. Silakan coba lagi.');
        dirty = false;
        return response;
    };
    const renderStep = async (response, historyMode, targetIndex) => {
        if (!response.ok) throw new Error('Langkah ini belum dapat dimuat. Silakan coba lagi.');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = page.querySelector('#form-content');
        if (!next || !page.querySelector('.sidebar')) throw new Error('Halaman belum berhasil dimuat. Silakan coba lagi.');
        const backwards = stepOrder.indexOf(next.dataset.currentStep) < stepOrder.indexOf(main.dataset.currentStep);
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches && main.animate) {
            await main.animate([{ opacity: 1, transform: 'translateX(0)' }, { opacity: 0, transform: `translateX(${backwards ? 24 : -24}px)` }], { duration: 130, easing: 'ease-in' }).finished;
        }
        disposePhoto();
        disposePhoto = () => {};
        // Clone into the active document so embedded previews receive a browsing context.
        main.replaceChildren(...Array.from(next.childNodes, node => document.importNode(node, true)));
        main.dataset.currentStep = next.dataset.currentStep;
        document.querySelector('.sidebar').replaceChildren(...page.querySelector('.sidebar').childNodes);
        document.title = page.title;
        currentUrl = response.url;
        if (historyMode === 'push' && currentUrl !== location.href) history.pushState({ builder: true, builderIndex: ++historyIndex }, '', currentUrl);
        else {
            historyIndex = targetIndex ?? historyIndex;
            history.replaceState({ builder: true, builderIndex: historyIndex }, '', currentUrl);
        }
        initializeStep();
        if (!main.querySelector('.error-summary')) main.focus({ preventScroll: true });
        main.scrollIntoView({ block: 'start', behavior: 'instant' });
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches && main.animate) {
            await main.animate([{ opacity: 0, transform: `translateX(${backwards ? -24 : 24}px)` }, { opacity: 1, transform: 'translateX(0)' }], { duration: 210, easing: 'ease-out' }).finished;
        }
    };
    const restoreHistory = targetIndex => {
        const delta = historyIndex - targetIndex;
        if (!Number.isFinite(delta) || !delta) return;
        restoringHistory = true;
        history.go(delta);
    };
    const navigate = async ({ url, form, submitter, historyMode = 'push', targetIndex }) => {
        if (busy) return;
        const activeForm = form || main.querySelector('.builder-form');
        busy = true;
        main.setAttribute('aria-busy', 'true');
        document.body.classList.add('builder-loading');
        try {
            let response;
            if (activeForm && (form || dirty)) {
                const status = main.querySelector('.save-status');
                if (status) status.textContent = 'Menyimpan data…';
                response = await saveForm(activeForm, submitter);
                if (!response) {
                    if (status) status.textContent = 'Lengkapi data yang ditandai agar perubahan dapat disimpan.';
                    if (historyMode === 'replace') restoreHistory(targetIndex);
                    return;
                }
            }
            if (url && (!response || new URL(response.url).pathname !== new URL(url).pathname)) {
                response = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } });
            }
            await renderStep(response, historyMode, targetIndex);
        } catch (error) {
            if (historyMode === 'replace') restoreHistory(targetIndex);
            showError(error instanceof TypeError ? 'Koneksi terputus. Data Anda masih ada di formulir; coba simpan kembali.' : error.message);
        } finally {
            busy = false;
            if (activeForm) activeForm.inert = false;
            main.removeAttribute('aria-busy');
            document.body.classList.remove('builder-loading');
        }
    };
    main.addEventListener('input', event => { if (event.target.closest('.builder-form')) markDirty(); });
    main.addEventListener('change', event => { if (event.target.closest('.builder-form')) markDirty(); });
    main.addEventListener('invalid', event => { const row = event.target.closest('[data-row]'); if (row) row.open = true; }, true);
    main.addEventListener('submit', event => {
        if (!event.target.matches('.builder-form') || !window.fetch) return;
        event.preventDefault();
        navigate({ form: event.target, submitter: event.submitter });
    });
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || !stepPaths.has(url.pathname) || url.hash || !window.fetch) return;
        event.preventDefault();
        if (url.href !== currentUrl) navigate({ url: url.href });
    });
    window.addEventListener('popstate', event => {
        if (restoringHistory) { restoringHistory = false; return; }
        if (stepPaths.has(location.pathname)) {
            const targetIndex = event.state?.builderIndex;
            if (busy) restoreHistory(targetIndex);
            else navigate({ url: location.href, historyMode: 'replace', targetIndex });
        }
    });
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    history.replaceState({ builder: true, builderIndex: historyIndex }, '', currentUrl);
    initializeStep();
});
