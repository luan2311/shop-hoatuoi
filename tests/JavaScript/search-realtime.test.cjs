const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function harness() {
    const listeners = {};
    const documentListeners = {};
    const input = { value: '', addEventListener: (type, callback) => { listeners[type] = callback; } };
    const results = { hidden: true, children: [], textContent: '',
        replaceChildren() { this.children = []; this.textContent = ''; },
        append(node) { this.children.push(node); } };
    const form = { dataset: { endpoint: '/api/products/search' },
        querySelector: (selector) => selector === 'input[name="q"]' ? input : results,
        contains: (target) => target === input };
    let now = 0;
    let nextTimer = 0;
    const timers = new Map();
    const requests = [];
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/search-realtime.js'), 'utf8'), {
        URL, AbortController, window: { location: { origin: 'http://localhost' } },
        document: { querySelectorAll: () => [form], createElement: () => ({}),
            addEventListener: (type, callback) => { documentListeners[type] = callback; } },
        setTimeout: (callback, delay) => { const id = ++nextTimer; timers.set(id, { callback, due: now + delay }); return id; },
        clearTimeout: (id) => timers.delete(id),
        fetch: (url, options) => new Promise((resolve, reject) => { requests.push({ url, options, resolve, reject }); }),
    });
    return { results, requests,
        type(value) { input.value = value; listeners.input(); },
        tick(ms) { now += ms; for (const [id, timer] of timers) { if (timer.due <= now) { timers.delete(id); timer.callback(); } } },
        escape() { listeners.keydown({ key: 'Escape' }); },
        outside() { documentListeners.click({ target: {} }); },
    };
}

async function respond(request, products) {
    request.resolve({ ok: true, json: async () => ({ products }) });
    await new Promise(setImmediate);
}

test('debounces 300 ms, aborts old request, and ignores a late response', async () => {
    const h = harness();
    h.type('h'); h.tick(299);
    assert.equal(h.requests.length, 0);
    h.type('hoa'); h.tick(299);
    assert.equal(h.requests.length, 0);
    h.tick(1);
    assert.equal(h.requests.length, 1);
    assert.equal(h.requests[0].url.searchParams.get('q'), 'hoa');
    h.type('tulip');
    assert.equal(h.requests[0].options.signal.aborted, true);
    h.tick(300);
    await respond(h.requests[1], [{ name: 'Tulip', formatted_price: '1 đ', slug: 'tulip' }]);
    await respond(h.requests[0], [{ name: 'Old result', formatted_price: '1 đ', slug: 'old' }]);
    assert.equal(h.results.children[0].textContent, 'Tulip — 1 đ');
    assert.equal(h.results.hidden, false);
});

test('renders untrusted names as text and URL-encodes slugs', async () => {
    const h = harness();
    h.type('<img>'); h.tick(300);
    await respond(h.requests[0], [{ name: '<img onerror=alert(1)>', formatted_price: '2 đ', slug: 'bad/"slug' }]);
    const link = h.results.children[0];
    assert.equal(link.textContent, '<img onerror=alert(1)> — 2 đ');
    assert.equal(link.innerHTML, undefined);
    assert.equal(link.href, '/products/bad%2F%22slug');
});

test('empty query, Escape and outside clicks cancel pending results', async () => {
    const h = harness();
    h.type('hoa'); h.tick(300); h.type(' ');
    await respond(h.requests[0], [{ name: 'Late', slug: 'late' }]);
    assert.equal(h.results.hidden, true);
    h.type('hoa'); h.escape(); h.tick(300);
    assert.equal(h.requests.length, 1);
    h.type('hoa'); h.tick(300); h.outside();
    await respond(h.requests[1], [{ name: 'Late', slug: 'late' }]);
    assert.equal(h.results.hidden, true);
});

test('shows empty and failure states without injecting server content', async () => {
    const h = harness();
    h.type('none'); h.tick(300); await respond(h.requests[0], []);
    assert.equal(h.results.textContent, 'Không tìm thấy sản phẩm.');
    h.type('failure'); h.tick(300);
    h.requests[1].resolve({ ok: false });
    await new Promise(setImmediate);
    assert.equal(h.results.textContent, 'Chưa thể tìm kiếm. Vui lòng thử lại.');
});
