let cartQuantityUpdating = false;
const pendingConfirmations = new WeakSet();
const approvedConfirmations = new WeakSet();
window.addEventListener('pageshow', () => { cartQuantityUpdating = false; });

document.addEventListener('submit', async function (event) {
    if (event.defaultPrevented) { return; }
    const form = event.target;
    if (pendingConfirmations.has(form) || form.dataset.uiSubmitting === 'true') { event.preventDefault(); return; }
    if (form.matches('[data-checkout-form]') && cartQuantityUpdating) { event.preventDefault(); return; }
    const quantityForm = form.matches('[data-quantity-form]');
    const input = quantityForm ? form.elements.quantity : null;
    if (quantityForm && (cartQuantityUpdating || !input.reportValidity() || input.value === input.defaultValue)) { event.preventDefault(); return; }
    const removing = quantityForm && Number(input.value) === 0;
    const message = removing ? form.dataset.removeConfirm : event.submitter?.dataset.confirm || form.dataset.confirm;
    const approved = approvedConfirmations.delete(form);
    if (message && !approved) {
        event.preventDefault();pendingConfirmations.add(form);
        const submitter = event.submitter;
        const options = { danger: /xóa|hủy/i.test(message), trigger: submitter || document.activeElement, name: window.RestaurantUI?.confirmationName(form) || '' };
        let accepted = false;
        try { accepted = window.RestaurantUI ? await RestaurantUI.confirm(message, options) : window.confirm(message); }
        finally { pendingConfirmations.delete(form); }
        if (!accepted) { if (removing) { input.value = input.defaultValue; } return; }
        approvedConfirmations.add(form);
        if (submitter?.isConnected) { form.requestSubmit(submitter); } else { form.requestSubmit(); }
        return;
    }
    if (quantityForm) {
        cartQuantityUpdating = true;
        document.querySelectorAll('[data-checkout-form] button, [data-quantity-step]').forEach(button => { button.disabled = true; });
    }
    window.RestaurantUI?.markSubmitting(form, event.submitter);
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-quantity-step]');
    if (!button || cartQuantityUpdating) { return; }
    const form = button.closest('[data-quantity-form]');
    const input = form.elements.quantity;
    if (!input.checkValidity()) { input.reportValidity(); return; }
    input.value = String(Math.max(0, Math.min(99, Number(input.value) + Number(button.dataset.quantityStep))));
    form.requestSubmit();
});

document.addEventListener('change', function (event) {
    if (event.target.matches('[data-quantity-form] input[name="quantity"]')) {
        if (event.target.checkValidity()) { event.target.form.requestSubmit(); }
        else { event.target.reportValidity(); }
    }
    if (event.target.matches('[data-check-all]')) {
        document.querySelectorAll('input[form="' + event.target.dataset.checkAll + '"][name="ids[]"]').forEach(function (box) { box.checked = event.target.checked; });
    }
    if (event.target.matches('[data-search-filter]')) {
        const form = event.target.closest('form');
        if (form.hasAttribute('data-live-search')) { return; }
        const query = form.elements.q;
        if (query && !query.value.trim()) { query.disabled = true; }
        form.submit();
    }
});

document.querySelectorAll('form[data-live-search]').forEach(function (form) {
    const results = document.getElementById(form.dataset.liveSearch);
    const status = document.querySelector('[data-search-status="' + form.dataset.liveSearch + '"]');
    if (!results) { return; }
    let timer;
    let controller;
    let version = 0;
    const currentUrl = function () {
        const url = new URL(form.action);
        for (const [key, value] of new FormData(form)) {
            if (typeof value === 'string' && value.trim()) { url.searchParams.set(key, value.trim()); }
        }
        return url;
    };
    const load = async function (url, requestVersion) {
        controller = new AbortController();
        if(window.RestaurantUI) { RestaurantUI.setLoading(results,true); } else { results.setAttribute('aria-busy','true'); }
        if (status) { status.classList.remove('visually-hidden','app-search-error'); status.setAttribute('role','status'); status.textContent = 'Đang tìm món…'; }
        try {
            const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
            if (!response.ok || response.redirected) { throw new Error('Cannot load dishes'); }
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (requestVersion !== version) { return; }
            const replacement = html.getElementById(results.id);
            if (!replacement) { throw new Error('Missing result list'); }
            window.RestaurantUI?.dispose(results);
            results.innerHTML = replacement.innerHTML;
            window.RestaurantUI?.enhance(results);
            history.replaceState(null, '', url);
            if (status) { status.classList.add('visually-hidden'); status.textContent = results.querySelector('[data-result-count]')?.textContent || 'Đã cập nhật danh sách món.'; }
        } catch (error) {
            if (error.name !== 'AbortError' && requestVersion === version && status) {
                const message='Chưa tải được món ăn. Vui lòng thử tìm kiếm lại.';
                if(window.RestaurantUI) { RestaurantUI.showRetry(status,message,()=>search(0,url)); } else { status.textContent=message; }
            }
        } finally {
            if (requestVersion === version) { if(window.RestaurantUI) { RestaurantUI.setLoading(results,false); } else { results.setAttribute('aria-busy','false'); } }
        }
    };
    const search = function (delay = 0, url = currentUrl()) {
        clearTimeout(timer);
        controller?.abort();
        const requestVersion = ++version;
        timer = setTimeout(() => load(url, requestVersion), delay);
    };
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!form.elements.q.value.trim()) { form.querySelectorAll('select').forEach(select => { select.value = ''; }); }
        search();
    });
    form.elements.q.addEventListener('input', function (event) {
        if (event.isComposing) { return; }
        if (!event.target.value.trim()) { form.querySelectorAll('select').forEach(select => { select.value = ''; }); }
        search(180);
    });
    form.elements.q.addEventListener('compositionend', () => search(180));
    form.querySelectorAll('[data-search-filter]').forEach(select => select.addEventListener('change', () => search()));
    results.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) { return; }
        event.preventDefault();
        search(0, new URL(link.href));
    });
});

document.querySelectorAll('[data-payment-qr]').forEach(function (element) {
    if (typeof QRCode !== 'undefined') {
        new QRCode(element, { text: element.dataset.paymentQr, width: 256, height: 256, correctLevel: QRCode.CorrectLevel.M });
        element.querySelectorAll('img').forEach(function (img) { img.alt = 'QR chuyển khoản MB Bank theo số tiền và mã đơn'; });
    }
});
