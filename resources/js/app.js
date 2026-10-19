const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');
const mobileViewport = window.matchMedia('(max-width: 767px)');

const setSidebarOpen = (isOpen) => {
    if (!sidebar || !sidebarToggle || !sidebarBackdrop) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !isOpen);
    sidebarBackdrop.classList.toggle('hidden', !isOpen || !mobileViewport.matches);
    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
    sidebarToggle.setAttribute('aria-label', isOpen ? 'Tutup navigasi' : 'Buka navigasi');
    document.body.classList.toggle('overflow-hidden', isOpen && mobileViewport.matches);
};

sidebarToggle?.addEventListener('click', () => {
    setSidebarOpen(sidebarToggle.getAttribute('aria-expanded') !== 'true');
});

sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));
sidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setSidebarOpen(false));
});

mobileViewport.addEventListener('change', () => setSidebarOpen(false));

const logoutModal = document.getElementById('logoutModal');
const logoutOpenButton = document.querySelector('[data-logout-open]');
const logoutSubmitButton = document.querySelector('[data-logout-submit]');
let logoutTrigger;

const setLogoutModalOpen = (isOpen) => {
    if (!logoutModal) {
        return;
    }

    logoutModal.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);

    if (isOpen) {
        logoutSubmitButton?.focus();
    } else {
        logoutTrigger?.focus();
    }
};

logoutOpenButton?.addEventListener('click', () => {
    logoutTrigger = logoutOpenButton;
    setLogoutModalOpen(true);
});

document.querySelectorAll('[data-logout-close]').forEach((button) => {
    button.addEventListener('click', () => setLogoutModalOpen(false));
});

logoutSubmitButton?.addEventListener('click', () => {
    document.getElementById('logoutForm')?.requestSubmit();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpen(false);

        if (logoutModal && !logoutModal.classList.contains('hidden')) {
            setLogoutModalOpen(false);
        }
    }
});
