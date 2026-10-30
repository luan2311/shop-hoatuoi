(function ($) {
    'use strict';
    const page = document.querySelector('.admin-product-page');
    if (!page) return;
    if ($.fn.popover) {
        $(page).find('[data-toggle="popover"]').popover({
            html: false,
            template: '<div class="popover an-product-reason-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') $(page).find('[data-toggle="popover"]').popover('hide');
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.product-delete-reason, .an-product-reason-popover')) {
                $(page).find('[data-toggle="popover"]').popover('hide');
            }
        });
    }
    const modal = document.getElementById('adminProductPreview');
    $(modal).on('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger || !trigger.dataset.preview) return;
        const product = JSON.parse(trigger.dataset.preview);
        modal.querySelector('[data-preview-name]').textContent = product.name;
        modal.querySelectorAll('[data-preview-value]').forEach(function (node) {
            node.textContent = product[node.dataset.previewValue] ?? '';
        });
        const image = modal.querySelector('[data-preview-image]');
        const placeholder = modal.querySelector('[data-preview-no-image]');
        image.hidden = !product.image;
        placeholder.hidden = !!product.image;
        image.alt = product.name;
        if (product.image) image.src = product.image;
        else image.removeAttribute('src');
        image.onerror = function () { image.hidden = true; placeholder.hidden = false; };
    });
    // Filters use the same GET form, so each dropdown works without dropping the keyword.
    page.querySelectorAll('.product-filter-form select').forEach(function (select) {
        select.addEventListener('change', function () { select.form.requestSubmit(); });
    });
})(window.jQuery);
