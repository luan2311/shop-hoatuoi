// Loading functionality
window.addEventListener('load', function () {
    // Ẩn loading sau khi trang load xong
    setTimeout(function () {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
            // Xóa element sau khi animation kết thúc
            setTimeout(function () {
                loadingOverlay.style.display = 'none';
            }, 300);
        }
    }, 0); // Thời gian hiển thị tối thiểu (500ms)
});

// Bấm Back/Forward: trình duyệt khôi phục trang từ bfcache (đúng trạng thái lúc rời đi,
// loading đang hiện) và KHÔNG chạy lại sự kiện 'load' -> phải ẩn loading ở 'pageshow'
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
            loadingOverlay.style.display = 'none';
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Handle click on the mobile menu button
    const btnMenuMobile = document.getElementById('btn-menu-mobile');
    if (btnMenuMobile) {
        btnMenuMobile.addEventListener('click', function () {
            document.querySelector('.header-menu').classList.add('current');
            document.querySelector('.mobile-nav-overflow').classList.toggle('open');
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
    quenmkButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const loginElement = document.getElementById('login');
            const recoverElement = document.querySelector('.h_recover');

            // Toggle class 'hidden' cho #login
            if (loginElement) {
                loginElement.classList.toggle('hidden');
            }

            // Slide toggle cho .h_recover
            if (recoverElement) {
                if (recoverElement.style.display === 'none' || recoverElement.style.display === '') {
                    // Hiện phần tử với animation
                    recoverElement.style.display = 'block';
                    recoverElement.style.maxHeight = '0';
                    recoverElement.style.overflow = 'hidden';
                    recoverElement.style.transition = 'max-height 0.3s ease-in-out';

                    // Trigger reflow
                    recoverElement.offsetHeight;

                    recoverElement.style.maxHeight = recoverElement.scrollHeight + 'px';
                } else {
                    // Ẩn phần tử với animation
                    recoverElement.style.maxHeight = '0';

                    setTimeout(function () {
                        recoverElement.style.display = 'none';
                    }, 300);
                }
            }
        });
    });

    // Handle Back to Top smooth scroll
    const backToTop = document.querySelector('.backtop');
    if (backToTop) {
        backToTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    //Handle loading overlay on internal link clicks
    const internalLinks = document.querySelectorAll('a[href^="/"], a[href^="~/"]');

    internalLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Không hiển thị loading cho link có target="_blank" hoặc download
            if (this.target === '_blank' || this.hasAttribute('download')) {
                return;
            }

            // Không hiển thị loading cho link javascript, anchor hash hoặc wishlist
            const href = this.getAttribute('href') || '';
            if (href.startsWith('javascript:') || href === '#' || href.startsWith('/#') || this.classList.contains('backtop') || this.classList.contains('js-wishlist-toggle')) {
                return;
            }

            // Hiển thị loading
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
                loadingOverlay.classList.remove('hidden');
            }
        });
    });

    // Hiển thị loading khi submit form (ngoại trừ live search debounce)
    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (this.hasAttribute('data-product-search') || this.classList.contains('header-search-form')) {
                return;
            }
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
                loadingOverlay.classList.remove('hidden');
            }
        });
    });
});