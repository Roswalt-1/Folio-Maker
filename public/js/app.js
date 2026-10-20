document.addEventListener('DOMContentLoaded', () => {
    const themeKey = 'folio-generator-theme';
    const themeToggle = document.querySelector('[data-site-theme-toggle]');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    const applyTheme = (theme) => {
        document.documentElement.dataset.theme = theme;
        if (themeToggle) {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            themeToggle.textContent = theme === 'dark' ? '☀' : '☾';
            themeToggle.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
            themeToggle.setAttribute('aria-pressed', String(theme === 'dark'));
            themeToggle.title = `Switch to ${nextTheme} mode`;
        }
    };

    applyTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');
    themeToggle?.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem(themeKey, nextTheme);
        applyTheme(nextTheme);
    });
    systemTheme.addEventListener('change', (event) => {
        if (!localStorage.getItem(themeKey)) applyTheme(event.matches ? 'dark' : 'light');
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.textContent = reveal ? 'Hide' : 'Show';
            button.setAttribute('aria-label', `${reveal ? 'Hide' : 'Show'} password`);
        });
    });

    document.querySelectorAll('[data-dismiss-toast]').forEach((button) => {
        button.addEventListener('click', () => button.closest('[data-toast]').remove());
    });
    const toast = document.querySelector('[data-toast]');
    if (toast) window.setTimeout(() => toast.remove(), 6000);

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirmMessage || 'Delete this portfolio?')) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-auto-submit]').forEach((select) => {
        select.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', select.value);
            window.location.assign(url);
        });
    });

});