/**
 * ProstheCare Clinic Management System - Auth JavaScript
 * Handles password visibility toggling and client-side interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle handler
    const toggleButtons = document.querySelectorAll('.password-toggle-button');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const inputContainer = this.closest('.password-input-container');
            const passwordInput = inputContainer.querySelector('input');
            const eyeIcon = this.querySelector('svg');

            if (!passwordInput) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Switch icon to "eye-slash"
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                // Switch icon back to normal open "eye"
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
                this.setAttribute('aria-label', 'Show password');
            }
        });
    });

    // Form submission animation / loading state
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(form => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('.btn-auth-action');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.dataset.originalText || submitBtn.textContent;
                submitBtn.dataset.originalText = originalText;
                submitBtn.innerHTML = `
                    <svg class="spin-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle>
                    </svg>
                    Processing...
                `;
            }
        });
    });
});
