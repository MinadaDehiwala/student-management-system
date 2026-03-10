(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function wireDeleteConfirmations() {
        document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var message = form.getAttribute('data-confirm-message') || 'Delete this student record? This cannot be undone.';
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    }

    function wireAlerts() {
        document.querySelectorAll('[data-dismiss-alert]').forEach(function (button) {
            button.addEventListener('click', function () {
                var alert = button.closest('.js-alert');
                if (!alert) {
                    return;
                }

                alert.classList.add('is-hidden');
                window.setTimeout(function () {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 220);
            });
        });
    }

    function animateCounters() {
        if (prefersReducedMotion) {
            document.querySelectorAll('[data-count]').forEach(function (node) {
                node.textContent = node.getAttribute('data-count') || '0';
            });
            return;
        }

        document.querySelectorAll('[data-count]').forEach(function (node) {
            var target = Number(node.getAttribute('data-count') || 0);
            if (!Number.isFinite(target)) {
                return;
            }

            var duration = 900;
            var startTs = null;

            function tick(ts) {
                if (startTs === null) {
                    startTs = ts;
                }
                var progress = Math.min((ts - startTs) / duration, 1);
                var value = Math.floor(target * progress);
                node.textContent = String(value);

                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    node.textContent = String(target);
                }
            }

            window.requestAnimationFrame(tick);
        });
    }

    function wireFormHints() {
        document.querySelectorAll('form[data-validate-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                form.querySelectorAll('input, select, textarea').forEach(function (field) {
                    if (field.hasAttribute('required') && field.value.trim() === '') {
                        field.classList.add('field-invalid');
                    } else {
                        field.classList.remove('field-invalid');
                    }
                });
            });
        });
    }

    function animateOnLoad() {
        if (prefersReducedMotion) {
            return;
        }

        document.querySelectorAll('.stat-card, .form-card, .result-card').forEach(function (card, index) {
            card.style.animationDelay = String(Math.min(index * 70, 400)) + 'ms';
            card.classList.add('animate-in');
        });
    }

    wireDeleteConfirmations();
    wireAlerts();
    animateCounters();
    wireFormHints();
    animateOnLoad();
})();
