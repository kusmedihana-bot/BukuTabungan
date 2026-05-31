// ── MOBILE SIDEBAR TOGGLE ──
(function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const closeBtn  = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('visible');
        hamburger.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
        hamburger.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openSidebar);
    if (closeBtn)  closeBtn.addEventListener('click', closeSidebar);
    if (overlay)   overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); });
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
})();

// ── CURRENCY INPUT ──
document.querySelectorAll('.currency-input').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// ── AUTO-DISMISS ALERTS ──
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.4s';
        setTimeout(() => alert.remove(), 400);
    }, 3500);
});

// ── SUMMARY TABS ──
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.summary-block').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(target)?.classList.add('active');
    });
});

// ── CONFIRM DELETE ──
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm(this.dataset.confirm || 'Yakin ingin menghapus?')) e.preventDefault();
    });
});
