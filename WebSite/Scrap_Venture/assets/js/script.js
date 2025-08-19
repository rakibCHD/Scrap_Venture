document.addEventListener('DOMContentLoaded', function() {
    // Phone number validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value.substring(0, 11);
            
            // Visual feedback
            if (value.length === 11) {
                e.target.classList.add('valid');
                e.target.classList.remove('invalid');
            } else {
                e.target.classList.add('invalid');
                e.target.classList.remove('valid');
            }
        });
    }

    // Form validation with better error messages
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Phone validation
            if (phoneInput && form.contains(phoneInput)) {
                const phoneValue = phoneInput.value.replace(/\D/g, '');
                if (phoneValue.length !== 11) {
                    e.preventDefault();
                    showToast('❌ Please enter a valid 11-digit phone number', 'error');
                    phoneInput.focus();
                }
            }

            // Bottle count validation
            const bottleInput = form.querySelector('#bottle_count');
            if (bottleInput) {
                const bottleCount = parseInt(bottleInput.value);
                if (isNaN(bottleCount) || bottleCount < 1) {
                    e.preventDefault();
                    showToast('❌ Please enter at least 1 bottle', 'error');
                    bottleInput.focus();
                }
            }
        });
    });

    // Enhanced withdrawal modal setup
    const modal = document.getElementById('withdrawModal');
    if (modal) {
        const cancelBtn = modal.querySelector('.cancel-btn');
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeWithdrawModal();
        });
        
        cancelBtn.addEventListener('click', closeWithdrawModal);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeWithdrawModal();
        });
    }

    // Auto-close toast notifications
    setTimeout(() => {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => toast.remove());
    }, 5000);
});

// Toast notification system
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

// Enhanced withdrawal flow with all safety checks
function openWithdrawModal() {
    const balanceEl = document.querySelector('.balance-amount');
    if (!balanceEl) return;

    const currentBalance = parseFloat(balanceEl.textContent);
    const minWithdrawal = 2.00; // Minimum 2 BDT required

    if (currentBalance < minWithdrawal) {
        showToast(`⚠️ Withdrawal failed. You need at least ${minWithdrawal} BDT (4 bottles) to withdraw.\n\nCurrent balance: ${currentBalance.toFixed(2)} BDT`, 'warning');
        return;
    }

    const modal = document.getElementById('withdrawModal');
    if (!modal) return;

    // Update modal content
    modal.querySelector('.modal-message').innerHTML = `
        You are about to withdraw <strong>${currentBalance.toFixed(2)} BDT</strong>.
        <div class="min-withdrawal-hint">Minimum withdrawal: ${minWithdrawal} BDT (4 bottles)</div>
    `;
    modal.style.display = 'block';
}

function closeWithdrawModal() {
    const modal = document.getElementById('withdrawModal');
    if (modal) modal.style.display = 'none';
}

function submitWithdraw() {
    const modal = document.getElementById('withdrawModal');
    if (!modal) return;

    const confirmBtn = modal.querySelector('.confirm-btn');
    const balanceEl = document.querySelector('.balance-amount');
    if (!confirmBtn || !balanceEl) return;

    const currentBalance = parseFloat(balanceEl.textContent);
    const minWithdrawal = 2.00;

    // Final validation
    if (currentBalance < minWithdrawal) {
        showToast(`❌ Withdrawal failed. Minimum ${minWithdrawal} BDT required (your balance: ${currentBalance.toFixed(2)} BDT)`, 'error');
        closeWithdrawModal();
        return;
    }

    // Visual feedback
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    confirmBtn.disabled = true;
    modal.querySelector('.cancel-btn').disabled = true;

    // Submit after delay
    setTimeout(() => {
        const form = document.getElementById('withdrawForm');
        if (form) form.submit();
    }, 1500);
}