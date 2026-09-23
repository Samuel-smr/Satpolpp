function toggleSidebar(open) {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!sidebar || !backdrop || !toggle) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !open);
    backdrop.classList.toggle('hidden', !open);
    toggle.setAttribute('aria-expanded', String(open));

    if (open) {
        sidebar.querySelector('a')?.focus();
    } else if (window.matchMedia('(max-width: 1023px)').matches) {
        toggle.focus();
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.querySelector('[data-sidebar-toggle]')?.getAttribute('aria-expanded') === 'true') {
        toggleSidebar(false);
    }
});

document.querySelectorAll('#sidebar nav a').forEach(function (link) {
    link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 1023px)').matches) {
            toggleSidebar(false);
        }
    });
});

window.matchMedia('(min-width: 1024px)').addEventListener('change', function (event) {
    if (event.matches && document.querySelector('[data-sidebar-toggle]')?.getAttribute('aria-expanded') === 'true') {
        toggleSidebar(false);
    }
});
