/**
 * Cart AJAX: Thêm sản phẩm vào giỏ hàng và đồng bộ số lượng giỏ hàng trên header ngay lập tức
 */
(function ($) {
    'use strict';

    /**
     * Cập nhật số lượng giỏ hàng hiển thị trên header
     */
    function updateCartCount(count) {
        var num = parseInt(count, 10);
        if (isNaN(num) || num < 0) {
            num = 0;
        }

        var $counts = $('.js-cart-count, .header-action_cart .count_item_pr');
        $counts.text(num);

        // Hiệu ứng bump nổi bật khi cập nhật
        $counts.addClass('cart-count-bump');
        setTimeout(function () {
            $counts.removeClass('cart-count-bump');
        }, 400);
    }

    /**
     * Hiển thị thông báo toast nổi ở góc trang
     */
    function showNotification(message, type) {
        type = type || 'success';
        $('.ajax-notification.cart-toast').remove();

        var box = $('<div class="ajax-notification cart-toast ' + type + '" style="display: block;">' + message + '</div>');
        $('body').append(box);

        setTimeout(function () {
            box.fadeOut(300, function () {
                $(this).remove();
            });
        }, 2800);
    }

    // Expose ra window để có thể gọi ở bất kỳ component nào
    window.updateCartCount = updateCartCount;
    window.showCartNotification = showNotification;

    /**
     * Thêm vào giỏ hàng bằng mã sản phẩm qua AJAX
     */
    window.addToCartAjax = function (productId, quantity, callback) {
        quantity = parseInt(quantity, 10) || 1;
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: '/cart/add',
            type: 'POST',
            data: {
                _token: csrfToken,
                product_id: productId,
                quantity: quantity
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                if (response.success) {
                    updateCartCount(response.cartCount);
                    if (response.wishlistCount !== undefined && response.wishlistCount !== null) {
                        $('.js-wishlist-count').text(response.wishlistCount);
                        $('.js-wishlist-total').text(response.wishlistCount);
                    }
                    showNotification(response.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success');
                    if (typeof callback === 'function') {
                        callback(null, response);
                    }
                } else {
                    showNotification(response.message || 'Không thể thêm sản phẩm', 'error');
                    if (typeof callback === 'function') {
                        callback(new Error(response.message));
                    }
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                var msg = 'Đã có lỗi xảy ra. Vui lòng thử lại!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showNotification(msg, 'error');
                if (typeof callback === 'function') {
                    callback(xhr);
                }
            },
            complete: function () {
                if (typeof window.hideLoadingOverlay === 'function') {
                    window.hideLoadingOverlay();
                } else {
                    var overlay = document.getElementById('loading-overlay');
                    if (overlay) {
                        overlay.classList.add('hidden');
                        overlay.style.display = 'none';
                    }
                }
            }
        });
    };

    $(document).ready(function () {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Ngăn chặn loading overlay khi bấm nút thêm vào giỏ
        $(document).on('click', '.btn-cart.add_to_cart, .btn_add_cart, [data-cart-form] button:not([name="buy_now"])', function () {
            if (typeof window.hideLoadingOverlay === 'function') {
                window.hideLoadingOverlay();
            }
        });

        // Bắt sự kiện submit form thêm vào giỏ hàng
        $(document).on('submit', 'form[action="/cart/add"], form[action*="/cart/add"], form[data-cart-form], #add-to-cart-form', function (e) {
            var $form = $(this);

            // Nếu người dùng bấm "Mua ngay", bỏ qua AJAX để submit bình thường sang trang đặt hàng/giỏ hàng
            var submitter = (e.originalEvent && e.originalEvent.submitter) || e.submitter;
            if (submitter && (submitter.name === 'buy_now' || $(submitter).hasClass('btn-buyNow') || submitter.value === '1')) {
                return true;
            }

            e.preventDefault();

            // Đảm bảo không bị treo loading overlay nếu listener khác đã lỡ kích hoạt
            if (typeof window.hideLoadingOverlay === 'function') {
                window.hideLoadingOverlay();
            } else {
                var overlay = document.getElementById('loading-overlay');
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                }
            }

            var $btn = submitter ? $(submitter) : $form.find('button[type="submit"]:not([name="buy_now"]), button.add_to_cart, button.btn_add_cart');
            if ($btn.data('is-submitting')) {
                return false;
            }
            $btn.data('is-submitting', true).prop('disabled', true);

            var formData = $form.serialize();

            $.ajax({
                url: $form.attr('action') || '/cart/add',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (response.success) {
                        updateCartCount(response.cartCount);

                        // Nếu thao tác từ trang wishlist hoặc có xóa khỏi wishlist
                        var $wishlistItem = $form.closest('.js-wishlist-item');
                        if ($wishlistItem.length || response.removedFromWishlist) {
                            var newWishlistCount = (response.wishlistCount !== undefined && response.wishlistCount !== null)
                                ? response.wishlistCount
                                : Math.max(0, $('.js-wishlist-item').length - 1);

                            $('.js-wishlist-count').text(newWishlistCount);
                            $('.js-wishlist-total').text(newWishlistCount);

                            if ($wishlistItem.length) {
                                $wishlistItem.fadeOut(250, function () {
                                    $(this).remove();
                                    if (newWishlistCount === 0 || $('.js-wishlist-item').length === 0) {
                                        var listEl = document.getElementById('wishlist-list');
                                        var emptyEl = document.getElementById('wishlist-empty');
                                        if (listEl) listEl.classList.add('d-none');
                                        if (emptyEl) emptyEl.classList.remove('d-none');
                                    }
                                });
                            } else if (newWishlistCount === 0) {
                                var listEl = document.getElementById('wishlist-list');
                                var emptyEl = document.getElementById('wishlist-empty');
                                if (listEl) listEl.classList.add('d-none');
                                if (emptyEl) emptyEl.classList.remove('d-none');
                            }
                        }

                        showNotification(response.message || 'Đã thêm sản phẩm vào giỏ hàng thành công!', 'success');
                    } else if (response.message) {
                        showNotification(response.message, 'error');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    var msg = 'Không thể thêm sản phẩm vào giỏ hàng.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showNotification(msg, 'error');
                },
                complete: function () {
                    if (typeof window.hideLoadingOverlay === 'function') {
                        window.hideLoadingOverlay();
                    } else {
                        var overlay = document.getElementById('loading-overlay');
                        if (overlay) {
                            overlay.classList.add('hidden');
                            overlay.style.display = 'none';
                        }
                    }
                    $btn.data('is-submitting', false).prop('disabled', false);
                }
            });
        });

        // Bắt sự kiện click các nút có data-ajax-add-cart (tương thích codebase C#)
        $(document).on('click', '[data-ajax-add-cart]', function (e) {
            e.preventDefault();
            var productId = $(this).data('product-id') || $(this).data('ajax-add-cart');
            var quantity = $(this).data('quantity') || 1;
            window.addToCartAjax(productId, quantity);
        });
    });
})(jQuery);
