/* Interactions de la page de démonstration uniquement. Aucune requête métier. */
(function () {
    'use strict';
    const toastElement = document.getElementById('previewToast');
    const showToast = function (message) {
        toastElement.querySelector('[data-ui-toast-body]').textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 5000 }).show();
    };
    document.querySelectorAll('[data-ui-toast]').forEach(button => button.addEventListener('click', () => showToast(button.dataset.uiToast)));
    document.querySelectorAll('[data-ui-tooltip]').forEach(button => new bootstrap.Tooltip(button, { trigger: 'hover focus', container: 'body' }));

    const topbar = document.querySelector('[data-ui-topbar]');
    const topObserver = new IntersectionObserver(entries => topbar.classList.toggle('is-scrolled', !entries[0].isIntersecting));
    const marker = document.createElement('div'); marker.setAttribute('aria-hidden', 'true'); document.body.prepend(marker); topObserver.observe(marker);

    const nav = document.getElementById('preview-navigation');
    const navLinks = Array.from(document.querySelectorAll('.ui-side-nav a'));
    const sectionObserver = new IntersectionObserver(entries => {
        const entry = entries.filter(item => item.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
        if (!entry) { return; }
        navLinks.forEach(link => { const active = link.hash === '#' + entry.target.id; link.classList.toggle('is-active', active); active ? link.setAttribute('aria-current', 'location') : link.removeAttribute('aria-current'); });
    }, { rootMargin: '-90px 0px -55% 0px', threshold: 0 });
    document.querySelectorAll('main > section[id]').forEach(section => sectionObserver.observe(section));
    navLinks.forEach(link => link.addEventListener('click', () => { if (nav.classList.contains('show')) { bootstrap.Offcanvas.getOrCreateInstance(nav).hide(); } }));

    const rows = Array.from(document.querySelectorAll('[data-ui-row]'));
    const all = document.querySelector('[data-ui-check-all]');
    const selected = document.querySelector('[data-ui-selected]');
    const updateSelection = function () {
        const visible = rows.filter(row => !row.hidden);
        const checkedVisible = visible.filter(row => row.querySelector('[data-ui-row-check]').checked).length;
        const count = rows.filter(row => row.querySelector('[data-ui-row-check]').checked).length;
        rows.forEach(row => { const checked = row.querySelector('[data-ui-row-check]').checked; row.classList.toggle('is-selected', checked); });
        all.checked = visible.length > 0 && checkedVisible === visible.length;
        all.indeterminate = checkedVisible > 0 && checkedVisible < visible.length;
        selected.textContent = count ? 'Đã chọn ' + count + ' dòng mẫu' : 'Chưa chọn dòng';
    };
    document.querySelectorAll('[data-ui-row-check]').forEach(input => input.addEventListener('change', updateSelection));
    all.addEventListener('change', () => { rows.filter(row => !row.hidden).forEach(row => { row.querySelector('[data-ui-row-check]').checked = all.checked; }); updateSelection(); });
    const normalize = value => value.toLocaleLowerCase('vi').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd');
    const filter = document.querySelector('[data-ui-filter]');
    const status = document.querySelector('[data-ui-status]');
    const applyFilter = function () {
        const query = normalize(filter.value.trim());
        rows.forEach(row => { const statusText = row.querySelector('.ui-badge').textContent; row.hidden = !normalize(row.textContent).includes(query) || Boolean(status.value && statusText !== status.value); });
        const count = rows.filter(row => !row.hidden).length;
        document.querySelector('[data-ui-table-scroll]').hidden = count === 0;
        document.querySelector('[data-ui-no-results]').hidden = count !== 0;
        document.querySelector('[data-ui-table-count]').textContent = 'Hiển thị ' + count + ' đơn mẫu';
        updateSelection();
    };
    filter.addEventListener('input', applyFilter); status.addEventListener('change', applyFilter);
    document.querySelector('[data-ui-table-scroll]').addEventListener('scroll', function () { this.classList.toggle('is-scrolled', this.scrollTop > 0); }, { passive: true });
    document.querySelector('[data-ui-sort]').addEventListener('click', function () {
        const header = document.querySelector('[data-ui-sort-header]');
        const ascending = header.getAttribute('aria-sort') !== 'ascending';
        header.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
        const sorted = rows.slice().sort((a, b) => (Number(a.dataset.amount) - Number(b.dataset.amount)) * (ascending ? 1 : -1));
        const body = document.querySelector('[data-ui-table-body]'); sorted.forEach(row => body.append(row));
        showToast('Đã sắp xếp số tiền của dữ liệu minh họa.');
    });

    let orderTrigger;
    const orderModal = document.getElementById('orderPreview');
    document.querySelectorAll('[data-ui-order]').forEach(button => button.addEventListener('click', () => {
        orderTrigger = button;
        const row = button.closest('tr');
        orderModal.querySelector('[data-ui-order-id]').textContent = '#' + button.dataset.uiOrder;
        orderModal.querySelector('[data-ui-order-name]').textContent = row.querySelector('.ui-table-name').textContent;
        orderModal.querySelector('[data-ui-order-amount]').textContent = row.querySelector('.ui-numeric').textContent;
        orderModal.querySelector('[data-ui-order-status]').textContent = row.querySelector('.ui-badge').textContent;
        bootstrap.Modal.getOrCreateInstance(orderModal).show(button);
    }));
    orderModal.addEventListener('hidden.bs.modal', () => orderTrigger?.focus());
    document.querySelector('[data-ui-confirm-delete]').addEventListener('click', () => { bootstrap.Modal.getOrCreateInstance(document.getElementById('deletePreview')).hide(); showToast('Đã minh họa xác nhận xóa. Không thay đổi dữ liệu nhà hàng.'); });

    const form = document.querySelector('[data-ui-form]');
    const name = document.getElementById('preview-name');
    const price = document.getElementById('preview-price');
    const setError = function (input, error, invalid) {
        input.setAttribute('aria-invalid', String(invalid));
        const ids = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== error);
        if (invalid) { ids.push(error); }
        if (ids.length) { input.setAttribute('aria-describedby', ids.join(' ')); } else { input.removeAttribute('aria-describedby'); }
        document.getElementById(error).hidden = !invalid;
    };
    name.addEventListener('input', () => setError(name, 'preview-name-error', !name.value.trim()));
    price.addEventListener('input', () => setError(price, 'preview-price-error', !price.checkValidity()));
    const loading = function (button, active) { button.classList.toggle('is-loading', active); button.disabled = active; button.setAttribute('aria-busy', String(active)); };
    form.addEventListener('submit', event => {
        event.preventDefault();
        setError(name, 'preview-name-error', !name.value.trim()); setError(price, 'preview-price-error', !price.checkValidity());
        const invalid = form.querySelector('[aria-invalid="true"]');
        const note = form.querySelector('[data-ui-form-note]');
        if (invalid) { invalid.focus(); note.textContent = 'Vui lòng kiểm tra các trường đang báo lỗi.'; return; }
        const button = form.querySelector('[data-ui-save]'); loading(button, true); note.textContent = 'Đang lưu dữ liệu mẫu…';
        setTimeout(() => { loading(button, false); note.textContent = 'Đã hoàn tất thao tác mẫu. Không ghi dữ liệu vào hệ thống.'; showToast('Đã lưu món mẫu thành công. Đây là bản xem trước giao diện.'); }, 750);
    });
    document.querySelector('[data-ui-retry]').addEventListener('click', function () {
        loading(this, true);
        document.querySelector('[data-ui-retry-title]').textContent = 'Đang tải lại dữ liệu mẫu…';
        setTimeout(() => { loading(this, false); document.querySelector('[data-ui-retry-title]').textContent = 'Đã tải được dữ liệu mẫu'; document.querySelector('[data-ui-retry-copy]').textContent = 'Trạng thái đã phục hồi. Mọi dữ liệu thật vẫn được giữ nguyên.'; showToast('Đã minh họa tải lại thành công.'); }, 750);
    });

    const quantity = document.getElementById('preview-quantity');
    const updateQuantity = function () { quantity.setCustomValidity(''); if (!quantity.checkValidity()) { quantity.reportValidity(); return; } document.querySelector('[data-ui-cart-total]').textContent = new Intl.NumberFormat('vi-VN').format(Number(quantity.value) * 55000) + 'đ'; };
    quantity.addEventListener('input', updateQuantity);
    document.querySelectorAll('[data-ui-quantity]').forEach(button => button.addEventListener('click', () => { quantity.value = String(Math.min(99, Math.max(1, Number(quantity.value || 1) + Number(button.dataset.uiQuantity)))); updateQuantity(); }));
    document.querySelector('[data-ui-motion-toggle]').addEventListener('click', function () { const disabled = document.documentElement.dataset.uiMotion !== 'off'; document.documentElement.dataset.uiMotion = disabled ? 'off' : 'on'; this.setAttribute('aria-pressed', String(disabled)); this.textContent = disabled ? 'Bật chuyển động mẫu' : 'Tắt chuyển động mẫu'; });
})();
