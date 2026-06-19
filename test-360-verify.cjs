const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 900 });

    const consoleErrors = [];
    const pageErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', (err) => pageErrors.push(err.message));
    page.on('requestfailed', (req) => {
        console.log('REQUEST FAILED:', req.url(), req.failure()?.errorText);
    });

    console.log('--- STEP 1: navigate to /test-360-viewer ---');
    const resp = await page.goto('http://127.0.0.1:8421/test-360-viewer', { waitUntil: 'networkidle0', timeout: 15000 });
    console.log('HTTP status:', resp.status());

    await page.screenshot({ path: 'test-360-step1-initial.png' });
    console.log('Screenshot saved: test-360-step1-initial.png');

    console.log('--- STEP 2: click "Buka Preview 360" button ---');
    await page.click('#btn-open-360-test');

    // give the modal transition + PSV init (setTimeout 50ms) + WebGL render time to settle
    await new Promise((r) => setTimeout(r, 1500));

    const modalVisible = await page.evaluate(() => {
        const modal = document.getElementById('hs-modal-360-test');
        return modal ? modal.classList.contains('open') || getComputedStyle(modal).display !== 'none' : false;
    });
    console.log('Modal element display !== none:', modalVisible);

    const viewerHasCanvas = await page.evaluate(() => {
        const el = document.getElementById('viewer-360-test');
        return el ? el.querySelector('canvas') !== null : false;
    });
    console.log('Canvas rendered inside viewer container:', viewerHasCanvas);

    await page.screenshot({ path: 'test-360-step2-modal-open.png' });
    console.log('Screenshot saved: test-360-step2-modal-open.png');

    console.log('--- STEP 3: simulate drag on viewer ---');
    const box = await page.evaluate(() => {
        const el = document.getElementById('viewer-360-test');
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, width: r.width, height: r.height };
    });
    const startX = box.x + box.width / 2;
    const startY = box.y + box.height / 2;
    await page.mouse.move(startX, startY);
    await page.mouse.down();
    await page.mouse.move(startX - 250, startY, { steps: 15 });
    await page.mouse.up();
    await new Promise((r) => setTimeout(r, 300));

    await page.screenshot({ path: 'test-360-step3-after-drag.png' });
    console.log('Screenshot saved: test-360-step3-after-drag.png');

    console.log('--- CONSOLE ERRORS ---');
    console.log(consoleErrors.length ? consoleErrors : '(none)');
    console.log('--- PAGE (JS) ERRORS ---');
    console.log(pageErrors.length ? pageErrors : '(none)');

    await browser.close();
})();
