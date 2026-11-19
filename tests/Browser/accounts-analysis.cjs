// Run against an isolated, migrated database and headless Chrome with CDP enabled.
// node tests/Browser/accounts-analysis.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.PORTOMATIS_BASE_URL || 'http://127.0.0.1:8011';
const cdp = process.env.PORTOMATIS_CDP_URL || 'http://127.0.0.1:9224';

(async () => {
    const info = await (await fetch(`${cdp}/json/version`)).json();
    const socket = new WebSocket(info.webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let sequence = 0;
    const pending = new Map();
    const errors = [];
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
        if (!pending.has(message.id)) return;
        const { resolve, reject } = pending.get(message.id);
        pending.delete(message.id);
        message.error ? reject(new Error(JSON.stringify(message.error))) : resolve(message.result);
    });
    const call = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
        const id = ++sequence;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params, sessionId }));
    });
    const context = await call('Target.createBrowserContext');
    const target = await call('Target.createTarget', { url: 'about:blank', browserContextId: context.browserContextId });
    const { sessionId } = await call('Target.attachToTarget', { targetId: target.targetId, flatten: true });
    const send = (method, params = {}) => call(method, params, sessionId);
    const evaluate = async expression => {
        const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
        return result.result.value;
    };
    const wait = async expression => {
        for (let i = 0; i < 250; i++) {
            if (await evaluate(expression)) return;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        throw new Error(`Timed out at ${await evaluate('location.href')}: ${expression}`);
    };
    const page = async (path, condition) => {
        await send('Page.navigate', { url: base + path });
        await wait(`document.readyState === 'complete' && (${condition})`);
    };
    const fill = values => evaluate(`(() => {
        for (const [name, value] of Object.entries(${JSON.stringify(values)})) {
            const input = document.getElementsByName(name)[0];
            if (!input) throw new Error('Missing input: '+name);
            if (input.type === 'checkbox') input.checked = !!value; else input.value = value;
            input.dispatchEvent(new Event('input', { bubbles:true }));
            input.dispatchEvent(new Event('change', { bubbles:true }));
        }
    })()`);
    const settled = step => wait(`document.readyState === 'complete' && document.querySelector('[data-current-step="${step}"]')?.dataset.historyStep && !document.body.classList.contains('builder-loading')`);
    const formStep = async (values, step) => {
        await fill(values);
        await evaluate(`document.querySelector('.step-panel.active .builder-form').requestSubmit()`);
        await settled(step);
        assert.equal(await evaluate('window.__sameDocument'), 'retained');
    };
    const screenshot = async name => {
        const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
        fs.mkdirSync('storage/framework/testing/browser', { recursive: true });
        fs.writeFileSync(`storage/framework/testing/browser/${name}.png`, Buffer.from(shot.data, 'base64'));
    };
    const responsive = async prefix => {
        for (const width of [320, 375, 768, 1440]) {
            await send('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
            if (prefix === 'accounts-analysis') await evaluate(`document.querySelector('.insights-section').scrollIntoView({ behavior: 'instant', block: 'start' })`);
            await evaluate('new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))');
            if (!await evaluate('document.documentElement.scrollWidth <= innerWidth')) {
                console.error(await evaluate(`Array.from(document.querySelectorAll('body *')).filter(el => el.getBoundingClientRect().right > innerWidth + 1 && getComputedStyle(el).position !== 'absolute').slice(0,15).map(el => ({tag:el.tagName, class:el.className, width:el.getBoundingClientRect().width, text:el.textContent.slice(0,60)}))`));
                await screenshot(`${prefix}-overflow`);
            }
            assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, `${prefix}: overflow at ${width}`);
            if ([375, 1440].includes(width)) await screenshot(`${prefix}-${width}`);
        }
    };
    const checks = [];
    try {
        await send('Page.enable');
        await send('Runtime.enable');
        await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
        await page('/builder', `location.pathname === '/login' && document.querySelector('#email')`);
        await responsive('accounts-login');
        await page('/register', `document.querySelector('[name="consent"]')`);
        const email = `qa-${Date.now()}@example.com`;
        await fill({ name: 'Ayu QA', email, password: 'rahasia123', password_confirmation: 'rahasia123', consent: true });
        await evaluate(`document.querySelector('.auth-form').requestSubmit()`);
        await settled('template');
        await evaluate(`window.__sameDocument = 'retained'; document.querySelector('.template-use[value="web"]').click()`);
        await settled('personal');
        await formStep({ 'personal[name]': 'Ayu Portfolio QA', 'personal[role]': 'Web Developer', 'personal[email]': 'portfolio-qa@example.com',
            'personal[bio]': 'Saya membangun website yang responsif dan mudah digunakan. Pengalaman saya mencakup pengelolaan konten serta pengembangan antarmuka.',
            'personal[phone]': '08123456789' }, 'experience');
        await formStep({ 'education[0][school]': 'Universitas QA', 'education[0][degree]': 'Informatika', 'education[0][period]': '2020–2024',
            'experience[0][company]': 'Studio QA', 'experience[0][role]': 'Mengelola website', 'experience[0][period]': '2024–2026',
            'experience[0][description]': 'Saya membangun website dengan HTML, CSS, dan JavaScript untuk meningkatkan kecepatan akses dan kemudahan penggunaan oleh pelanggan.' }, 'projects');
        await formStep({ skills: 'Canva, Figma, HTML, CSS, JavaScript', 'projects[0][title]': 'Website QA',
            'projects[0][description]': 'Saya merancang dan membangun website dengan HTML, CSS, JavaScript, dan Figma. Hasil proyek meningkatkan akses informasi bagi pengguna.',
            'projects[0][url]': 'https://example.com/qa' }, 'preview');
        assert.equal(await evaluate(`document.querySelector('.score-ring > span').textContent.startsWith('96%')`), true);
        assert.match(await evaluate(`document.querySelector('.insights-section').innerText`), /Front-end Developer/);
        await evaluate(`document.querySelector('.insights-section').scrollIntoView()`);
        await responsive('accounts-analysis');
        const pdf = await evaluate(`fetch('/builder/download/pdf').then(async r => ({status:r.status, signature:new TextDecoder().decode((await r.arrayBuffer()).slice(0,5))}))`);
        assert.deepEqual(pdf, { status:200, signature:'%PDF-' });
        checks.push('registration, template continuation, persistent wizard, live analysis, SAW score and real PDF');
        await evaluate(`document.querySelector('[data-builder-step="template"]').click()`);
        await settled('template');
        await evaluate(`document.querySelector('.template-use[value="graphic"]').click()`);
        await settled('preview');
        assert.equal(await evaluate(`document.querySelector('#preview-website iframe').srcdoc.includes('theme-graphic')`), true);
        assert.match(await evaluate(`document.querySelector('.insights-section').innerText`), /Front-end Developer/);
        await evaluate(`document.querySelector('form[action$="/builder/publish"]').requestSubmit()`);
        await wait(`document.readyState === 'complete' && document.querySelector('#share-url')`);
        const shareUrl = await evaluate(`document.querySelector('#share-url').value`);
        await evaluate(`document.querySelector('form[action$="/logout"]').requestSubmit()`);
        await wait(`document.readyState === 'complete' && location.pathname === '/login'`);
        const publicContent = await evaluate(`fetch(${JSON.stringify(shareUrl)}).then(r => r.text())`);
        assert.match(publicContent, /Ayu Portfolio QA/);
        assert.ok(!publicContent.includes(email));
        await fill({email, password:'rahasia123'});
        await evaluate(`document.querySelector('.auth-form').requestSubmit()`);
        await settled('template');
        await evaluate(`document.querySelector('.template-use[value="web"]').click()`);
        await settled('preview');
        assert.match(await evaluate(`document.querySelector('#preview-website iframe').srcdoc`), /Ayu Portfolio QA/);
        const forbidden = await evaluate(`fetch('/admin', {headers:{Accept:'application/json'}}).then(r=>r.status)`);
        assert.equal(forbidden, 403);
        checks.push('template switching retains data, publication works, logout/login restores draft, member admin access denied');
        await evaluate(`document.querySelector('form[action$="/logout"]').requestSubmit()`);
        await wait(`document.readyState === 'complete' && location.pathname === '/login'`);
        await fill({email:'admin@gmail.com', password:'admin123'});
        await evaluate(`document.querySelector('.auth-form').requestSubmit()`);
        await wait(`document.readyState === 'complete' && location.pathname === '/admin' && document.querySelector('.stats-grid')`);
        assert.match(await evaluate('document.body.innerText'), new RegExp(email.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
        assert.match(await evaluate('document.body.innerText'), /portfolio-qa@example.com/);
        await responsive('accounts-admin');
        await page('/admin?q=not-found-qa-user', `document.body.innerText.includes('Pengguna yang dicari tidak ada.')`);
        await page('/admin?q=portfolio-qa%40example.com', `document.querySelector('.account-table')`);
        await evaluate(`document.querySelector('.table-link').click()`);
        await wait(`document.readyState === 'complete' && document.querySelector('.portfolio-detail')`);
        const detail = await evaluate('document.body.innerText');
        for (const value of [email, 'portfolio-qa@example.com', 'Studio QA', 'Universitas QA', 'Website QA', 'Figma']) assert.ok(detail.includes(value), value);
        await responsive('accounts-admin-detail');
        checks.push('admin statistics, separated emails, account/portfolio search, missing state and full user detail');
        assert.equal(errors.length, 0, JSON.stringify(errors));
        console.log(JSON.stringify({ passed:checks, runtimeErrors:errors.length }, null, 2));
    } finally {
        await call('Target.disposeBrowserContext', { browserContextId:context.browserContextId }).catch(()=>{});
        socket.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
