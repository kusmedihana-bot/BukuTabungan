// Currency input formatting
document.querySelectorAll('.currency-input').forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value.replace(/[^0-9]/g, '');
        this.value = val;
    });
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.4s';
        setTimeout(() => alert.remove(), 400);
    }, 3500);
});

// Tab switching (summary page)
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.summary-block').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(target)?.classList.add('active');
    });
});

// Confirm delete
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm(this.dataset.confirm || 'Yakin ingin menghapus?')) {
            e.preventDefault();
        }
    });
});
