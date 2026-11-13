const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

let playwright;
try { playwright = require(process.env.PLAYWRIGHT_MODULE_PATH || 'playwright'); } catch (_) {}
const options = { skip: !playwright && 'Install Playwright or set PLAYWRIGHT_MODULE_PATH' };
const headClient = fs.readFileSync(path.join(__dirname, '../../resources/views/head/client.blade.php'), 'utf8');
const appearance = fs.readFileSync(path.join(__dirname, '../../resources/views/utilities/concurrentUri/appearance.blade.php'), 'utf8');
let browser;
test.before(async () => {
    if (playwright) browser = await playwright.chromium.launch({
        headless: true,
        ...(process.env.PLAYWRIGHT_CHANNEL ? { channel: process.env.PLAYWRIGHT_CHANNEL } : {}),
    });
});
test.after(async () => { if (browser) await browser.close(); });

const originalHead = '<title>Commessa di prova</title>'
    + '<link rel="icon" type="image/x-icon" sizes="16x16" href="/normal.ico">'
    + '<link rel="icon" type="image/png" sizes="32x32" href="/normal.png">'
    + '<link rel="apple-touch-icon" sizes="180x180" href="/touch.png">'
    + '<link rel="canonical" href="https://template.test/order">'
    + '<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">'
    + '<meta name="theme-color" content="#111111" media="(prefers-color-scheme: dark)">';

async function fixture(t, { metadata = originalHead, config = {}, initialState = 'checking' } = {}) {
    const context = await browser.newContext();
    t.after(() => context.close());
    const errors = [];
    function html(state) {
        return '<!doctype html><html><head>' + metadata + headClient + '</head><body><script>'
            + 'window.fixtureState=' + JSON.stringify(state) + ';'
            + 'window.ibConcurrentUri={state:()=>window.fixtureState,isOwner:()=>window.fixtureState==="owner"};'
            + 'window.ibConcurrentUriAppearanceConfig=' + JSON.stringify(config) + ';'
            + 'window.changeState=state=>{window.fixtureState=state;window.dispatchEvent(new CustomEvent("ib-concurrent-uri-state",{detail:{state}}));};'
            + '</script>' + appearance + '</body></html>';
    }
    await context.route('**/*', route => {
        const url = new URL(route.request().url());
        if (url.pathname === '/page' || url.pathname === '/frame') {
            return route.fulfill({ contentType: 'text/html', body: html(url.pathname === '/frame' ? 'conflict' : initialState) });
        }
        return route.fulfill({ status: 204, body: '' });
    });
    const page = await context.newPage();
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('https://template.test/page');
    t.after(() => assert.deepEqual(errors, []));
    return page;
}

async function metadata(page) {
    return page.evaluate(() => ({
        icons: Array.from(document.querySelectorAll('link[rel~="icon"]'), node => node.outerHTML),
        themes: Array.from(document.querySelectorAll('meta[name="theme-color"]'), node => node.outerHTML),
        touch: document.querySelector('link[rel="apple-touch-icon"]')?.outerHTML,
        canonical: document.querySelector('link[rel="canonical"]')?.outerHTML,
        title: document.title,
    }));
}

test('checking preserves the original icon and theme; conflicts restore them exactly after recovery', options, async t => {
    const page = await fixture(t);
    const original = await metadata(page);
    await page.evaluate(() => changeState('owner'));
    assert.deepEqual(await metadata(page), original);
    await page.evaluate(() => changeState('conflict'));
    const warningIcon = await page.locator('link[rel="icon"]').getAttribute('href');
    assert.match(warningIcon, /^data:image\/png;base64,/);
    assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'), '#facc15');
    assert.deepEqual(await page.evaluate(async () => {
        const image = new Image();
        image.src = document.querySelector('link[rel="icon"]').href;
        await image.decode();
        return [image.naturalWidth, image.naturalHeight];
    }), [32, 32]);
    await page.evaluate(() => { changeState('conflict'); changeState('checking'); });
    assert.equal(await page.locator('link[rel="icon"]').getAttribute('href'), warningIcon);
    await page.evaluate(() => changeState('owner'));
    assert.deepEqual(await metadata(page), original);
    await page.evaluate(() => { changeState('conflict'); changeState('owner'); });
    assert.deepEqual(await metadata(page), original);
});

test('offline and expired pages show the warning without leaving generated tags after recovery', options, async t => {
    const page = await fixture(t, { metadata: '<title>Page</title>' });
    for (const state of ['offline', 'expired']) {
        await page.evaluate(state => changeState(state), state);
        assert.equal(await page.locator('link[rel="icon"]').count(), 1);
        assert.equal(await page.locator('meta[name="theme-color"]').count(), 1);
        await page.evaluate(() => changeState('owner'));
        assert.equal(await page.locator('link[rel="icon"]').count(), 0);
        assert.equal(await page.locator('meta[name="theme-color"]').count(), 0);
    }
});

test('custom warning icons and theme color are applied when the listener loads after a conflict', options, async t => {
    const page = await fixture(t, {
        initialState: 'conflict',
        config: { warningFavicon: { path: '/warning.svg', type: 'image/svg+xml', sizes: 'any' }, warningThemeColor: '#eab308' },
    });
    const icon = page.locator('link[rel="icon"]');
    assert.equal(await icon.getAttribute('href'), '/warning.svg');
    assert.equal(await icon.getAttribute('type'), 'image/svg+xml');
    assert.equal(await icon.getAttribute('sizes'), 'any');
    assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'), '#eab308');
});

test('the head helper changes only favicon and theme tags and supports their removal and restoration', options, async t => {
    const page = await fixture(t);
    const original = await metadata(page);
    await page.evaluate(() => {
        ibHeadMetadata.setFavicon('/custom.ico');
        ibHeadMetadata.setThemeColor('#123456');
    });
    assert.equal(await page.locator('link[rel="icon"]').getAttribute('href'), '/custom.ico');
    const custom = await metadata(page);
    assert.equal(custom.touch, original.touch);
    assert.equal(custom.canonical, original.canonical);
    assert.equal(custom.title, original.title);
    await page.evaluate(() => { ibHeadMetadata.setFavicon(null); ibHeadMetadata.setThemeColor(null); });
    assert.equal(await page.locator('link[rel="icon"]').count(), 0);
    assert.equal(await page.locator('meta[name="theme-color"]').count(), 0);
    await page.evaluate(() => { ibHeadMetadata.restoreFavicon(); ibHeadMetadata.restoreThemeColor(); });
    assert.deepEqual(await metadata(page), original);
});

test('iframe states do not change the browser indicators of their owning tab', options, async t => {
    const page = await fixture(t);
    const original = await metadata(page);
    const loaded = page.waitForEvent('framenavigated', frame => frame.name() === 'child');
    await page.evaluate(() => {
        const frame = document.createElement('iframe');
        frame.name = 'child';
        frame.src = '/frame';
        document.body.appendChild(frame);
    });
    await loaded;
    const frame = page.frame({ name: 'child' });
    await frame.waitForLoadState('load');
    assert.equal(await frame.evaluate(() => window.ibConcurrentUriAppearance), undefined);
    assert.deepEqual(await metadata(frame), original);
    assert.deepEqual(await metadata(page), original);
});
