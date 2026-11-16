document.addEventListener('DOMContentLoaded', () => {
    const main = document.querySelector('#form-content');
    if (!main) return;
    const stepOrder = ['template', 'personal', 'experience', 'projects', 'preview'];
    let dirty = false;
    let busy = false;
    let disposePhoto = () => {};
    const viewport = main.querySelector('[data-step-viewport]');
    const resizeObserver = 'ResizeObserver' in window ? new ResizeObserver(entries => {
        const activePanel = main.querySelector('.step-panel.active');
        if (entries.some(entry => entry.target === activePanel)) {
            viewport.style.height = `${activePanel.offsetHeight}px`;
        }
    }) : null;
    let templateChosen = main.dataset.currentStep !== 'template';
    const descriptions = {
        template: 'Pilih tampilan yang paling mencerminkan diri Anda. Bisa diubah kapan saja.',
        personal: 'Mulai dengan perkenalan singkat. Bantu orang mengenal Anda dan karya Anda.',
        experience: 'Ceritakan perjalanan belajar dan pengalaman profesional Anda.',
        projects: 'Tunjukkan keahlian dan karya yang paling Anda banggakan.',
        preview: 'Periksa hasilnya, unduh, atau publikasikan saat Anda sudah siap.',
    };

    const markDirty = () => {
        dirty = true;
        const activeForm = main.querySelector('[data-step-panel].active .builder-form');
        const status = activeForm?.querySelector('.save-status');
        if (status) status.textContent = activeForm.querySelector('.template-grid')
            ? 'Pilihan template akan disimpan saat Anda menekan panah kanan.'
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
        const activePanel = main.querySelector('[data-step-panel].active');
        activePanel.querySelector('.builder-form')?.prepend(summary);
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
    };
    const saveForm = async (form, submitter) => {
        if (!form.reportValidity()) return null;
        const data = new FormData(form);
        if (submitter?.name) data.set(submitter.name, submitter.value);
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
    const updatePreview = async url => {
        const response = await fetch(url, { cache: 'no-store', credentials: 'same-origin', headers: { Accept: 'text/html' } });
        if (!response.ok) throw new Error('Pratinjau belum berhasil diperbarui. Silakan coba lagi.');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const preview = page.querySelector('[data-step-panel="preview"]');
        const currentPreview = main.querySelector('[data-step-panel="preview"]');
        if (!preview || !currentPreview) throw new Error('Pratinjau belum berhasil diperbarui. Silakan coba lagi.');
        currentPreview.replaceChildren(...Array.from(preview.childNodes, node => document.importNode(node, true)));
        initializePreview();
    };
    const activateStep = async (step, { pushHistory = true, focus = true } = {}) => {
        if (!stepOrder.includes(step)) return;
        if (step === 'preview' && !main.querySelector('[name="personal[name]"]')?.value.trim()) {
            step = 'personal';
            const notice = main.querySelector('.notice');
            if (notice) notice.textContent = 'Isi nama dan profesi Anda terlebih dahulu untuk melihat pratinjau.';
        }
        const nextPanel = main.querySelector(`[data-step-panel="${step}"]`);
        if (!nextPanel) return;
        const previousIndex = stepOrder.indexOf(main.dataset.currentStep);
        const nextIndex = stepOrder.indexOf(step);
        const stepTrack = main.querySelector('[data-step-track]');
        main.querySelectorAll('[data-step-panel]').forEach(panel => {
            const active = panel === nextPanel;
            panel.classList.toggle('active', active);
            panel.setAttribute('aria-hidden', String(!active));
            panel.inert = !active;
        });
        stepTrack.style.transform = `translateX(-${nextIndex * 100}%)`;
        viewport.style.height = `${nextPanel.offsetHeight}px`;
        resizeObserver?.disconnect();
        resizeObserver?.observe(nextPanel);
        main.dataset.currentStep = step;
        main.querySelector('[data-step-title]').textContent = nextPanel.dataset.stepLabel;
        main.querySelector('[data-step-count]').textContent = `LANGKAH ${nextIndex + 1} DARI ${stepOrder.length}`;
        main.querySelector('[data-step-description]').textContent = descriptions[step];
        main.querySelector('[data-progress-bar]').style.width = `${((nextIndex + 1) / stepOrder.length) * 100}%`;
        main.querySelector('[data-carousel-prev]').disabled = nextIndex === 0;
        main.querySelector('[data-carousel-next]').disabled = nextIndex === stepOrder.length - 1;
        if (step === 'template' && templateChosen) main.classList.add('template-chosen');
        document.title = `${nextPanel.dataset.stepLabel} - Portomatis`;
        if (pushHistory && step !== main.dataset.historyStep) history.pushState({ builder: true, step }, '', location.href);
        main.dataset.historyStep = step;
        if (focus) {
            main.querySelector('.page-heading').scrollIntoView({ block: 'start', behavior: 'smooth' });
            main.querySelector('[data-step-title]').focus({ preventScroll: true });
        }
    };
    const navigate = async ({ step, form, submitter }) => {
        if (busy) return;
        const activeForm = form || main.querySelector(`[data-form-step="${main.dataset.currentStep}"]`);
        busy = true;
        main.setAttribute('aria-busy', 'true');
        document.body.classList.add('builder-loading');
        try {
            let response = null;
            if (activeForm && (form || dirty)) {
                const status = activeForm.querySelector('.save-status');
                if (status) status.textContent = 'Menyimpan data…';
                response = await saveForm(activeForm, submitter);
                if (!response) {
                    if (status) status.textContent = 'Lengkapi data yang ditandai agar perubahan dapat disimpan.';
                    return;
                }
            }
            if (response && activeForm?.dataset.formStep === 'template') step = 'preview';
            if (response && (step === 'preview' || activeForm?.dataset.formStep === 'template')) {
                await updatePreview(response.url);
            }
            await activateStep(step || main.dataset.currentStep);
        } catch (error) {
            showError(error instanceof TypeError ? 'Koneksi terputus. Data Anda masih ada di formulir; coba simpan kembali.' : error.message);
        } finally {
            busy = false;
            if (activeForm) activeForm.inert = false;
            main.removeAttribute('aria-busy');
            document.body.classList.remove('builder-loading');
        }
    };
    document.documentElement.classList.add('js-enabled');
    main.dataset.historyStep = main.dataset.currentStep;
    main.querySelectorAll('[data-step-panel]').forEach(panel => {
        const inactive = panel.dataset.stepPanel !== main.dataset.currentStep;
        panel.setAttribute('aria-hidden', String(inactive));
        panel.inert = inactive;
    });
    const initialPanelIndex = stepOrder.indexOf(main.dataset.currentStep);
    main.querySelector('[data-step-track]').style.transform = `translateX(-${initialPanelIndex * 100}%)`;
    const initialActivePanel = main.querySelector('.step-panel.active');
    viewport.style.height = `${initialActivePanel.offsetHeight}px`;
    resizeObserver?.observe(initialActivePanel);
    main.querySelector('[data-progress-bar]').style.width = `${((stepOrder.indexOf(main.dataset.currentStep) + 1) / stepOrder.length) * 100}%`;
    main.querySelector('[data-carousel-prev]').disabled = stepOrder.indexOf(main.dataset.currentStep) === 0;
    main.querySelector('[data-carousel-next]').disabled = stepOrder.indexOf(main.dataset.currentStep) === stepOrder.length - 1;
    if (templateChosen) main.classList.add('template-chosen');
    else main.classList.add('template-choice-pending');
    history.replaceState({ builder: true, step: main.dataset.currentStep }, '', location.href);
    main.addEventListener('input', event => { if (event.target.closest('.builder-form')) markDirty(); });
    main.addEventListener('change', event => {
        if (!event.target.closest('.builder-form')) return;
        markDirty();
        if (event.target.matches('input[name="template"]')) {
            templateChosen = true;
            main.classList.remove('template-choice-pending');
            main.classList.add('template-chosen');
        }
    });
    main.addEventListener('invalid', event => { const row = event.target.closest('[data-row]'); if (row) row.open = true; }, true);
    main.addEventListener('submit', event => {
        if (!event.target.matches('.builder-form') || !window.fetch) return;
        event.preventDefault();
        const orderIndex = stepOrder.indexOf(event.target.dataset.formStep);
        navigate({ form: event.target, submitter: event.submitter, step: stepOrder[orderIndex + 1] });
    });
    document.addEventListener('click', event => {
        const carouselArrow = event.target.closest('[data-carousel-prev], [data-carousel-next]');
        if (carouselArrow) {
            const index = stepOrder.indexOf(main.dataset.currentStep);
            if (carouselArrow.matches('[data-carousel-prev]') && index > 0) {
                activateStep(stepOrder[index - 1]);
            } else if (carouselArrow.matches('[data-carousel-next]') && index < stepOrder.length - 1) {
                if (main.dataset.currentStep === 'template' && !templateChosen) return;
                const nextStep = stepOrder[index + 1];
                const form = main.querySelector(`[data-form-step="${main.dataset.currentStep}"]`);
                if (form) navigate({ form, step: nextStep });
                else activateStep(nextStep);
            }
            return;
        }
        if (event.target.closest('[data-go-to-personal]')) {
            activateStep('personal');
            return;
        }
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
        const url = new URL(link.href);
        const requestedStep = link.dataset.builderStep;
        if (url.origin !== location.origin || !stepOrder.includes(requestedStep) || url.hash || !window.fetch) return;
        event.preventDefault();
        if (requestedStep === main.dataset.currentStep) return;
        if (dirty && stepOrder.indexOf(requestedStep) > stepOrder.indexOf(main.dataset.currentStep)) {
            navigate({ form: main.querySelector(`[data-form-step="${main.dataset.currentStep}"]`), step: requestedStep });
        } else {
            activateStep(requestedStep);
        }
    });
    window.addEventListener('popstate', event => {
        const step = event.state?.step || Array.from(document.querySelectorAll('[data-builder-step]'))
            .find(link => new URL(link.href).pathname === location.pathname)?.dataset.builderStep;
        if (step) activateStep(step, { pushHistory: false });
    });
    let touchStartX = null;
    main.addEventListener('touchstart', event => { touchStartX = event.changedTouches[0]?.screenX ?? null; }, { passive: true });
    main.addEventListener('touchend', event => {
        if (touchStartX === null || busy) return;
        const distance = event.changedTouches[0].screenX - touchStartX;
        touchStartX = null;
        if (Math.abs(distance) < 70) return;
        const index = stepOrder.indexOf(main.dataset.currentStep);
        if (distance < 0 && index < stepOrder.length - 1) {
            const form = main.querySelector(`[data-form-step="${main.dataset.currentStep}"]`);
            if (form) navigate({ form, step: stepOrder[index + 1] });
            else activateStep(stepOrder[index + 1]);
        } else if (distance > 0 && index > 0) activateStep(stepOrder[index - 1]);
    }, { passive: true });
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    window.addEventListener('resize', () => {
        const activePanel = main.querySelector('.step-panel.active');
        viewport.style.height = `${activePanel.offsetHeight}px`;
    });
    initializeStep();
});
