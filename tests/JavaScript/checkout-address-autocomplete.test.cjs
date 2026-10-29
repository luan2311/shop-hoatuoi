const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function createMockElement(tag = 'div') {
    const listeners = {};
    const classes = new Set();
    const children = [];
    const attrs = {};
    const element = {
        tagName: tag.toUpperCase(),
        value: '',
        textContent: '',
        innerHTML: '',
        style: {},
        parentNode: null,
        children,
        classList: {
            add: (c) => classes.add(c),
            remove: (c) => classes.delete(c),
            contains: (c) => classes.has(c),
        },
        get className() { return Array.from(classes).join(' '); },
        set className(v) {
            classes.clear();
            if (typeof v === 'string') {
                v.split(' ').forEach((c) => { if (c.trim()) classes.add(c.trim()); });
            }
        },
        setAttribute: (k, v) => {
            attrs[k] = v;
            if (k === 'class' && typeof v === 'string') {
                classes.clear();
                v.split(' ').forEach((c) => { if (c.trim()) classes.add(c.trim()); });
            }
        },
        getAttribute: (k) => attrs[k] !== undefined ? attrs[k] : null,
        addEventListener: (type, callback) => {
            if (!listeners[type]) listeners[type] = [];
            listeners[type].push(callback);
        },
        dispatchEvent: (event) => {
            const handlers = listeners[event.type] || [];
            handlers.forEach((fn) => fn.call(element, event));
        },
        appendChild: (child) => {
            child.parentNode = element;
            children.push(child);
            return child;
        },
        removeChild: (child) => {
            const idx = children.indexOf(child);
            if (idx > -1) {
                children.splice(idx, 1);
                child.parentNode = null;
            }
            return child;
        },
        querySelector: (selector) => {
            if (selector === '.autocomplete-items') {
                return children.find((c) => c.classList && c.classList.contains('autocomplete-items')) || null;
            }
            return null;
        },
        getElementsByTagName: (tagName) => {
            const lower = tagName.toLowerCase();
            return children.filter((c) => c.tagName.toLowerCase() === lower);
        },
        scrollIntoView: () => {},
        click: () => {
            const handlers = listeners['click'] || [];
            handlers.forEach((fn) => fn.call(element, { type: 'click', stopPropagation: () => {} }));
        },
    };
    return element;
}

function loadCheckoutContext() {
    const bladePath = path.join(__dirname, '../../resources/views/cart/checkout.blade.php');
    const bladeContent = fs.readFileSync(bladePath, 'utf8');
    const scriptMatch = bladeContent.match(/<script>([\s\S]*?)<\/script>/);
    assert.ok(scriptMatch, 'Script block must exist in checkout.blade.php');

    let scriptText = scriptMatch[1];
    // Replace Blade interpolation
    scriptText = scriptText.replace(/\{\{\s*\(int\)\s*\(\$total\s*\?\?\s*0\)\s*\}\}/g, '700000');

    const documentListeners = {};
    const elementsById = {};

    const mockDocument = {
        createElement: (tag) => createMockElement(tag),
        getElementById: (id) => {
            if (!elementsById[id]) {
                const el = createMockElement('input');
                el.id = id;
                const parent = createMockElement('div');
                parent.classList.add('input-with-icon');
                parent.appendChild(el);
                elementsById[id] = el;
            }
            return elementsById[id];
        },
        getElementsByClassName: (className) => {
            const results = [];
            Object.values(elementsById).forEach((el) => {
                if (el.parentNode) {
                    el.parentNode.children.forEach((c) => {
                        if (c.classList && c.classList.contains(className)) {
                            results.push(c);
                        }
                    });
                }
            });
            return results;
        },
        querySelectorAll: () => [],
        addEventListener: (type, callback) => {
            if (!documentListeners[type]) documentListeners[type] = [];
            documentListeners[type].push(callback);
        },
    };

    const sandbox = {
        document: mockDocument,
        window: { document: mockDocument },
        Event: function(type, opts) { this.type = type; this.bubbles = !!(opts && opts.bubbles); },
        alert: () => {},
        navigator: { clipboard: null },
        console,
    };

    vm.createContext(sandbox);
    vm.runInContext(scriptText, sandbox);

    return { sandbox, documentListeners, elementsById };
}

test('checkout script parses and loads without syntax errors', () => {
    const { sandbox } = loadCheckoutContext();
    assert.equal(typeof sandbox.setupAutocomplete, 'function');
    assert.equal(typeof sandbox.removeVietnameseTones, 'function');
    assert.ok(Array.isArray(sandbox.tinhThanhData));
    assert.equal(sandbox.tinhThanhData.length, 63, 'Must include all 63 Vietnamese provinces/cities');
});

test('removeVietnameseTones correctly strips Vietnamese diacritics', () => {
    const { sandbox } = loadCheckoutContext();
    assert.equal(sandbox.removeVietnameseTones('Đà Nẵng'), 'da nang');
    assert.equal(sandbox.removeVietnameseTones('Hà Nội'), 'ha noi');
    assert.equal(sandbox.removeVietnameseTones('TP Hồ Chí Minh'), 'tp ho chi minh');
});

