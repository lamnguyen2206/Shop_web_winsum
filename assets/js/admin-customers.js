/**
 * Handlers quản lý khách hàng (admin).
 */
(function () {
    'use strict';

    const cfg = window.adminCustomersConfig || {};
    const listBase = cfg.listUrl || 'index.php?view=admin-customers';

    function buildDetailUrl(id) {
        const joiner = listBase.indexOf('?') >= 0 ? '&' : '?';
        return listBase + joiner + 'id=' + encodeURIComponent(String(id));
    }

    function submitPost(action, customerId) {
        const form = document.getElementById('admin-customer-action-form');
        if (!form) {
            return;
        }
        const actionInput = form.querySelector('[name="action"]');
        const idInput = form.querySelector('[name="customer_id"]');
        if (!actionInput || !idInput) {
            return;
        }
        actionInput.value = action;
        idInput.value = String(customerId);
        form.submit();
    }

    function getModal() {
        return document.getElementById('customer-delete-modal');
    }

    window.AdminCustomers = {
        onView(customerId) {
            window.location.href = buildDetailUrl(customerId);
        },

        onEdit(customerId) {
            window.location.href = buildDetailUrl(customerId) + '#customer-edit';
        },

        onToggleBlock(customerId) {
            submitPost('toggle_customer_block', customerId);
        },

        onDelete(customerId, ev) {
            const modal = getModal();
            const idInput = document.getElementById('customer-delete-id');
            const idBackup = document.getElementById('customer-delete-id-backup');
            const nameEl = document.getElementById('customer-delete-name');
            const btn = ev && ev.currentTarget ? ev.currentTarget : null;
            const resolvedId = String(
                btn && btn.dataset && btn.dataset.deleteId ? btn.dataset.deleteId : customerId
            );
            if (!modal || !idInput || !resolvedId || resolvedId === '0') {
                return;
            }
            idInput.value = resolvedId;
            if (idBackup) {
                idBackup.value = resolvedId;
            }
            if (nameEl) {
                const row = document.querySelector(
                    '[data-customer-id="' + customerId + '"] [data-customer-name]'
                );
                const detailName = document.querySelector('[data-customer-detail-name]');
                if (row) {
                    nameEl.textContent = row.textContent.trim();
                } else if (detailName) {
                    nameEl.textContent = detailName.textContent.trim();
                } else {
                    nameEl.textContent = 'khách hàng này';
                }
            }
            modal.hidden = false;
            document.body.classList.add('admin-modal-open');
        },

        closeDeleteModal() {
            const modal = getModal();
            if (modal) {
                modal.hidden = true;
            }
            document.body.classList.remove('admin-modal-open');
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        const modal = getModal();
        if (!modal) {
            return;
        }

        modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () {
                window.AdminCustomers.closeDeleteModal();
            });
        });

        modal.addEventListener('click', function (e) {
            if (e.target.classList.contains('admin-modal-backdrop')) {
                window.AdminCustomers.closeDeleteModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.hidden) {
                window.AdminCustomers.closeDeleteModal();
            }
        });

        const deleteForm = document.getElementById('customer-delete-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function (e) {
                const idInput = document.getElementById('customer-delete-id');
                const idBackup = document.getElementById('customer-delete-id-backup');
                const id = (idInput && idInput.value) || (idBackup && idBackup.value) || '';
                if (!id || id === '0') {
                    e.preventDefault();
                    window.alert('Không xác định được khách hàng. Vui lòng bấm biểu tượng thùng rác trên dòng khách hàng trước.');
                } else if (idInput) {
                    idInput.value = id;
                }
            });
        }

        if (window.location.hash === '#customer-edit') {
            const crud = document.getElementById('customer-edit');
            if (crud) {
                crud.scrollIntoView({ behavior: 'smooth', block: 'start' });
                const focusEl = crud.querySelector('select, input, button');
                if (focusEl) {
                    focusEl.focus();
                }
            }
        }
    });
})();
