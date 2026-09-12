import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { setCustomSelectValue } from './custom-select.js';

const ROLE_DESCRIPTIONS = {
    owner: 'Повний доступ до всіх розділів та налаштувань системи.',
    manager: 'Бачить тільки свої угоди та клієнтів.',
    admin: 'Керує командою, товарами та налаштуваннями.',
    accountant: 'Доступ до фінансів, звітів та дебіторки.',
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function renderAvatar(container, member, size) {
    if (member.avatar_url) {
        container.innerHTML = `<img src="${member.avatar_url}" width="${size}" height="${size}" class="rounded-circle object-fit-cover" alt="">`;
    } else {
        const fontSize = Math.round(size / 2.5);
        container.innerHTML = `<span class="avatar-initials" style="width:${size}px;height:${size}px;background-color:${member.avatar_color};font-size:${fontSize}px;">${member.initials}</span>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // View toggle (table/grid), persisted in localStorage
    const tableView = document.getElementById('teamTableView');
    const gridView = document.getElementById('teamGridView');
    const viewTableBtn = document.getElementById('viewTableBtn');
    const viewGridBtn = document.getElementById('viewGridBtn');

    if (tableView && gridView) {
        const applyView = (view) => {
            if (view === 'grid') {
                tableView.classList.add('d-none');
                gridView.classList.remove('d-none');
                viewGridBtn?.classList.add('active');
                viewTableBtn?.classList.remove('active');
            } else {
                gridView.classList.add('d-none');
                tableView.classList.remove('d-none');
                viewTableBtn?.classList.add('active');
                viewGridBtn?.classList.remove('active');
            }
        };
        applyView(localStorage.getItem('teamView') || 'table');
        viewTableBtn?.addEventListener('click', () => {
            localStorage.setItem('teamView', 'table');
            applyView('table');
        });
        viewGridBtn?.addEventListener('click', () => {
            localStorage.setItem('teamView', 'grid');
            applyView('grid');
        });
    }

    // Add/Edit offcanvas
    const formOffcanvasEl = document.getElementById('memberFormOffcanvas');
    const formOffcanvas = formOffcanvasEl ? new bootstrap.Offcanvas(formOffcanvasEl) : null;
    const form = document.getElementById('memberForm');
    const formMethod = document.getElementById('memberFormMethod');
    const formTitle = document.getElementById('memberFormTitle');
    const avatarPreview = document.getElementById('memberAvatarPreview');
    const avatarPlaceholder = document.getElementById('memberDropzonePlaceholder');
    const avatarInput = document.getElementById('memberAvatarInput');
    const roleSelect = document.getElementById('field_member_role');
    const roleDescriptionEl = document.getElementById('roleDescription');
    const sendInviteWrapper = document.getElementById('sendInviteWrapper');
    const submitBtn = document.getElementById('memberFormSubmitBtn');

    const storeUrl = form?.dataset.storeUrl;

    function resetForm() {
        form.reset();
        form.action = storeUrl;
        formMethod.value = 'POST';
        document.getElementById('field_member_id').value = '';
        formTitle.textContent = 'Додати співробітника';
        avatarPreview.classList.add('d-none');
        avatarPreview.src = '';
        avatarPlaceholder.classList.remove('d-none');
        setCustomSelectValue(roleSelect, 'manager');
        setCustomSelectValue(document.getElementById('field_member_department'), '');
        document.getElementById('field_member_is_active').checked = true;
        sendInviteWrapper.classList.remove('d-none');
        document.getElementById('field_send_invite').checked = true;
        submitBtn.textContent = 'Надіслати запрошення';
    }

    function fillForm(member) {
        formTitle.textContent = 'Редагувати співробітника';
        form.action = member.update_url;
        formMethod.value = 'PUT';
        document.getElementById('field_member_id').value = member.id;
        document.getElementById('field_member_name').value = member.name || '';
        document.getElementById('field_member_email').value = member.email || '';
        document.getElementById('field_member_phone').value = member.phone || '';
        setCustomSelectValue(roleSelect, member.role);
        setCustomSelectValue(document.getElementById('field_member_department'), member.department || '');
        document.getElementById('field_member_hire_date').value = member.hire_date || '';
        document.getElementById('field_member_is_active').checked = !!member.is_active;
        sendInviteWrapper.classList.add('d-none');
        submitBtn.textContent = 'Зберегти';

        if (member.avatar_url) {
            avatarPreview.src = member.avatar_url;
            avatarPreview.classList.remove('d-none');
            avatarPlaceholder.classList.add('d-none');
        } else {
            avatarPreview.classList.add('d-none');
            avatarPlaceholder.classList.remove('d-none');
        }
    }

    document.getElementById('addMemberBtn')?.addEventListener('click', () => {
        resetForm();
        formOffcanvas?.show();
    });
    document.getElementById('emptyAddMemberBtn')?.addEventListener('click', () => {
        resetForm();
        formOffcanvas?.show();
    });

    if (formOffcanvasEl?.dataset.openOnLoad) {
        formOffcanvas?.show();
    }

    // Role description text
    roleSelect?.addEventListener('change', () => {
        roleDescriptionEl.textContent = ROLE_DESCRIPTIONS[roleSelect.value] || '';
    });

    // Avatar dropzone
    const dropzone = document.getElementById('memberDropzone');

    function showAvatarPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.src = e.target.result;
            avatarPreview.classList.remove('d-none');
            avatarPlaceholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }
    avatarInput?.addEventListener('change', () => showAvatarPreview(avatarInput.files[0]));
    ['dragover', 'dragenter'].forEach((evt) => {
        dropzone?.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach((evt) => {
        dropzone?.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });
    });
    dropzone?.addEventListener('drop', (e) => {
        const file = e.dataTransfer.files[0];
        if (file) {
            avatarInput.files = e.dataTransfer.files;
            showAvatarPreview(file);
        }
    });

    // Phone mask (simple UA format)
    const phoneInput = document.getElementById('field_member_phone');
    phoneInput?.addEventListener('input', () => {
        let digits = phoneInput.value.replace(/\D/g, '');
        if (digits.startsWith('380')) digits = digits.slice(3);
        digits = digits.slice(0, 9);
        let formatted = '+380';
        if (digits.length) formatted += ' ' + digits.slice(0, 2);
        if (digits.length > 2) formatted += ' ' + digits.slice(2, 5);
        if (digits.length > 5) formatted += ' ' + digits.slice(5, 7);
        if (digits.length > 7) formatted += ' ' + digits.slice(7, 9);
        phoneInput.value = formatted;
    });

    // Details offcanvas
    const detailsOffcanvasEl = document.getElementById('memberDetailsOffcanvas');
    const detailsOffcanvas = detailsOffcanvasEl ? new bootstrap.Offcanvas(detailsOffcanvasEl) : null;
    let currentDetailsMember = null;

    function openDetails(member) {
        currentDetailsMember = member;
        renderAvatar(document.getElementById('detailsAvatarWrapper'), member, 80);
        document.getElementById('detailsName').textContent = member.name;
        const badge = document.getElementById('detailsRoleBadge');
        badge.className = 'badge ' + member.role_badge;
        badge.textContent = member.role_label;
        document.getElementById('detailsEmail').textContent = member.email;
        document.getElementById('detailsPhone').textContent = member.phone || '—';

        const seed = member.id;
        document.getElementById('detailsClients').textContent = ((seed * 5) % 30) + 3;
        document.getElementById('detailsDeals').textContent = member.deals_in_progress;
        document.getElementById('detailsClosed').textContent = ((seed * 2) % 12) + 1;
        document.getElementById('detailsRevenue').textContent = '₴' + (((seed * 1300) % 90000) + 15000).toLocaleString('uk-UA');

        const blockBtn = document.getElementById('detailsBlockBtn');
        blockBtn.innerHTML = member.is_active
            ? '<i class="ti ti-lock me-1"></i>Заблокувати'
            : '<i class="ti ti-lock-open me-1"></i>Активувати';
        blockBtn.disabled = !!member.is_self;

        detailsOffcanvas?.show();
    }

    document.querySelectorAll('.team-row, .team-card').forEach((el) => {
        el.addEventListener('click', () => {
            const member = JSON.parse(el.dataset.member);
            openDetails(member);
        });
    });

    document.getElementById('detailsEditBtn')?.addEventListener('click', () => {
        if (!currentDetailsMember) return;
        detailsOffcanvas?.hide();
        fillForm(currentDetailsMember);
        formOffcanvas?.show();
    });

    document.getElementById('detailsResetPasswordBtn')?.addEventListener('click', () => {
        if (!currentDetailsMember) return;
        const resetForm = document.getElementById('reset-password-details-form');
        resetForm.action = currentDetailsMember.reset_password_url;
        resetForm.submit();
    });

    document.getElementById('detailsBlockBtn')?.addEventListener('click', () => {
        if (!currentDetailsMember || currentDetailsMember.is_self) return;
        toggleActiveRequest(currentDetailsMember.id).then(() => {
            window.location.reload();
        });
    });

    // Edit buttons (dropdown items)
    document.querySelectorAll('.edit-member-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const row = btn.closest('[data-member]');
            const member = JSON.parse(row.dataset.member);
            fillForm(member);
            formOffcanvas?.show();
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-member-form').forEach((deleteForm) => {
        deleteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Видалити співробітника?',
                text: 'Цю дію неможливо скасувати.',
                showCancelButton: true,
                confirmButtonText: 'Видалити',
                cancelButtonText: 'Скасувати',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteForm.submit();
                }
            });
        });
    });

    // Toggle active (checkbox switches in table)
    function toggleActiveRequest(id) {
        return fetch(`/team/${id}/toggle-active`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
        }).then((res) => {
            if (!res.ok) throw new Error('failed');
            return res.json();
        });
    }

    document.querySelectorAll('.toggle-active').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            toggleActiveRequest(checkbox.dataset.id).catch(() => {
                checkbox.checked = !checkbox.checked;
            });
        });
    });

    document.querySelectorAll('.toggle-active-menu-item').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleActiveRequest(btn.dataset.id).then(() => window.location.reload());
        });
    });

    // Debounced search auto-submit
    const searchInput = document.querySelector('input[name="search"]');
    let searchTimeout;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchInput.form.requestSubmit(), 400);
    });
});
