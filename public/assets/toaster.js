(function () {
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-octagon-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    const delays = { success: 4000, info: 5000, warning: 7000, error: 9000 };

    function container() {
        let el = document.getElementById('toaster');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toaster';
            el.className = 'toast-container app-toaster';
            document.body.appendChild(el);
        }
        return el;
    }

    // window.toast('Saved') or window.toast(['Name is required', 'Email is invalid'], 'error')
    window.toast = function (message, type) {
        type = icons[type] ? type : 'info';
        const lines = Array.isArray(message) ? message : [message];

        const toastEl = document.createElement('div');
        toastEl.className = 'toast app-toast app-toast-' + type;
        toastEl.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toastEl.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
        toastEl.setAttribute('aria-atomic', 'true');

        const body = document.createElement('div');
        body.className = 'd-flex align-items-start';

        const icon = document.createElement('i');
        icon.className = 'bi ' + icons[type] + ' app-toast-icon';
        icon.setAttribute('aria-hidden', 'true');

        const text = document.createElement('div');
        text.className = 'app-toast-text';
        lines.forEach(function (line) {
            const p = document.createElement('div');
            p.textContent = line;
            text.appendChild(p);
        });

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close app-toast-close';
        close.setAttribute('data-bs-dismiss', 'toast');
        close.setAttribute('aria-label', 'Close');

        body.append(icon, text, close);
        toastEl.appendChild(body);
        container().appendChild(toastEl);

        toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
        new bootstrap.Toast(toastEl, { delay: delays[type] }).show();
    };
})();
