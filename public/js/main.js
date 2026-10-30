// Loading functionality
let loadingSafetyTimer = null;

function hideLoadingOverlay() {
    clearTimeout(loadingSafetyTimer);
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('active');
        loadingOverlay.classList.add('hidden');
        loadingOverlay.style.display = 'none';
        loadingOverlay.style.opacity = '0';
        loadingOverlay.style.pointerEvents = 'none';
    }
}

function showLoadingOverlay() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('active');
        loadingOverlay.style.display = 'flex';
        loadingOverlay.style.opacity = '1';
        loadingOverlay.style.pointerEvents = 'auto';

        clearTimeout(loadingSafetyTimer);
        // Tự động tắt sau 3 giây để phòng ngừa bị kẹt
        loadingSafetyTimer = setTimeout(hideLoadingOverlay, 3000);
    }
}

window.showLoadingOverlay = showLoadingOverlay;
window.hideLoadingOverlay = hideLoadingOverlay;

window.addEventListener('load', function () {
    // Ẩn loading sau khi trang load xong
    setTimeout(hideLoadingOverlay, 0);
});

// Bấm Back/Forward: trình duyệt khôi phục trang từ bfcache (đúng trạng thái lúc rời đi,
// loading đang hiện) và KHÔNG chạy lại sự kiện 'load' -> phải ẩn loading ở 'pageshow'
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        hideLoadingOverlay();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Handle click on the mobile menu button
    const btnMenuMobile = document.getElementById('btn-menu-mobile');
    if (btnMenuMobile) {
        btnMenuMobile.addEventListener('click', function () {
            document.querySelector('.header-menu').classList.add('current');
            document.querySelector('.mobile-nav-overflow').classList.add('open');
        });
    }

    // Handle click on close button in mobile drawer
    const btnCloseDrawer = document.getElementById('btn-close-drawer');
    if (btnCloseDrawer) {
        btnCloseDrawer.addEventListener('click', function () {
            document.querySelector('.header-menu').classList.remove('current');
            document.querySelector('.mobile-nav-overflow').classList.remove('open');
        });
    }

    // Handle click on the title menu to remove classes
    const titleMenu = document.querySelector('.header-menu .title_menu');
    if (titleMenu) {
        titleMenu.addEventListener('click', function () {
            document.querySelector('.header-menu').classList.remove('current');
            document.querySelector('.mobile-nav-overflow').classList.remove('open');
        });
    }

    // Handle click on the mobile-nav-overflow to toggle classes
    const mobileNavOverflow = document.querySelector('.mobile-nav-overflow');
    if (mobileNavOverflow) {
        mobileNavOverflow.addEventListener('click', function () {
            document.querySelector('.header-menu').classList.remove('current');
            this.classList.remove('open');
        });
    }

    // Handle dropdown menu toggle
    const dropdownToggles = document.querySelectorAll('#nav li > .open_mnu');
    dropdownToggles.forEach(function (element) {
        element.addEventListener('click', function (e) {
            e.preventDefault();

            const parentLi = this.closest('li');
            const dropdownMenu = Array.from(parentLi.children).find(child => child.classList.contains('dropdown-menu'));

            if (dropdownMenu) {
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                parentLi.classList.toggle('current');
                dropdownMenu.classList.toggle('current');
            }

            this.classList.toggle('current');
        });
    });

    // Handle footer section toggle
    const footerHeaders = document.querySelectorAll('.footer-click h4');

    footerHeaders.forEach(function (header) {
        header.addEventListener('click', function () {
            // Toggle class 'cls_mn' cho chính thẻ <h4> được click
            this.classList.toggle('cls_mn');

            // Tìm phần tử tiếp theo (next sibling) và toggle hiển thị
            const nextElement = this.nextElementSibling;
            if (nextElement) {
                // Toggle hiển thị bằng cách thay đổi style
                if (nextElement.style.display === 'none' || nextElement.style.display === '') {
                    nextElement.style.display = 'block';
                } else {
                    nextElement.style.display = 'none';
                }

                // Toggle class 'current' cho <ul> tiếp theo
                if (nextElement.tagName === 'UL') {
                    nextElement.classList.toggle('current');
                }
            }
        });
    });

    // Handle active state for navigation items
    const navLinks = document.querySelectorAll('.header-menu .nav-link');

    navLinks.forEach(function (navLink) {
        navLink.addEventListener('click', function () {
            // Tìm phần tử nav-item cha của nav-link được click
            const parentNavItem = this.closest('.nav-item');

            // Xóa class 'active' khỏi tất cả các nav-item
            document.querySelectorAll('.header-menu .nav-item').forEach(function (item) {
                item.classList.remove('active');
                console.log("Hello")
            });

            // Thêm class 'active' vào nav-item cha của nav-link được click
            if (parentNavItem) {
                parentNavItem.classList.add('active');
                console.log('Active nav-item:', parentNavItem);
            }
        });
    });

    // Handle password recovery toggle
    const quenmkButtons = document.querySelectorAll('.quenmk');
    const quaylaiButtons = document.querySelectorAll('.quay_lai');
    const loginElement = document.getElementById('login');
    const recoverElement = document.querySelector('.h_recover');
    const mainLoginTitle = document.querySelector('.pagecustome > .wpx > h1.title_heads');

    function openRecoverForm(e) {
        if (e) e.preventDefault();
        if (loginElement) {
            loginElement.style.display = 'none';
            loginElement.classList.add('hidden', 'd-none');
        }
        if (mainLoginTitle) {
            mainLoginTitle.style.display = 'none';
        }
        if (recoverElement) {
            recoverElement.style.display = 'block';
            recoverElement.classList.remove('d-none');
            recoverElement.style.maxHeight = 'none';
            recoverElement.style.overflow = 'visible';
        }
    }

    function closeRecoverForm(e) {
        if (e) e.preventDefault();
        if (recoverElement) {
            recoverElement.style.display = 'none';
            recoverElement.classList.add('d-none');
        }
        if (loginElement) {
            loginElement.style.display = 'block';
            loginElement.classList.remove('hidden', 'd-none');
        }
        if (mainLoginTitle) {
            mainLoginTitle.style.display = 'block';
        }
    }

    quenmkButtons.forEach(function (button) {
        button.addEventListener('click', openRecoverForm);
    });

    quaylaiButtons.forEach(function (button) {
        button.addEventListener('click', closeRecoverForm);
    });

    if (window.location.search && window.location.search.includes('show_recover')) {
        openRecoverForm();
    }

    // Handle Back to Top smooth scroll
    const backToTop = document.querySelector('.backtop');
    if (backToTop) {
        backToTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Handle loading overlay on internal link clicks
    const internalLinks = document.querySelectorAll('a[href^="/"], a[href^="~/"]');

    internalLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Không hiển thị loading cho link có target="_blank" hoặc download
            if (this.target === '_blank' || this.hasAttribute('download')) {
                return;
            }

            // Không hiển thị loading cho link javascript, anchor hash hoặc các nút bấm AJAX
            const href = this.getAttribute('href') || '';
            if (
                href.startsWith('javascript:') ||
                href === '#' ||
                href.startsWith('/#') ||
                this.classList.contains('backtop') ||
                this.classList.contains('js-wishlist-toggle') ||
                this.classList.contains('setWishlist') ||
                this.classList.contains('remove-fav') ||
                this.classList.contains('remove-item-cart') ||
                this.classList.contains('btn-clear-cart') ||
                this.classList.contains('btn-qty-minus') ||
                this.classList.contains('btn-qty-plus') ||
                this.hasAttribute('data-no-loading') ||
                this.hasAttribute('data-ajax-add-cart')
            ) {
                return;
            }

            // Hiển thị loading
            showLoadingOverlay();
        });
    });

    // Hiển thị loading khi submit form (ngoại trừ live search debounce, các form ajax giỏ hàng)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') {
            return;
        }

        // Bỏ qua nếu submit đã bị preventDefault
        if (e.defaultPrevented) {
            return;
        }

        // Kiểm tra nếu người dùng bấm "Mua ngay" -> cho phép hiển thị loading khi chuyển hướng
        const submitter = e.submitter || (e.originalEvent && e.originalEvent.submitter);
        const isBuyNow = submitter && (
            submitter.name === 'buy_now' ||
            (submitter.classList && submitter.classList.contains('btn-buyNow')) ||
            submitter.value === '1'
        );

        const action = (form.getAttribute('action') || '').toLowerCase();
        const isAddToCart = action.includes('/cart/add') ||
            action.includes('cart/themgiohang') ||
            form.id === 'add-to-cart-form' ||
            form.hasAttribute('data-cart-form');

        // Bỏ qua các form AJAX hoặc không cần loading overlay
        if (
            form.hasAttribute('data-product-search') ||
            form.classList.contains('header-search-form') ||
            form.hasAttribute('data-no-loading') ||
            form.classList.contains('form-remove-item') ||
            form.classList.contains('form-update-qty') ||
            form.classList.contains('form-clear-cart') ||
            form.classList.contains('form-cart-ajax') ||
            (isAddToCart && !isBuyNow)
        ) {
            return;
        }

        showLoadingOverlay();
    });
});