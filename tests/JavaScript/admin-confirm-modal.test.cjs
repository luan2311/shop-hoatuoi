const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/admin-confirm-modal.js'), 'utf8');

function harness(bootstrap = true) {
    class Node {
        constructor() { this.listeners = {}; this.attrs = {}; this.classes = new Set(); this.textContent = ''; this.disabled = false; this.hidden = false; this.isConnected = true; }
        addEventListener(name, fn) { (this.listeners[name] ||= []).push(fn); }
        async click() { if (!this.disabled) await Promise.all((this.listeners.click || []).map(fn => fn({ target: this }))); }
        focus() { document.activeElement = this; }
        setAttribute(name, value) { this.attrs[name] = value; }
        classList = { toggle: (name, enabled) => enabled ? this.classes.add(name) : this.classes.delete(name) };
    }
    const nodes = Object.fromEntries(['accept', 'close', 'cancel', 'error', 'title', 'message', 'subject', 'icon', 'modal', 'gate', 'notice', 'opener'].map(key => [key, new Node()]));
    nodes.gate.disabled = true;
    const selectors = { '[data-confirm-accept]': 'accept', '#adminConfirmError': 'error', '#adminConfirmTitle': 'title', '#adminConfirmMessage': 'message', '#adminConfirmSubject': 'subject', '[data-confirm-icon]': 'icon' };
    nodes.modal.querySelector = selector => nodes[selectors[selector]];
    nodes.modal.querySelectorAll = selector => selector === '[data-confirm-dismiss]' ? [nodes.close, nodes.cancel] : [];
    const document = {
        activeElement: nodes.opener,
        getElementById: () => nodes.modal,
        addEventListener() {},
        querySelectorAll: selector => selector.includes('fieldset') ? [nodes.gate] : [nodes.notice]
    };
    const events = {};
    let visible = false;
    const jq = () => ({
        on(name, fn) { events[name] = fn; return this; },
        modal(value) {
            if (value === 'hide') {
                const event = { prevented: false, preventDefault() { this.prevented = true; } };
                events['hide.bs.modal']?.(event);
                if (!event.prevented) { visible = false; events['hidden.bs.modal']?.(); }
            } else { visible = true; events['shown.bs.modal']?.(); }
            return this;
        }
    });
    jq.fn = bootstrap ? { modal() {} } : {};
    const window = { jQuery: jq, addEventListener() {} };
    vm.runInNewContext(source, { document, window, HTMLFormElement: class {}, WeakSet, Set, Map, Promise, Object, URL });
    return { api: window.AdminConfirm, nodes, visible: () => visible, focused: () => document.activeElement };
}

test('fails closed when Bootstrap is unavailable', () => {
    const h = harness(false);
    assert.equal(h.api, undefined);
    assert.equal(h.nodes.gate.disabled, true);
    assert.equal(h.nodes.notice.hidden, false);
});

test('uses one configurable modal, plain text and returns focus after cancel', async () => {
    const h = harness();
    for (const type of ['success', 'primary', 'warning', 'danger']) {
        const title = '<img src=x onerror=alert(1)>';
        let calls = 0;
        const result = h.api.open({ type, title, message: 'Nội dung', confirmText: 'Đồng ý', onConfirm: () => calls++, opener: h.nodes.opener });
        assert.equal(h.nodes.title.textContent, title);
        assert.equal(h.nodes.accept.className, 'btn btn-' + type);
        assert.equal(h.visible(), true);
        await h.nodes.cancel.click();
        assert.equal(await result, false);
        assert.equal(calls, 0);
        assert.equal(h.visible(), false);
        assert.equal(h.focused(), h.nodes.opener);
    }
    assert.equal(h.nodes.gate.disabled, false);
});

test('double click and dismiss during a pending callback execute once', async () => {
    const h = harness();
    let release;
    let calls = 0;
    const result = h.api.open({ onConfirm: () => { calls++; return new Promise(resolve => { release = resolve; }); } });
    const first = h.nodes.accept.click();
    await h.nodes.accept.click();
    await h.nodes.close.click();
    assert.equal(calls, 1);
    assert.equal(h.visible(), true);
    assert.equal(h.nodes.accept.disabled, true);
    release();
    await first;
    assert.equal(await result, true);
    assert.equal(h.visible(), false);
});

test('rejected callback stays open, shows error as text and allows safe cancel/reopen', async () => {
    const h = harness();
    const result = h.api.open({ onConfirm: () => Promise.reject(new Error('<b>503</b>')) });
    await h.nodes.accept.click();
    assert.equal(h.visible(), true);
    assert.equal(h.nodes.error.textContent, '<b>503</b>');
    assert.equal(h.nodes.accept.disabled, false);
    await h.nodes.close.click();
    assert.equal(await result, false);
    const next = h.api.open({ title: 'Lần sau' });
    assert.equal(h.nodes.error.hidden, true);
    await h.nodes.cancel.click();
    assert.equal(await next, false);
});
