// Run against an isolated local server and Chrome with remote debugging enabled.
// PORTOMATIS_BASE_URL and PORTOMATIS_CDP_URL override the defaults below.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.PORTOMATIS_BASE_URL || 'http://127.0.0.1:8010';
const cdp = process.env.PORTOMATIS_CDP_URL || 'http://127.0.0.1:9223';

(async () => {
    const browserInfo = await (await fetch(`${cdp}/json/version`)).json();
    const browser = new WebSocket(browserInfo.webSocketDebuggerUrl);
    await new Promise(resolve => browser.addEventListener('open', resolve, { once: true }));
    let browserSequence = 0;
    const browserCall = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
        const id = ++browserSequence;
        const handler = event => {
            const message = JSON.parse(event.data);
            if (message.id !== id) return;
            browser.removeEventListener('message', handler);
            message.error ? reject(new Error(JSON.stringify(message.error))) : resolve(message.result);
        };
        browser.addEventListener('message', handler);
        browser.send(JSON.stringify({ id, method, params, sessionId }));
    });
    const context = await browserCall('Target.createBrowserContext');
    const created = await browserCall('Target.createTarget', { url: 'about:blank', browserContextId: context.browserContextId });
    const target = (await (await fetch(`${cdp}/json/list`)).json()).find(tab => tab.id === created.targetId);
    const socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let sequence = 0;
    const pending = new Map();
    const runtimeErrors = [];
    socket.addEventListener('message', event => {
        const result = JSON.parse(event.data);
        if (result.method === 'Runtime.exceptionThrown') runtimeErrors.push(result.params.exceptionDetails);
        if (!pending.has(result.id)) return;
        const { resolve, reject } = pending.get(result.id);
        pending.delete(result.id);
        result.error ? reject(new Error(JSON.stringify(result.error))) : resolve(result.result);
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++sequence;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
    const evaluate = async expression => {
        const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
        return result.result.value;
    };
    const wait = async expression => {
        for (let attempt = 0; attempt < 200; attempt++) {
            if (await evaluate(expression)) return;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        throw new Error(`Timed out: ${expression}; page=${await evaluate('location.href')}`);
    };
    const settled = step => wait(`document.readyState === 'complete' && document.querySelector('[data-current-step="${step}"]') && !document.body.classList.contains('builder-loading')`);
    const fill = values => evaluate(`(() => {
        for (const [name, value] of Object.entries(${JSON.stringify(values)})) {
            const field = document.getElementsByName(name)[0];
            if (!field) throw new Error('Missing field: ' + name);
            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }
    })()`);
    const submit = async (values, next) => {
        await fill(values);
        await evaluate(`document.querySelector('.builder-form').requestSubmit()`);
        await settled(next);
        assert.equal(await evaluate('window.__sameDocument'), 'retained', 'Builder navigation reloaded the document');
    };
    const checks = [];
    try {
        await send('Page.enable');
        await send('Runtime.enable');
        await send('Network.enable');
        await send('Page.navigate', { url: `${base}/builder/template` });
        await settled('template');
        await evaluate(`window.__sameDocument = 'retained'; document.querySelector('button[name="template"][value="modern"]').click()`);
        await settled('personal');
        assert.equal(await evaluate('window.__sameDocument'), 'retained');
        assert.equal(await evaluate('document.querySelector("[data-domicile-province]").options.length'), 39);
        await fill({ 'personal[name]': 'Ayu Putri', 'personal[role]': 'Product Designer', 'personal[website]': 'ftp://example.com' });
        await evaluate(`document.querySelector('.builder-form').requestSubmit()`);
        await wait(`document.querySelector('.error-summary') && !document.body.classList.contains('builder-loading')`);
        assert.equal(await evaluate('document.querySelector("[data-current-step]").dataset.currentStep'), 'personal');
        assert.equal(await evaluate('document.getElementsByName("personal[name]")[0].value'), 'Ayu Putri');
        assert.equal(await evaluate('document.querySelector(".builder-form").inert'), false);
        checks.push('server validation preserves inputs in the same document');
        await fill({ 'personal[website]': 'https://example.com/ayu' });
        await evaluate(`Object.defineProperty(navigator, 'geolocation', { configurable: true, value: { getCurrentPosition(success, failure) { failure({code: 1}); } } }); document.querySelector('[data-domicile-detect]').click()`);
        assert.match(await evaluate('document.querySelector("[data-domicile-status]").textContent'), /Izin GPS/);
        await evaluate(`Object.defineProperty(navigator, 'geolocation', { configurable: true, value: { getCurrentPosition(success) { success({ coords: {latitude: -6.91, longitude: 107.61, accuracy: 10} }); } } }); document.querySelector('[data-domicile-detect]').click()`);
        assert.equal(await evaluate('document.querySelector("[data-domicile-regency]").value'), '');
        await evaluate(`const candidates = document.querySelector('[data-domicile-candidates]'); candidates.value = '32.73'; candidates.dispatchEvent(new Event('change', {bubbles:true})); document.querySelector('[data-domicile-accept]').click()`);
        assert.equal(await evaluate('document.querySelector("[data-domicile-regency]").value'), 'Kota Bandung, Jawa Barat, Indonesia');
        checks.push('GPS denied fallback and explicit approximate suggestion acceptance');
        await submit({ 'personal[bio]': 'Merancang produk yang berguna dan mudah digunakan.', 'personal[email]': 'ayu@example.com' }, 'experience');
        await evaluate(`document.querySelector('[data-repeat-group="education"] [data-add]').click()`);
        assert.equal(await evaluate('document.querySelectorAll("[data-repeat-group=education] [data-row]").length'), 2);
        await evaluate(`document.querySelectorAll('[data-repeat-group="education"] [data-remove]')[1].click()`);
        await fill({ 'education[0][school]': 'Universitas Indonesia', 'education[0][degree]': 'Desain', 'experience[0][company]': 'Studio Kreatif', 'experience[0][role]': 'Designer' });
        assert.equal(await evaluate('document.querySelector("[data-collapse]")'), null);
        await evaluate(`const row = document.querySelector('[data-repeat-group="education"] [data-row]'); row.open = true; row.querySelector('summary').click()`);
        await wait(`document.querySelector('[data-repeat-group="education"] [data-row-toggle]').textContent === 'Edit'`);
        // Sidebar navigation must save dirty values before changing steps.
        await evaluate(`document.querySelector('[data-builder-step="projects"]').click()`);
        await settled('projects');
        await evaluate(`history.back()`);
        await settled('experience');
        assert.equal(await evaluate('document.getElementsByName("education[0][school]")[0].value'), 'Universitas Indonesia');
        await evaluate(`history.forward()`);
        await settled('projects');
        checks.push('entry edit/add/remove and saved sidebar navigation with browser back/forward');
        await submit({ skills: 'Figma, CSS', 'projects[0][title]': 'Aplikasi Ruang', 'projects[0][description]': 'Riset, perancangan, dan hasil proyek yang dapat dibaca dalam kedua format.', 'projects[0][url]': 'https://example.com/projects/ruang' }, 'preview');
        await new Promise(resolve => setTimeout(resolve, 300));
        const targets = await browserCall('Target.getTargets');
        const websiteFrame = targets.targetInfos.find(frame => frame.type === 'iframe' && frame.url === 'about:srcdoc' && frame.browserContextId === context.browserContextId);
        assert.ok(websiteFrame, 'Website preview frame was not created');
        const websiteSession = await browserCall('Target.attachToTarget', { targetId: websiteFrame.targetId, flatten: true });
        const rendered = await browserCall('Runtime.evaluate', { expression: 'document.body?.innerText', returnByValue: true }, websiteSession.sessionId);
        assert.match(rendered.result.value || '', /Aplikasi Ruang/, 'Website preview must render the actual portfolio');
        await evaluate(`document.querySelector('[data-preview-tab="pdf"]').click()`);
        assert.equal(await evaluate('document.querySelector("#preview-pdf").hidden'), false);
        assert.equal(await evaluate('document.querySelector("#preview-website").hidden'), true);
        const pdf = await evaluate(`fetch(${JSON.stringify(`${base}/builder/pdf`)}).then(async response => ({status: response.status, type: response.headers.get('content-type'), signature: new TextDecoder().decode((await response.arrayBuffer()).slice(0,5))}))`);
        assert.deepEqual(pdf, { status: 200, type: 'application/pdf', signature: '%PDF-' });
        await evaluate(`document.querySelector('[data-preview-tab="website"]').click()`);
        const website = await evaluate(`(() => { const html = document.querySelector('#preview-website iframe').srcdoc; return {containsName:html.includes('Ayu Putri'), containsDomicile:html.includes('Kota Bandung'), containsProject:html.includes('Aplikasi Ruang'), containsSchool:html.includes('Universitas Indonesia')}; })()`);
        assert.ok(Object.values(website).every(Boolean));
        checks.push('website/PDF preview tabs and real PDF response');
        for (const width of [320, 375, 768, 1440]) {
            await send('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
            assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, `Preview overflow at ${width}px`);
            await evaluate(`document.querySelector('[data-preview-tab="pdf"]').click()`);
            assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, `PDF preview overflow at ${width}px`);
            await evaluate(`document.querySelector('[data-preview-tab="website"]').click()`);
            await evaluate('new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))');
            const layout = await browserCall('Runtime.evaluate', { expression: '({width:innerWidth, scroll:document.documentElement.scrollWidth, font:parseFloat(getComputedStyle(document.querySelector(".project-card p")).fontSize)})', returnByValue: true }, websiteSession.sessionId);
            assert.ok(layout.result.value.scroll <= layout.result.value.width, `Portfolio overflow at ${width}px`);
            assert.ok(layout.result.value.font >= 17, `Portfolio text too small at ${width}px`);
            if (width === 375 || width === 1440) {
                const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
                fs.mkdirSync('storage/framework/testing/browser', { recursive: true });
                fs.writeFileSync(`storage/framework/testing/browser/builder-updated-${width}.png`, Buffer.from(shot.data, 'base64'));
            }
        }
        checks.push('responsive website and PDF preview at 320/375/768/1440px');
        await evaluate(`document.querySelector('[data-builder-step="personal"]').click()`);
        await settled('personal');
        assert.equal(await evaluate('document.querySelector("[data-domicile-province]").value'), '32');
        assert.equal(await evaluate('document.querySelector("[data-domicile-regency]").value'), 'Kota Bandung, Jawa Barat, Indonesia');
        // A failed browser Back must restore the existing history entry without overwriting it.
        await fill({ 'personal[website]': 'ftp://example.com' });
        await evaluate('history.back()');
        await wait(`document.querySelector('.error-summary') && !document.body.classList.contains('builder-loading') && location.pathname === '/builder/personal'`);
        await fill({ 'personal[website]': 'https://example.com/ayu' });
        await evaluate('history.back()');
        await settled('preview');
        assert.equal(await evaluate('location.pathname'), '/builder/preview');
        checks.push('domicile restoration and validation-safe browser history');
        await evaluate(`document.querySelector('[data-builder-step="personal"]').click()`);
        await settled('personal');
        await fill({ 'personal[bio]': 'Perubahan ketika koneksi terganggu.' });
        await evaluate(`window.__originalFetch = window.fetch; window.fetch = () => Promise.reject(new TypeError('Network error')); document.querySelector('.builder-form').requestSubmit()`);
        await wait(`document.querySelector('.error-summary') && !document.body.classList.contains('builder-loading')`);
        assert.equal(await evaluate('document.getElementsByName("personal[bio]")[0].value'), 'Perubahan ketika koneksi terganggu.');
        await evaluate('window.fetch = window.__originalFetch');
        await submit({}, 'experience');
        checks.push('network failure preserves data and retry succeeds');
        for (const width of [320, 375, 768, 1440]) {
            await send('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
            for (const step of ['template', 'personal', 'experience', 'projects']) {
                await evaluate(`document.querySelector('[data-builder-step="${step}"]').click()`);
                await settled(step);
                assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, `${step} overflow at ${width}px`);
            }
        }
        checks.push('all form steps responsive at 320/375/768/1440px');
        await send('Emulation.setScriptExecutionDisabled', { value: true });
        await send('Page.navigate', { url: `${base}/builder/personal` });
        await settled('personal');
        assert.equal(await evaluate('document.querySelector("[data-domicile-regency]").options.length'), 515);
        assert.equal(await evaluate('document.querySelector("[data-domicile-gps]").hidden'), true);
        await send('Emulation.setScriptExecutionDisabled', { value: false });
        checks.push('native domicile selection remains available without JavaScript');
        assert.equal(runtimeErrors.length, 0, JSON.stringify(runtimeErrors));
        console.log(JSON.stringify({ passed: checks, runtimeErrors: runtimeErrors.length }, null, 2));
    } finally {
        await browserCall('Target.disposeBrowserContext', { browserContextId: context.browserContextId }).catch(() => {});
        socket.close();
        browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
