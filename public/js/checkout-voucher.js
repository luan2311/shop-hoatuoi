(() => {
    const input = document.getElementById('voucherInput');
    const button = document.getElementById('btnApplyVoucher');
    const form = document.getElementById('checkoutForm');
    if (!input || !button || !form) return;

    let pending = false;
    const money = amount => Number(amount).toLocaleString('vi-VN') + ' đ';
    const feedback = document.getElementById('voucherFeedback');

    function render(quote) {
        document.getElementById('appliedVoucher').value = quote.voucher_code || '';
        document.getElementById('subtotalDisplay').textContent = money(quote.subtotal_amount);
        document.getElementById('discountDisplay').textContent = '- ' + money(quote.discount_amount);
        document.getElementById('discountRow').style.display = quote.voucher_code ? 'flex' : 'none';
        document.getElementById('grandTotalPrice').textContent = money(quote.total_amount);
        document.getElementById('mobileTotalPreview').textContent = money(quote.total_amount);
    }

    async function apply(code) {
        if (pending) return;
        pending = true;
        input.readOnly = true;
        button.disabled = true;
        const submitButtons = [...form.querySelectorAll('[type="submit"]')];
        const previousDisabled = submitButtons.map(element => element.disabled);
        submitButtons.forEach(element => element.disabled = true);
        feedback.textContent = 'Đang xác minh mã ưu đãi...';
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({voucher_code: code}),
            });
            const quote = await response.json();
            if (Number.isSafeInteger(quote.total_amount)) render(quote);
            if (!response.ok) throw new Error(quote.errors?.voucher_code?.[0] || quote.message || 'Không thể áp dụng mã ưu đãi.');
            input.value = quote.voucher_code || '';
            feedback.style.color = '#16a34a';
            feedback.textContent = quote.voucher_code
                ? (quote.voucher_code === 'FREESHIP'
                    ? '✓ Áp dụng mã FREESHIP: Phí giao hàng hiện đã miễn phí.'
                    : '✓ Áp dụng mã ' + quote.voucher_code + ' thành công: Giảm ' + money(quote.discount_amount))
                : 'Đã bỏ mã ưu đãi.';
        } catch (error) {
            feedback.style.color = '#dc2626';
            feedback.textContent = error.message || 'Không thể xác minh mã. Vui lòng thử lại.';
        } finally {
            pending = false;
            input.readOnly = false;
            button.disabled = false;
            submitButtons.forEach((element, index) => element.disabled = previousDisabled[index]);
        }
    }

    button.addEventListener('click', () => apply(input.value));
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            apply(input.value);
        }
    });
    window.applyVoucherPreset = code => {
        if (pending) return;
        input.value = code;
        apply(code);
    };
})();
