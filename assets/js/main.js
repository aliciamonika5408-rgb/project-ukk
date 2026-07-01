/* ============================================
   STOCK GUDANG — Main JavaScript
   ============================================ */

// ============ MODAL SYSTEM ============
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Focus first input
    setTimeout(() => {
        const first = modal.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
        if (first) first.focus();
    }, 150);
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => {
            if (m.style.display !== 'none') {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
});

// ============ LOADING OVERLAY ============
function showLoading() {
    const el = document.getElementById('loadingOverlay');
    if (el) el.style.display = 'flex';
}

function hideLoading() {
    const el = document.getElementById('loadingOverlay');
    if (el) el.style.display = 'none';
}

// ============ TOAST NOTIFICATIONS ============
function showToast(message, type = 'info', duration = 3500) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: 'fa-circle-check',
        error:   'fa-circle-xmark',
        warning: 'fa-triangle-exclamation',
        info:    'fa-circle-info'
    };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info} toast-icon"></i>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="removeToast(this.parentElement)">
            <i class="fas fa-xmark"></i>
        </button>
    `;

    container.appendChild(toast);

    // Auto remove
    setTimeout(() => removeToast(toast), duration);
}

function removeToast(toast) {
    if (!toast || !toast.parentElement) return;
    toast.style.animation = 'none';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(50px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
}

// ============ SIDEBAR TOGGLE (Mobile) ============
const menuToggle    = document.getElementById('menuToggle');
const sidebar       = document.getElementById('sidebar');
const sidebarOverlay= document.getElementById('sidebarOverlay');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('active');
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
    });
}

// ============ NOTIFICATION PANEL ============
function toggleNotifPanel() {
    const panel = document.getElementById('notifPanel');
    if (!panel) return;
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Close notif panel when clicking outside
document.addEventListener('click', function(e) {
    const panel   = document.getElementById('notifPanel');
    const notifBtn= document.getElementById('notifBtn');
    if (panel && notifBtn && !panel.contains(e.target) && !notifBtn.contains(e.target)) {
        panel.style.display = 'none';
    }
});

// ============ GLOBAL SEARCH ============
const globalSearch = document.getElementById('globalSearch');
if (globalSearch) {
    globalSearch.addEventListener('input', function() {
        const q    = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.data-table tbody tr');
        if (!q) {
            rows.forEach(r => r.style.display = '');
            return;
        }
        rows.forEach(r => {
            const text = r.textContent.toLowerCase();
            r.style.display = text.includes(q) ? '' : 'none';
        });
    });
}

// ============ TABLE FILTER HELPER ============
function filterTbl(input, tableId) {
    const q     = input.value.toLowerCase().trim();
    const rows  = document.querySelectorAll(`#${tableId} tbody tr`);
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// ============ IMAGE PREVIEW ============
function previewFoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

// ============ FORM VALIDATION ============
document.addEventListener('submit', function(e) {
    const form = e.target;
    const requiredFields = form.querySelectorAll('[required]');
    let valid = true;

    requiredFields.forEach(field => {
        field.classList.remove('is-invalid');
        const errEl = field.parentElement.querySelector('.invalid-feedback');
        if (errEl) errEl.remove();

        if (!field.value.trim()) {
            valid = false;
            field.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'invalid-feedback';
            err.innerHTML = '<i class="fas fa-circle-exclamation"></i> Field ini wajib diisi';
            field.parentElement.appendChild(err);
        }
    });

    if (!valid) {
        e.preventDefault();
        showToast('Mohon lengkapi semua field yang wajib diisi!', 'warning');
    }
}, true);

// Remove invalid state on input
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('is-invalid')) {
        e.target.classList.remove('is-invalid');
        const err = e.target.parentElement.querySelector('.invalid-feedback');
        if (err) err.remove();
    }
});

// ============ NUMBER FORMAT ============
function formatNumber(n) {
    return new Intl.NumberFormat('id-ID').format(n);
}

function formatRupiah(n) {
    return 'Rp ' + formatNumber(n);
}

// ============ AUTO-RESIZE TEXTAREA ============
document.querySelectorAll('textarea.form-control').forEach(el => {
    el.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});


// ============ PRINT HANDLER ============
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
});

// ============ INITIALIZE ============
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to stat cards
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.4s ease';
        setTimeout(() => {
            card.style.opacity   = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + i * 80);
    });

    // Add animation to cards
    const allCards = document.querySelectorAll('.card');
    allCards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        card.style.transition = 'all 0.4s ease';
        setTimeout(() => {
            card.style.opacity   = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + i * 60);
    });
});
