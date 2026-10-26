(() => {
    document.querySelectorAll('[data-product-search]').forEach((form) => {
        const input = form.querySelector('input[name="q"]');
        const results = form.querySelector('[data-search-results]');
        let timer;
        let controller;
        let version = 0;
        const clear = () => { results.replaceChildren(); results.hidden = true; };
        input.addEventListener('input', () => {
            clearTimeout(timer);
            controller?.abort();
            const current = ++version;
            const query = input.value.trim();
            clear();
            if (!query) return;
            timer = setTimeout(async () => {
                controller = new AbortController();
                try {
                    const url = new URL(form.dataset.endpoint, window.location.origin);
                    url.searchParams.set('q', query);
                    const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('search');
                    const data = await response.json();
                    if (version !== current) return;
                    clear();
                    for (const product of data.products) {
                        const link = document.createElement('a');
                        link.href = `/products/${encodeURIComponent(product.slug)}`;
                        link.className = 'block border-b p-3 hover:bg-gray-100';
                        link.textContent = `${product.name} — ${product.formatted_price}`;
                        results.append(link);
                    }
                    if (!data.products.length) results.textContent = 'Không tìm thấy sản phẩm.';
                    results.hidden = false;
                } catch (error) {
                    if (error.name === 'AbortError' || version !== current) return;
                    results.textContent = 'Chưa thể tìm kiếm. Vui lòng thử lại.';
                    results.hidden = false;
                }
            }, 300);
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { ++version; clearTimeout(timer); controller?.abort(); clear(); }
        });
        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) { ++version; clearTimeout(timer); controller?.abort(); clear(); }
        });
    });
})();
