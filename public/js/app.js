/**
 * Custom JavaScript for Shena Companion Welfare Association
 */

// Global app object
const ShenaApp = {
    // Configuration
    config: {
        mpesaPaybill: '4163987',
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedFileTypes: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']
    },

    // Initialize the application
    init: function() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupFormValidation();
        this.initializeSmsComposers();
        this.initializeSmsResponseFeedback();
        if (window.location.hash.startsWith("#sms-review-") || window.location.hash === "#account-update-sms") this.focusSmsTarget(window.location.hash);
    },

    initializeSmsResponseFeedback: function() {
        const showReview = data => {
            if (!data || !data.sms_review) return;
            const review = data.sms_review;
            window.setTimeout(() => ShenaApp.feedback([{type: 'info', message: review.message, target: review.target}]), 0);
        };
        if (window.fetch) {
            const originalFetch = window.fetch.bind(window);
            window.fetch = async function(...args) {
                const response = await originalFetch(...args);
                if (response.url && new URL(response.url, window.location.href).origin === window.location.origin && (response.headers.get('content-type') || '').includes('application/json')) {
                    const target = response.headers.get('X-Shena-Sms-Review');
                    if (target) showReview({sms_review: {target, message: 'Your SMS draft is ready to edit and review. No SMS has been sent.'}});
                    else response.clone().json().then(showReview).catch(() => {});
                }
                return response;
            };
        }
        if (window.jQuery) window.jQuery(document).ajaxComplete((event, xhr) => {
            const target = xhr.getResponseHeader('X-Shena-Sms-Review');
            showReview(target ? {sms_review: {target, message: 'Your SMS draft is ready to edit and review. No SMS has been sent.'}} : xhr.responseJSON);
        });
    },

    // Setup event listeners
    setupEventListeners: function() {
        // Confirmation dialogs
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('confirm-delete') || 
                e.target.closest('.confirm-delete')) {
                e.preventDefault();
                ShenaApp.confirmAction('Are you sure you want to delete this item?', function() {
                    if (e.target.tagName === 'FORM') {
                        e.target.submit();
                    } else if (e.target.closest('form')) {
                        e.target.closest('form').submit();
                    } else if (e.target.href) {
                        window.location.href = e.target.href;
                    }
                });
                return false;
            }

            if (e.target.classList.contains('confirm-action') || 
                e.target.closest('.confirm-action')) {
                e.preventDefault();
                const message = e.target.dataset.message || 'Are you sure you want to perform this action?';
                ShenaApp.confirmAction(message, function() {
                    if (e.target.tagName === 'FORM') {
                        e.target.submit();
                    } else if (e.target.closest('form')) {
                        e.target.closest('form').submit();
                    } else if (e.target.href) {
                        window.location.href = e.target.href;
                    }
                });
                return false;
            }
        });

        // Mobile navigation
        const navToggler = document.querySelector('.navbar-toggler');
        const navCollapse = document.querySelector('.navbar-collapse');
        
        if (navToggler && navCollapse) {
            navToggler.addEventListener('click', function() {
                navCollapse.classList.toggle('show');
            });
        }
    },

    // Initialize components
    initializeComponents: function() {
        this.initializePhoneFormatting();
        this.initializeFileUpload();
        this.initializeDatePickers();
        this.initializeTooltips();
        this.initializeModals();
    },

    // Phone number formatting
    initializePhoneFormatting: function() {
        const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="phone"]');
        
        phoneInputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                ShenaApp.formatPhoneNumber(this);
            });

            input.addEventListener('input', function() {
                // Remove non-digit characters while typing
                this.value = this.value.replace(/[^\d+]/g, '');
            });
        });
    },

    // Format phone number
    formatPhoneNumber: function(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.startsWith('254')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+254' + value.substring(1);
        } else if (value.length === 9 && /^[17]/.test(value)) {
            value = '+254' + value;
        }
        
        input.value = value;
    },

    // File upload initialization
    initializeFileUpload: function() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                ShenaApp.validateFileUpload(this);
            });
        });
    },

    // Validate file upload
    validateFileUpload: function(input) {
        const files = input.files;
        const feedback = input.parentNode.querySelector('.invalid-feedback') || 
                        this.createFeedbackElement(input);

        let isValid = true;
        let message = '';

        if (files.length > 0) {
            const file = files[0];
            const fileSize = file.size;
            const fileName = file.name;
            const fileExt = fileName.split('.').pop().toLowerCase();

            // Check file size
            if (fileSize > this.config.maxFileSize) {
                isValid = false;
                message = 'File size must be less than 5MB';
            }

            // Check file type
            if (!this.config.allowedFileTypes.includes(fileExt)) {
                isValid = false;
                message = 'Invalid file type. Allowed: ' + this.config.allowedFileTypes.join(', ');
            }
        }

        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            feedback.style.display = 'none';
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
    },

    // Create feedback element
    createFeedbackElement: function(input) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.appendChild(feedback);
        return feedback;
    },

    // Initialize date pickers
    initializeDatePickers: function() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(function(input) {
            // Set max date for date of birth to 100 years ago
            if (input.name === 'date_of_birth') {
                const today = new Date();
                const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
                const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
                
                input.max = maxDate.toISOString().split('T')[0];
                input.min = minDate.toISOString().split('T')[0];
            }
        });
    },

    // Initialize tooltips
    initializeTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Initialize modals
    initializeModals: function() {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(function(modalEl) {
            new bootstrap.Modal(modalEl);
        });
    },

    // Form validation
    setupFormValidation: function() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Password confirmation validation
        this.setupPasswordConfirmation();
        this.setupBeneficiaryPercentageValidation();
    },

    // Password confirmation validation
    setupPasswordConfirmation: function() {
        const passwordInputs = document.querySelectorAll('input[name="password"]');
        const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
        
        if (passwordInputs.length > 0 && confirmPasswordInputs.length > 0) {
            const password = passwordInputs[0];
            const confirmPassword = confirmPasswordInputs[0];
            
            const validateMatch = function() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            };
            
            password.addEventListener('input', validateMatch);
            confirmPassword.addEventListener('input', validateMatch);
        }
    },

    // Beneficiary percentage validation
    setupBeneficiaryPercentageValidation: function() {
        const percentageInputs = document.querySelectorAll('input[name="percentage"]');
        
        percentageInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (value < 1 || value > 100) {
                    this.setCustomValidity('Percentage must be between 1 and 100');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    },

    // Payment functions
    payment: {
        initiate: function(memberId, amount, phoneNumber, paymentType = 'monthly') {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<span class="loading-spinner"></span> Processing...';
            button.disabled = true;
            
            const data = {
                member_id: memberId,
                amount: amount,
                phone_number: phoneNumber,
                payment_type: paymentType
            };
            
            fetch('/api/payment/initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ShenaApp.showAlert('success', data.message);
                    // Optionally check payment status after a delay
                    setTimeout(() => {
                        ShenaApp.payment.checkStatus(data.checkout_request_id);
                    }, 10000); // Check after 10 seconds
                } else {
                    ShenaApp.showAlert('error', data.error || 'Payment initiation failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ShenaApp.showAlert('error', 'An error occurred while processing payment');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        },
        
        checkStatus: function(checkoutRequestId) {
            fetch(`/api/payment/status?checkout_request_id=${checkoutRequestId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.status) {
                    const status = data.status;
                    if (status.ResultCode === '0') {
                        ShenaApp.showAlert('success', 'Payment completed successfully!');
                        // Optionally reload page or update UI
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else if (status.ResultCode !== '1037') { // Not still processing
                        ShenaApp.showAlert('error', 'Payment failed: ' + status.ResultDesc);
                    }
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
        }
    },

    focusSmsTarget: function(target) {
        if (!target) return;
        if (target.startsWith('/')) {
            const url = new URL(target, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname !== window.location.pathname || url.search !== window.location.search) { window.location.assign(url.href); return; }
            target = url.hash;
        }
        if (!/^#(?:account-update-sms|sms-review-[a-z0-9-]+)$/.test(target)) return;
        const element = document.getElementById(target.slice(1));
        if (!element) return;
        const focus = element.querySelector('textarea') || element;
        focus.focus({preventScroll: true});
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        element.scrollIntoView({behavior: reduced ? 'auto' : 'smooth', block: 'start'});
    },

    initializeSmsComposers: function() {
        document.querySelectorAll('[data-sms-composer]').forEach(form => {
            if (form.dataset.initialized) return;
            form.dataset.initialized = '1';
            const input = form.querySelector('[data-sms-message]');
            const bubble = form.querySelector('[data-sms-bubble]');
            const count = form.querySelector('[data-sms-count]');
            const update = () => {
                bubble.textContent = input.value;
                const length = Array.from(input.value).length;
                // Conservative estimate; provider encoding may use more segments.
                const unicode = /[^\x20-\x7E\r\n]/.test(input.value);
                const units = unicode ? input.value.length : input.value.length + (input.value.match(/[\^{}\\\[\]~|]/g) || []).length;
                const single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
                const segments = units <= single ? (units ? 1 : 0) : Math.ceil(units / multi);
                count.textContent = `${length}/${input.maxLength} characters - ${segments} SMS segment${segments === 1 ? '' : 's'}`;
            };
            input.addEventListener('input', update); update();
            form.addEventListener('submit', event => {
                if (form.dataset.submitting) { event.preventDefault(); return; }
                const decision = event.submitter?.value || 'send';
                if (decision === 'save') return;
                event.preventDefault();
                const message = decision === 'discard' ? 'Discard this unsent SMS? The saved action will remain.' : 'Send this message to the recipient shown above?\n\n' + input.value;
                ShenaApp.confirmAction(message, () => {
                    form.dataset.submitting = '1';
                    const field = document.createElement('input'); field.type = 'hidden'; field.name = 'decision'; field.value = decision; form.append(field);
                    form.querySelectorAll('button').forEach(button => button.disabled = true);
                    form.submit();
                }, null, {textOnly: true, title: decision === 'discard' ? 'Discard SMS' : 'Review SMS', confirmText: decision === 'discard' ? 'Discard' : 'Send SMS'});
            });
        });
    },

    // Persistent, text-only result dialog; native dialog provides keyboard focus containment.
    feedback: function(messages) {
        const target = [...messages].reverse().find(item => item.target)?.target || '';
        let dialog = document.getElementById('shena-feedback-dialog');
        if (dialog) {
            if (target) dialog.dataset.feedbackTarget = target;
            messages.forEach(item => {
                const p = document.createElement('p'); p.textContent = item.message;
                dialog.querySelector('[data-feedback-messages]').append(p);
            });
            return;
        }
        if (!document.getElementById('shena-feedback-style')) {
            const style = document.createElement('style'); style.id = 'shena-feedback-style';
            style.textContent = '#shena-feedback-dialog::backdrop{background:rgba(0,0,0,.5)} #shena-feedback-dialog button:focus-visible{outline:3px solid #7F3D9E;outline-offset:4px}';
            document.head.append(style);
        }
        const previousFocus = document.activeElement;
        dialog = document.createElement('dialog');
        dialog.id = 'shena-feedback-dialog';
        dialog.dataset.feedbackTarget = target;
        dialog.setAttribute('aria-labelledby', 'shena-feedback-title');
        dialog.style.cssText = 'position:fixed;inset:0;margin:auto;width:min(92vw,560px);max-height:85vh;overflow:auto;border:0;border-radius:12px;padding:24px;box-shadow:0 12px 60px #0005;';
        const title = document.createElement('h2'); title.id = 'shena-feedback-title';
        title.style.fontSize = '1.3rem';
        title.textContent = messages.some(m => m.type === 'error') ? 'Please check this' : 'Update';
        const body = document.createElement('div'); body.dataset.feedbackMessages = '';
        messages.forEach(item => { const p = document.createElement('p'); p.textContent = item.message; p.style.whiteSpace = 'pre-wrap'; body.append(p); });
        const close = document.createElement('button'); close.type = 'button'; close.textContent = 'Got it';
        close.className = 'btn btn-primary'; close.style.minHeight = '44px';
        close.addEventListener('click', () => { if (typeof dialog.close === 'function') dialog.close(); else dialog.remove(); });
        dialog.append(title, body, close); document.body.append(dialog);
        dialog.addEventListener('close', () => { const destination = dialog.dataset.feedbackTarget; dialog.remove(); if (destination) ShenaApp.focusSmsTarget(destination); else if (previousFocus && previousFocus.isConnected) previousFocus.focus(); });
        if (typeof dialog.showModal === 'function') { dialog.showModal(); close.focus(); }
        else { dialog.setAttribute('open', ''); close.addEventListener('click', () => { if (target) ShenaApp.focusSmsTarget(target); else if (previousFocus && previousFocus.isConnected) previousFocus.focus(); }); }
    },

    // Modern Modal System
    confirmAction: function(message, onConfirm, onCancel = null, options = {}) {
        const title = options.title || 'Confirm Action';
        const confirmText = options.confirmText || 'Confirm';
        const cancelText = options.cancelText || 'Cancel';
        const type = options.type || 'primary'; // primary, danger, warning, success

        this.showModal({
            title: title,
            message: message,
            confirmText: confirmText,
            cancelText: cancelText,
            type: type,
            onConfirm: onConfirm,
            onCancel: onCancel,
            textOnly: options.textOnly === true
        });
    },

    alert: function(message, type = 'info', title = null) {
        const titles = {
            success: 'Success!',
            error: 'Error!',
            warning: 'Warning!',
            info: 'Information'
        };
        
        const icons = {
            success: '<i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>',
            error: '<i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>',
            warning: '<i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>',
            info: '<i class="fas fa-info-circle text-info" style="font-size: 3rem;"></i>'
        };

        this.showModal({
            title: title || titles[type] || titles.info,
            message: message,
            icon: icons[type] || icons.info,
            confirmText: 'OK',
            showCancel: false,
            type: type,
            onConfirm: function() {}
        });
    },

    showModal: function(options) {
        // Remove any existing custom modals
        const existingModal = document.getElementById('shena-custom-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const {
            title = 'Confirmation',
            message = '',
            icon = '',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            showCancel = true,
            type = 'primary',
            onConfirm = function() {},
            onCancel = function() {}
        } = options;

        const buttonColors = {
            primary: 'btn-primary',
            danger: 'btn-danger',
            warning: 'btn-warning',
            success: 'btn-success',
            info: 'btn-info',
            error: 'btn-danger'
        };

        const buttonClass = buttonColors[type] || buttonColors.primary;

        const modalHTML = `
            <div class="modal fade" id="shena-custom-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">${options.textOnly ? '' : title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            ${icon ? `<div class="mb-3">${icon}</div>` : ''}
                            <p class="mb-0 fs-6">${options.textOnly ? '' : message}</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pt-0">
                            ${showCancel ? `<button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">${cancelText}</button>` : ''}
                            <button type="button" class="btn ${buttonClass} px-4" id="modal-confirm-btn">${confirmText}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modalElement = document.getElementById('shena-custom-modal');
        if (options.textOnly) {
            modalElement.querySelector('.modal-title').textContent = title;
            modalElement.querySelector('.modal-body p').textContent = message;
        }
        const modal = new bootstrap.Modal(modalElement);
        const confirmBtn = document.getElementById('modal-confirm-btn');

        // Handle confirm
        confirmBtn.addEventListener('click', function() {
            modal.hide();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });

        // Handle cancel
        modalElement.addEventListener('hidden.bs.modal', function() {
            if (!confirmBtn.hasAttribute('data-confirmed') && typeof onCancel === 'function') {
                onCancel();
            }
            modalElement.remove();
        });

        confirmBtn.addEventListener('click', function() {
            this.setAttribute('data-confirmed', 'true');
        });

        // Show modal
        modal.show();
    },

    showNotification: function(message, type = 'info', duration = 3000) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const colors = {
            success: 'bg-success',
            error: 'bg-danger', 
            warning: 'bg-warning',
            info: 'bg-info'
        };

        const notificationHTML = `
            <div class="shena-notification ${colors[type] || colors.info} text-white shadow-lg animate__animated animate__fadeInRight" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 8px; padding: 15px 20px;">
                <div class="d-flex align-items-center">
                    <i class="fas ${icons[type] || icons.info} me-2 fs-5"></i>
                    <span class=" grow">${message}</span>
                    <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', notificationHTML);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(function() {
                const notification = document.querySelector('.shena-notification:last-of-type');
                if (notification) {
                    notification.classList.add('animate__fadeOutRight');
                    setTimeout(() => notification.remove(), 500);
                }
            }, duration);
        }
    },

    // Utility functions
    showAlert: function(type, message) {
        const alertClass = type === 'error' ? 'alert-danger' : `alert-${type}`;
        const icon = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle',
            warning: 'fa-exclamation-triangle'
        }[type] || 'fa-info-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert at top of main content or body
        const container = document.querySelector('main') || document.body;
        container.insertAdjacentHTML('afterbegin', alertHtml);
    },

    // Format currency
    formatCurrency: function(amount, currency = 'KES') {
        return `${currency} ${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    },

    // Format date
    formatDate: function(dateString, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        const formatOptions = { ...defaultOptions, ...options };
        return new Date(dateString).toLocaleDateString('en-US', formatOptions);
    },

    // Copy to clipboard
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showAlert('success', 'Copied to clipboard!');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showAlert('success', 'Copied to clipboard!');
        });
    }
};

// Override native alerts to use ShenaApp modal when available
(function() {
    const nativeAlert = window.alert;

    window.alert = function(message) {
        if (window.ShenaApp && typeof ShenaApp.alert === 'function') {
            ShenaApp.alert(String(message), 'info');
            return;
        }
        nativeAlert(message);
    };
})();

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ShenaApp.init();
});

// Export for global use
window.ShenaApp = ShenaApp;
