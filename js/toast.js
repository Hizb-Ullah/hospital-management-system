/* hospital/js/toast.js */

const Toast = {
    container: null,

    init() {
        // Create container if it doesn't exist
        this.container = document.querySelector('.toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'success', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Define icons based on type
        let icon = '✓';
        if (type === 'error') icon = '✗';
        if (type === 'warning') icon = '⚠';
        if (type === 'info') icon = 'ℹ';

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <div class="toast-message">${message}</div>
            <button class="toast-close">&times;</button>
            <div class="toast-progress"></div>
        `;

        this.container.appendChild(toast);

        // Close button action
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            this.remove(toast);
        });

        // Auto remove after duration
        const timer = setTimeout(() => {
            this.remove(toast);
        }, duration);

        toast.dataset.timerId = timer;
    },

    remove(toast) {
        if (toast.dataset.timerId) {
            clearTimeout(Number(toast.dataset.timerId));
        }
        
        toast.classList.add('toast-out');
        
        // Remove from DOM after animation completes
        toast.addEventListener('animationend', (e) => {
            if (e.animationName === 'toastOut') {
                toast.remove();
            }
        });
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error'); },
    warning(message) { this.show(message, 'warning'); },
    info(message) { this.show(message, 'info'); }
};

// Expose globally
window.Toast = Toast;
