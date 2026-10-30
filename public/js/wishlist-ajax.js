// Wishlist AJAX: bấm tim để thêm/bỏ yêu thích, không reload trang
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const HEART_EMPTY = '/images/heart.png';
    const HEART_RED = '/images/heart-red.svg';

    function toggleWishlist(productCode) {
        return fetch('/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_code: productCode })
        }).then(function (response) {
            // Chưa đăng nhập thì chuyển sang trang đăng nhập
            if (response.status === 401) {
                window.location.href = '/login';
                throw new Error('unauthenticated');
            }
            if (!response.ok) {
                throw new Error('request failed');
            }
            return response.json();
        });
    }

    // Cập nhật số đếm trên navbar (icon tim + dòng "Yêu thích(n)" trong menu tài khoản)
    function updateCount(count) {
        document.querySelectorAll('.js-wishlist-count').forEach(function (el) {
            el.textContent = count;
        });
    }

    // Đổi tim rỗng <-> tim đỏ cho mọi nút tim của cùng sản phẩm trên trang
    function setLiked(productCode, liked) {
        document.querySelectorAll('.setWishlist[data-product-code="' + productCode + '"]').forEach(function (btn) {
            const img = btn.querySelector('img');
            btn.classList.toggle('active', liked);
            btn.title = liked ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
            if (img) {
                img.src = liked ? HEART_RED : HEART_EMPTY;
                img.classList.toggle('img-unlike', liked);
            }
        });
    }

    function showNotification(message, type) {
        const box = document.createElement('div');
        box.className = 'ajax-notification ' + type;
        box.style.display = 'block';
        box.textContent = message;
        document.body.appendChild(box);
        setTimeout(function () {
            box.remove();
        }, 2500);
    }

    document.addEventListener('click', function (e) {
        // Nút tim trên thẻ sản phẩm
        const heartBtn = e.target.closest('.setWishlist');
        if (heartBtn) {
            e.preventDefault();
            // Chặn bấm liên tục khi request trước chưa xong
            if (heartBtn.dataset.loading) {
                return;
            }
            heartBtn.dataset.loading = '1';
            const productCode = heartBtn.dataset.productCode;

            // Đổi tim + số đếm ngay khi bấm (không chờ server), server trả về thì đồng bộ lại
            const wasLiked = heartBtn.classList.contains('active');
            const countEl = document.querySelector('.js-wishlist-count');
            const oldCount = countEl ? parseInt(countEl.textContent, 10) || 0 : 0;
            setLiked(productCode, !wasLiked);
            updateCount(Math.max(0, oldCount + (wasLiked ? -1 : 1)));

            toggleWishlist(productCode).then(function (data) {
                setLiked(productCode, data.status === 'added');
                updateCount(data.count);
                showNotification(data.status === 'added' ? 'Đã thêm vào yêu thích' : 'Đã bỏ khỏi yêu thích', 'success');
            }).catch(function (error) {
                // Lỗi thì trả tim + số đếm về như cũ
                setLiked(productCode, wasLiked);
                updateCount(oldCount);
                if (error.message !== 'unauthenticated') {
                    showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
                }
            }).finally(function () {
                delete heartBtn.dataset.loading;
            });
            return;
        }

        // Nút "Xóa" trên trang danh sách yêu thích
        const removeBtn = e.target.closest('.remove-fav');
        if (removeBtn) {
            e.preventDefault();
            if (removeBtn.disabled || !confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                return;
            }
            removeBtn.disabled = true;
            const productCode = removeBtn.dataset.productCode;

            toggleWishlist(productCode).then(function (data) {
                // Sản phẩm đã bị bỏ thích ở tab khác -> toggle vừa thêm lại, gọi thêm lần nữa để xóa
                return data.status === 'added' ? toggleWishlist(productCode) : data;
            }).then(function (data) {
                const item = removeBtn.closest('.js-wishlist-item');
                if (item) {
                    item.remove();
                }
                updateCount(data.count);
                document.querySelectorAll('.js-wishlist-total').forEach(function (el) {
                    el.textContent = data.count;
                });
                if (data.count === 0) {
                    document.getElementById('wishlist-list')?.classList.add('d-none');
                    document.getElementById('wishlist-empty')?.classList.remove('d-none');
                }
                showNotification('Đã xóa khỏi yêu thích', 'success');
            }).catch(function (error) {
                removeBtn.disabled = false;
                if (error.message !== 'unauthenticated') {
                    showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
                }
            });
        }
    });
});