test('typing 1 character shows matching dropdown suggestions (accent & accentless)', () => {
    const { sandbox, elementsById } = loadCheckoutContext();
    const input = sandbox.document.getElementById('TinhThanh');
    sandbox.setupAutocomplete(input, sandbox.tinhThanhData);

    // 1. Type 1 character "h" -> should show dropdown
    input.value = 'h';
    input.dispatchEvent(new sandbox.Event('input'));

    let dropdown = input.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown, 'Dropdown must appear when 1 character "h" is typed');
    assert.ok(dropdown.children.length > 0, 'Dropdown must have suggestions');
    const texts = dropdown.children.map((c) => c.getAttribute('data-value'));
    assert.ok(texts.includes('TP Hồ Chí Minh') || texts.includes('Hà Nội'));

    // 2. Type 1 character "d" -> should match "Đà Nẵng" (accent-insensitive)
    input.value = 'd';
    input.dispatchEvent(new sandbox.Event('input'));

    dropdown = input.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown, 'Dropdown must appear when 1 character "d" is typed');
    const dTexts = dropdown.children.map((c) => c.getAttribute('data-value'));
    assert.ok(dTexts.includes('Đà Nẵng'), 'Typing "d" must match "Đà Nẵng"');
});

test('selecting an item updates input value and dispatches change event', () => {
    const { sandbox } = loadCheckoutContext();
    const input = sandbox.document.getElementById('TinhThanh');
    sandbox.setupAutocomplete(input, sandbox.tinhThanhData);

    let changeFired = false;
    input.addEventListener('change', () => { changeFired = true; });

    input.value = 'da';
    input.dispatchEvent(new sandbox.Event('input'));

    const dropdown = input.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown && dropdown.children.length > 0);

    const firstItem = dropdown.children[0];
    firstItem.click();

    assert.equal(input.value, firstItem.getAttribute('data-value'));
    assert.equal(changeFired, true, 'Change event must be dispatched upon selection');
    assert.equal(input.parentNode.querySelector('.autocomplete-items'), null, 'Dropdown must close after selection');
});

test('keyboard arrow down and enter selects highlighted option', () => {
    const { sandbox } = loadCheckoutContext();
    const input = sandbox.document.getElementById('TinhThanh');
    sandbox.setupAutocomplete(input, sandbox.tinhThanhData);

    input.value = 'can';
    input.dispatchEvent(new sandbox.Event('input'));

    const dropdown = input.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown && dropdown.children.length > 0);

    // Arrow down (keyCode 40)
    input.dispatchEvent({ type: 'keydown', keyCode: 40, preventDefault: () => {} });
    assert.ok(dropdown.children[0].classList.contains('autocomplete-active'), 'First item should be active');

    // Enter (keyCode 13)
    input.dispatchEvent({ type: 'keydown', keyCode: 13, preventDefault: () => {} });
    assert.equal(input.value, 'Cần Thơ', 'Enter must select active item');
});

test('focusing or clicking input displays suggestion list', () => {
    const { sandbox } = loadCheckoutContext();
    const input = sandbox.document.getElementById('TinhThanh');
    sandbox.setupAutocomplete(input, sandbox.tinhThanhData);

    input.value = '';
    input.dispatchEvent(new sandbox.Event('focus'));

    const dropdown = input.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown, 'Dropdown should appear on focus');
    assert.ok(dropdown.children.length > 0, 'Should display initial popular suggestions');
});

test('cascading updates available districts when province changes', () => {
    const { sandbox, documentListeners } = loadCheckoutContext();
    const tinhInput = sandbox.document.getElementById('TinhThanh');
    const quanInput = sandbox.document.getElementById('QuanHuyen');

    // Trigger DOMContentLoaded
    const domLoadedHandlers = documentListeners['DOMContentLoaded'] || [];
    domLoadedHandlers.forEach((fn) => fn());

    // Initially QuanHuyen has TP Hồ Chí Minh districts
    quanInput.value = '';
    quanInput.dispatchEvent(new sandbox.Event('focus'));
    let dropdown = quanInput.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown);
    let items = dropdown.children.map((c) => c.getAttribute('data-value'));
    assert.ok(items.includes('Quận 1'));

    // Change province to Hà Nội
    tinhInput.value = 'Hà Nội';
    tinhInput.dispatchEvent(new sandbox.Event('change'));

    assert.equal(quanInput.value, '', 'District should be reset when province changes');

    quanInput.value = '';
    quanInput.dispatchEvent(new sandbox.Event('focus'));
    dropdown = quanInput.parentNode.querySelector('.autocomplete-items');
    assert.ok(dropdown);
    items = dropdown.children.map((c) => c.getAttribute('data-value'));
    assert.ok(items.includes('Ba Đình'), 'Hà Nội districts should now be available');
    assert.ok(!items.includes('Quận 1'), 'TP HCM districts should no longer be listed');
});

test('Escape key and outside click close the dropdown', () => {
    const { sandbox } = loadCheckoutContext();
    const input = sandbox.document.getElementById('TinhThanh');
    sandbox.setupAutocomplete(input, sandbox.tinhThanhData);

    input.value = 'ha';
    input.dispatchEvent(new sandbox.Event('input'));
    assert.ok(input.parentNode.querySelector('.autocomplete-items'));

    // Press Escape (keyCode 27)
    input.dispatchEvent({ type: 'keydown', keyCode: 27, preventDefault: () => {} });
    assert.equal(input.parentNode.querySelector('.autocomplete-items'), null);

    // Reopen
    input.dispatchEvent(new sandbox.Event('input'));
    assert.ok(input.parentNode.querySelector('.autocomplete-items'));

    // Outside click
    sandbox.closeAllLists({});
    assert.equal(input.parentNode.querySelector('.autocomplete-items'), null);
});
