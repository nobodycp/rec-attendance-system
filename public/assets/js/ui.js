(function () {
    'use strict';

    /* Modal helpers */
    window.openModal = function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    document.addEventListener('click', function (e) {
        var closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            var modal = closeBtn.closest('.modal');
            if (modal) closeModal(modal.id);
            return;
        }

        var modal = e.target.classList && e.target.classList.contains('modal') ? e.target : null;
        if (modal && e.target === modal) {
            closeModal(modal.id);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal.is-open').forEach(function (m) {
            closeModal(m.id);
        });
    });

    document.addEventListener('click', function (e) {
        var um = e.target.closest('details.rd-user-menu');
        document.querySelectorAll('details.rd-user-menu[open]').forEach(function (openMenu) {
            if (openMenu !== um) {
                openMenu.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('details.rd-user-menu[open]').forEach(function (m) {
            m.removeAttribute('open');
        });
    });

    /* Auto-dismiss alerts after 5s */
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 300);
        }, 5000);
    });
})();
