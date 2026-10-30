(function ($) {
    'use strict';
    const modal = document.getElementById('adminConfirmModal');
    // Do not enable guarded forms if Bootstrap, the component or its script is unavailable.
    if (!modal || !$ || !$.fn.modal || window.AdminConfirm) return;
    const accept = modal.querySelector('[data-confirm-accept]');
    const dismiss = [...modal.querySelectorAll('[data-confirm-dismiss]')];
    const error = modal.querySelector('#adminConfirmError');
    const allowed = new WeakSet();
    const pending = new Set();
    const disabledButtons = new Map();
    let active = null;
    let loading = false;
    const types = { success: 'check-circle', primary: 'save', warning: 'pause-circle', danger: 'exclamation-circle' };

    function setLoading(value) {
        loading = !!value;
        modal.setAttribute('aria-busy', String(loading));
        accept.disabled = loading;
        dismiss.forEach(button => { button.disabled = loading; });
        accept.textContent = loading ? 'Đang xử lý…' : (active?.confirmText || 'Xác nhận');
    }

    function releaseForm(form) {
        pending.delete(form);
        delete form.dataset.confirmPending;
        (disabledButtons.get(form) || []).forEach(([button, wasDisabled]) => { button.disabled = wasDisabled; });
        disabledButtons.delete(form);
    }

    function lockButtons(form) {
        const buttons = [...form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]')];
        disabledButtons.set(form, buttons.map(button => [button, button.disabled]));
        buttons.forEach(button => { button.disabled = true; });
    }

    async function submitFetch(form, submitter) {
        if (pending.has(form)) return false;
        const body = new FormData(form);
        if (submitter?.name && submitter.form === form && !submitter.disabled) {
            body.append(submitter.name, submitter.value);
        }
        pending.add(form);
        form.dataset.confirmPending = 'true';
        lockButtons(form);
        const response = await fetch(form.action, {
            method: form.method.toUpperCase(), body, credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json().catch(() => null);
        if (!response.ok || data?.success !== true) {
            const messages = data?.errors ? Object.values(data.errors).flat().slice(0, 3).join('\n') : '';
            throw new Error(messages || data?.message || 'Không thể xác nhận kết quả từ máy chủ. Vui lòng kiểm tra trước khi thử lại.');
        }
        if (!data.redirect) throw new Error('Máy chủ chưa cung cấp trang kết quả. Vui lòng kiểm tra thao tác trước khi thử lại.');
        const redirect = new URL(data.redirect, window.location.origin);
        if (redirect.origin !== window.location.origin) throw new Error('Trang kết quả không hợp lệ.');
        window.location.assign(redirect.href);
        return true;
    }

    function open(options = {}) {
        if (active) return Promise.resolve(false);
        const type = Object.hasOwn(types, options.type) ? options.type : 'primary';
        return new Promise(resolve => {
            active = { ...options, confirmText: options.confirmText || 'Xác nhận', resolve, confirmed: false,
                opener: options.opener || document.activeElement };
            Object.keys(types).forEach(key => modal.classList.toggle('admin-confirm-' + key, key === type));
            modal.querySelector('#adminConfirmTitle').textContent = options.title || 'Xác nhận thao tác';
            modal.querySelector('#adminConfirmMessage').textContent = options.message || 'Bạn có muốn tiếp tục?';
            const subject = modal.querySelector('#adminConfirmSubject');
            subject.textContent = options.subject || '';
            subject.hidden = !options.subject;
            modal.querySelector('[data-confirm-icon]').className = 'fas fa-' + types[type];
            dismiss[1].textContent = options.cancelText || 'Hủy';
            accept.className = 'btn btn-' + type;
            error.hidden = true;
            error.textContent = '';
            setLoading(options.loading || false);
            $(modal).modal({ show: true, backdrop: true, keyboard: true });
        });
    }

    dismiss.forEach(button => button.addEventListener('click', () => {
        if (!loading) $(modal).modal('hide');
    }));
    $(modal).on('shown.bs.modal', () => { if (!loading) dismiss[1].focus(); });
    $(modal).on('hide.bs.modal', event => { if (loading) event.preventDefault(); });
    $(modal).on('hidden.bs.modal', () => {
        if (!active) return;
        const previous = active;
        active = null;
        setLoading(false);
        if (previous.opener?.isConnected) previous.opener.focus();
        previous.resolve(previous.confirmed);
    });

    accept.addEventListener('click', async () => {
        if (!active || loading) return;
        const context = active;
        setLoading(true);
        error.hidden = true;
        try {
            const result = await context.onConfirm?.();
            if (result === false) throw new Error('Thao tác chưa được gửi. Vui lòng kiểm tra lại dữ liệu.');
            context.confirmed = true;
            if (!context.nativeForm) {
                setLoading(false);
                $(modal).modal('hide');
            }
            // Native form navigation displays the real Laravel response, never a success toast here.
        } catch (exception) {
            if (context.nativeForm) releaseForm(context.nativeForm);
            setLoading(false);
            error.textContent = exception?.message || 'Không thể thực hiện thao tác. Vui lòng thử lại.';
            error.hidden = false;
            accept.focus();
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm-form]')) return;
        if (pending.has(form)) { event.preventDefault(); return; }
        if (allowed.has(form)) {
            allowed.delete(form);
            pending.add(form);
            form.dataset.confirmPending = 'true';
            return;
        }
        event.preventDefault();
        if (active || !form.reportValidity()) return;
        const submitter = event.submitter || (form.contains(document.activeElement) ? document.activeElement : null);
        open({
            title: form.dataset.confirmTitle, message: form.dataset.confirmMessage,
            type: form.dataset.confirmType, confirmText: form.dataset.confirmText,
            cancelText: form.dataset.cancelText, subject: form.dataset.confirmSubject,
            opener: submitter, nativeForm: form,
            onConfirm: () => {
                if (!form.isConnected || !form.checkValidity()) return false;
                if (form.dataset.confirmTransport === 'fetch') return submitFetch(form, submitter);
                let dispatched;
                const remember = event => { dispatched = event; };
                form.addEventListener('submit', remember, { once: true });
                allowed.add(form);
                try {
                    const validSubmitter = submitter && submitter.form === form &&
                        ((submitter instanceof HTMLButtonElement && submitter.type === 'submit') ||
                         (submitter instanceof HTMLInputElement && ['submit', 'image'].includes(submitter.type)));
                    HTMLFormElement.prototype.requestSubmit.call(form, validSubmitter ? submitter : undefined);
                } finally {
                    allowed.delete(form);
                    form.removeEventListener('submit', remember);
                }
                if (!dispatched || dispatched.defaultPrevented) return false;
                // requestSubmit has already constructed the native payload, including submitter/CSRF/_method/files.
                lockButtons(form);
                return true;
            }
        });
    }, true);

    window.AdminConfirm = { open, setLoading };
    // Register all interception before unlocking any form; partial initialization fails closed.
    document.querySelectorAll('form[data-confirm-form] fieldset[data-confirm-gate]').forEach(gate => { gate.disabled = false; });
    document.querySelectorAll('[data-confirm-unavailable]').forEach(notice => { notice.hidden = true; });
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        pending.forEach(releaseForm);
        if (active?.nativeForm) {
            setLoading(false);
            $(modal).modal('hide');
        }
    });
})(window.jQuery);
