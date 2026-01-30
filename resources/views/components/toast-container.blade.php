{{--
    Toast Container Component
    Add this once in your layout for toast notifications

    Usage in layout:
    <x-toast-container />

    Then use JavaScript to show toasts:
    Toast.success('Changes saved!');
    Toast.error('Something went wrong');
    Toast.warning('Please check your input');
    Toast.info('New update available', 'Update');

    Or with options:
    Toast.show({
        type: 'success',
        title: 'Success',
        message: 'Your changes have been saved',
        duration: 5000,
        dismissible: true
    });
--}}

<div class="toast-container" id="toastContainer"></div>

<script>
window.Toast = (function() {
    const container = document.getElementById('toastContainer');

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    function show(options) {
        const {
            type = 'info',
            title = '',
            message = '',
            duration = 5000,
            dismissible = true
        } = typeof options === 'string' ? { message: options } : options;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        toast.innerHTML = `
            <i class="fas ${icons[type]} toast-icon"></i>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            ${dismissible ? `
                <button type="button" class="toast-close" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            ` : ''}
        `;

        container.appendChild(toast);

        // Close button handler
        if (dismissible) {
            const closeBtn = toast.querySelector('.toast-close');
            closeBtn?.addEventListener('click', () => removeToast(toast));
        }

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => removeToast(toast), duration);
        }

        return toast;
    }

    function removeToast(toast) {
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 200);
    }

    function success(message, title = '') {
        return show({ type: 'success', title, message });
    }

    function error(message, title = '') {
        return show({ type: 'error', title, message });
    }

    function warning(message, title = '') {
        return show({ type: 'warning', title, message });
    }

    function info(message, title = '') {
        return show({ type: 'info', title, message });
    }

    return { show, success, error, warning, info };
})();
</script>
