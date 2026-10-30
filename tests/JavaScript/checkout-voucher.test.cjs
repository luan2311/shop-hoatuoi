const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function checkout(fetch) {
    const ids = ['voucherInput', 'btnApplyVoucher', 'checkoutForm', 'voucherFeedback', 'appliedVoucher', 'subtotalDisplay', 'discountDisplay', 'discountRow', 'grandTotalPrice', 'mobileTotalPreview'];
    const nodes = Object.fromEntries(ids.map(id => [id, {value: '', textContent: '', style: {}, dataset: {}, listeners: {}, addEventListener(event, handler) {this.listeners[event] = handler;}}]));
    const submit = {disabled: false};
    nodes.checkoutForm.querySelectorAll = () => [submit];
    nodes.btnApplyVoucher.dataset.url = '/checkout/voucher';
    const context = {document: {getElementById: id => nodes[id], querySelector: () => ({content: 'csrf-fixture'})}, window: {}, fetch};
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/checkout-voucher.js'), 'utf8'), context);
    return {nodes, submit, window: context.window};
}

const quote = {subtotal_amount: 650000, discount_amount: 50000, total_amount: 600000, voucher_code: 'HOATUOI2026'};

test('voucher UI waits for server, sends only code with CSRF and renders the quote once', async () => {
    let resolve;
    let request;
    let calls = 0;
    const state = checkout((url, options) => {calls++; request = {url, options}; return new Promise(done => resolve = done);});
    state.nodes.voucherInput.value = 'HOATUOI2026';
    const pending = state.nodes.btnApplyVoucher.listeners.click();
    assert.equal(state.submit.disabled, true);
    assert.equal(state.nodes.grandTotalPrice.textContent, '');
    state.window.applyVoucherPreset('FREESHIP');
    assert.equal(calls, 1);
    resolve({ok: true, json: async () => quote});
    await pending;
    assert.equal(request.url, '/checkout/voucher');
    assert.equal(request.options.headers['X-CSRF-TOKEN'], 'csrf-fixture');
    assert.deepEqual(JSON.parse(request.options.body), {voucher_code: 'HOATUOI2026'});
    assert.equal(state.nodes.appliedVoucher.value, 'HOATUOI2026');
    assert.equal(state.nodes.grandTotalPrice.textContent, '600.000 đ');
    assert.equal(state.nodes.mobileTotalPreview.textContent, '600.000 đ');
    assert.equal(state.submit.disabled, false);
});

test('invalid code clears stale discount from server response and shows an error', async () => {
    const state = checkout(async () => ({ok: false, json: async () => ({subtotal_amount: 650000, discount_amount: 0, total_amount: 650000, voucher_code: null, errors: {voucher_code: ['Mã ưu đãi không hợp lệ.']}})}));
    state.nodes.appliedVoucher.value = 'HOATUOI2026';
    await state.nodes.btnApplyVoucher.listeners.click();
    assert.equal(state.nodes.appliedVoucher.value, '');
    assert.equal(state.nodes.grandTotalPrice.textContent, '650.000 đ');
    assert.equal(state.nodes.discountRow.style.display, 'none');
    assert.equal(state.nodes.voucherFeedback.textContent, 'Mã ưu đãi không hợp lệ.');
});

test('network failure never shows a fake success or changes the last verified amount', async () => {
    const state = checkout(async () => {throw new Error('Network unavailable');});
    state.nodes.grandTotalPrice.textContent = '650.000 đ';
    await state.nodes.btnApplyVoucher.listeners.click();
    assert.equal(state.nodes.grandTotalPrice.textContent, '650.000 đ');
    assert.equal(state.nodes.voucherFeedback.textContent, 'Network unavailable');
    assert.equal(state.nodes.btnApplyVoucher.disabled, false);
    assert.equal(state.submit.disabled, false);
});
