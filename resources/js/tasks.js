import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { setCustomSelectValue } from './custom-select.js';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function apiFetch(url, options = {}) {
    return fetch(url, {
        ...options,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            ...(options.headers || {}),
        },
    }).then((res) => {
        if (!res.ok) throw new Error('request failed');
        return res.json();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ---- View toggle (kanban/list) ----
    const kanbanView = document.getElementById('tasksKanbanView');
    const listView = document.getElementById('tasksListView');
    const viewKanbanBtn = document.getElementById('viewKanbanBtn');
    const viewListBtn = document.getElementById('viewListBtn');

    if (kanbanView && listView) {
        const applyView = (view) => {
            if (view === 'list') {
                kanbanView.classList.add('d-none');
                listView.classList.remove('d-none');
                viewListBtn?.classList.add('active');
                viewKanbanBtn?.classList.remove('active');
            } else {
                listView.classList.add('d-none');
                kanbanView.classList.remove('d-none');
                viewKanbanBtn?.classList.add('active');
                viewListBtn?.classList.remove('active');
            }
        };
        applyView(localStorage.getItem('tasksView') || 'kanban');
        viewKanbanBtn?.addEventListener('click', () => {
            localStorage.setItem('tasksView', 'kanban');
            applyView('kanban');
        });
        viewListBtn?.addEventListener('click', () => {
            localStorage.setItem('tasksView', 'list');
            applyView('list');
        });
    }

    // ---- Drag & drop between kanban columns ----
    let draggedCard = null;

    document.querySelectorAll('.task-card').forEach((card) => {
        card.addEventListener('dragstart', () => {
            draggedCard = card;
            setTimeout(() => card.classList.add('dragging'), 0);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });
    });

    document.querySelectorAll('.kanban-column-body').forEach((column) => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
            column.classList.add('drag-over');
        });
        column.addEventListener('dragleave', () => {
            column.classList.remove('drag-over');
        });
        column.addEventListener('drop', (e) => {
            e.preventDefault();
            column.classList.remove('drag-over');
            if (!draggedCard) return;

            const status = column.dataset.status;
            const taskId = draggedCard.dataset.taskId;
            const emptyState = column.querySelector('.kanban-empty');
            emptyState?.remove();
            column.appendChild(draggedCard);
            updateColumnCounts();

            apiFetch(`/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status }),
            }).catch(() => window.location.reload());
        });
    });

    function updateColumnCounts() {
        document.querySelectorAll('.kanban-column').forEach((col) => {
            const status = col.querySelector('.kanban-column-body')?.dataset.status;
            const count = col.querySelectorAll('.task-card').length;
            const badge = col.querySelector('.column-count-badge');
            if (badge) badge.textContent = count;

            const body = col.querySelector('.kanban-column-body');
            if (body && count === 0 && !body.querySelector('.kanban-empty')) {
                const div = document.createElement('div');
                div.className = 'kanban-empty';
                div.textContent = 'Перетягніть задачу сюди';
                body.appendChild(div);
            }
        });
    }

    // ---- Quick create modal ----
    const createModalEl = document.getElementById('taskCreateModal');
    const createModal = createModalEl ? new bootstrap.Modal(createModalEl) : null;
    const moreFieldsToggle = document.getElementById('toggleMoreFields');
    const moreFieldsWrapper = document.getElementById('moreFieldsWrapper');

    moreFieldsToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        moreFieldsWrapper.classList.toggle('d-none');
        moreFieldsToggle.textContent = moreFieldsWrapper.classList.contains('d-none') ? 'Більше полів' : 'Менше полів';
    });

    document.querySelectorAll('.quick-add-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('quickCreateStatus').value = btn.dataset.status;
            createModal?.show();
        });
    });
    document.getElementById('newTaskBtn')?.addEventListener('click', () => {
        document.getElementById('quickCreateStatus').value = 'new';
        createModal?.show();
    });

    // ---- Details offcanvas ----
    const detailsOffcanvasEl = document.getElementById('taskDetailsOffcanvas');
    const detailsOffcanvas = detailsOffcanvasEl ? new bootstrap.Offcanvas(detailsOffcanvasEl) : null;
    const detailsForm = document.getElementById('taskDetailsForm');
    let currentTask = null;

    function fillDetails(task) {
        currentTask = task;
        detailsForm.action = task.update_url;
        document.getElementById('detailsTitleInput').value = task.title;
        document.getElementById('detailsDescription').value = task.description || '';
        setCustomSelectValue(document.getElementById('detailsAssignee'), task.assignee_id || '');
        setCustomSelectValue(document.getElementById('detailsPriority'), task.priority);
        setCustomSelectValue(document.getElementById('detailsStatus'), task.status);
        setCustomSelectValue(document.getElementById('detailsTag'), task.tag || '');
        document.getElementById('detailsClientName').value = task.client_name || '';
        document.getElementById('detailsDueDate').value = task.due_date_input || '';
        document.getElementById('detailsCreator').textContent = 'Створив: ' + (task.creator_name || '—');

        renderChecklist(task.checklist_items || []);
        renderComments(task.comments || []);
        renderAttachments(task.attachments || []);

        document.getElementById('detailsTaskId').value = task.id;
        document.getElementById('deleteTaskForm').action = task.destroy_url;

        detailsOffcanvas?.show();
    }

    document.querySelectorAll('.task-card, .task-row').forEach((el) => {
        el.addEventListener('click', (e) => {
            if (e.target.closest('a, button, input')) return;
            const task = JSON.parse(el.dataset.task);
            fillDetails(task);
        });
    });

    // ---- Checklist ----
    const checklistList = document.getElementById('checklistItems');
    const checklistProgress = document.getElementById('checklistProgress');
    const checklistInput = document.getElementById('newChecklistItem');

    function checklistItemHtml(item) {
        return `<div class="d-flex align-items-center gap-2 checklist-item" data-id="${item.id}">
            <input type="checkbox" class="form-check-input checklist-toggle" ${item.is_done ? 'checked' : ''}>
            <span class="flex-grow-1 small ${item.is_done ? 'text-decoration-line-through text-muted' : ''}">${item.title}</span>
            <button type="button" class="btn btn-sm btn-icon text-muted checklist-delete"><i class="ti ti-x"></i></button>
        </div>`;
    }

    function renderChecklist(items) {
        checklistList.innerHTML = items.map(checklistItemHtml).join('');
        updateChecklistProgress(items);
        bindChecklistEvents();
    }

    function updateChecklistProgress(items) {
        const done = items.filter((i) => i.is_done).length;
        checklistProgress.textContent = `${done}/${items.length} виконано`;
    }

    function bindChecklistEvents() {
        checklistList.querySelectorAll('.checklist-toggle').forEach((cb) => {
            cb.addEventListener('change', () => {
                const row = cb.closest('.checklist-item');
                apiFetch(`/checklist-items/${row.dataset.id}/toggle`, { method: 'PATCH' }).then((data) => {
                    row.querySelector('span').classList.toggle('text-decoration-line-through', data.is_done);
                    row.querySelector('span').classList.toggle('text-muted', data.is_done);
                    checklistProgress.textContent = data.progress + ' виконано';
                });
            });
        });
        checklistList.querySelectorAll('.checklist-delete').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('.checklist-item');
                apiFetch(`/checklist-items/${row.dataset.id}`, { method: 'DELETE' }).then((data) => {
                    row.remove();
                    checklistProgress.textContent = data.progress + ' виконано';
                });
            });
        });
    }

    document.getElementById('addChecklistItemBtn')?.addEventListener('click', () => {
        const title = checklistInput.value.trim();
        if (!title || !currentTask) return;

        apiFetch(`/tasks/${currentTask.id}/checklist`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title }),
        }).then((data) => {
            checklistList.insertAdjacentHTML('beforeend', checklistItemHtml(data.item));
            bindChecklistEvents();
            checklistInput.value = '';
            checklistProgress.textContent = data.progress + ' виконано';
        });
    });

    // ---- Comments ----
    const commentsList = document.getElementById('commentsList');
    const commentInput = document.getElementById('newComment');

    function commentHtml(comment) {
        return `<div class="mb-3">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold small">${comment.user_name}</span>
                <span class="text-muted small">${comment.created_at}</span>
            </div>
            <div class="small">${comment.body}</div>
        </div>`;
    }

    function renderComments(comments) {
        commentsList.innerHTML = comments.map(commentHtml).join('') || '<p class="small text-muted">Коментарів ще немає.</p>';
    }

    document.getElementById('addCommentBtn')?.addEventListener('click', () => {
        const body = commentInput.value.trim();
        if (!body || !currentTask) return;

        apiFetch(`/tasks/${currentTask.id}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body }),
        }).then((comment) => {
            if (commentsList.querySelector('p')) commentsList.innerHTML = '';
            commentsList.insertAdjacentHTML('afterbegin', commentHtml(comment));
            commentInput.value = '';
        });
    });

    // ---- Attachments ----
    const attachmentsList = document.getElementById('attachmentsList');
    const attachmentInput = document.getElementById('attachmentInput');

    function attachmentHtml(attachment) {
        return `<div class="d-flex align-items-center justify-content-between mb-2" data-id="${attachment.id}">
            <a href="${attachment.url}" target="_blank" class="small text-truncate"><i class="ti ti-paperclip me-1"></i>${attachment.name}</a>
            <button type="button" class="btn btn-sm btn-icon text-muted attachment-delete"><i class="ti ti-x"></i></button>
        </div>`;
    }

    function renderAttachments(attachments) {
        attachmentsList.innerHTML = attachments.map(attachmentHtml).join('');
        bindAttachmentEvents();
    }

    function bindAttachmentEvents() {
        attachmentsList.querySelectorAll('.attachment-delete').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('[data-id]');
                apiFetch(`/attachments/${row.dataset.id}`, { method: 'DELETE' }).then(() => row.remove());
            });
        });
    }

    attachmentInput?.addEventListener('change', () => {
        const file = attachmentInput.files[0];
        if (!file || !currentTask) return;

        const formData = new FormData();
        formData.append('file', file);

        fetch(`/tasks/${currentTask.id}/attachments`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
            body: formData,
        })
            .then((res) => res.json())
            .then((attachment) => {
                attachmentsList.insertAdjacentHTML('beforeend', attachmentHtml(attachment));
                bindAttachmentEvents();
                attachmentInput.value = '';
            });
    });

    // ---- Delete task ----
    function confirmDelete(e) {
        e.preventDefault();
        const deleteForm = e.target;
        Swal.fire({
            icon: 'warning',
            title: 'Видалити задачу?',
            text: 'Цю дію неможливо скасувати.',
            showCancelButton: true,
            confirmButtonText: 'Видалити',
            cancelButtonText: 'Скасувати',
            confirmButtonColor: '#dc3545',
        }).then((result) => {
            if (result.isConfirmed) deleteForm.submit();
        });
    }

    document.getElementById('deleteTaskForm')?.addEventListener('submit', confirmDelete);
    document.querySelectorAll('.delete-task-form-list').forEach((form) => {
        form.addEventListener('submit', confirmDelete);
    });

    // ---- Debounced search auto-submit ----
    const searchInput = document.querySelector('input[name="search"]');
    let searchTimeout;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchInput.form.requestSubmit(), 400);
    });
});
