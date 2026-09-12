import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { setCustomSelectValue } from './custom-select.js';

document.addEventListener('DOMContentLoaded', () => {
    const tableView = document.getElementById('productsTableView');
    const gridView = document.getElementById('productsGridView');
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

        applyView(localStorage.getItem('productsView') || 'table');

        viewTableBtn?.addEventListener('click', () => {
            localStorage.setItem('productsView', 'table');
            applyView('table');
        });
        viewGridBtn?.addEventListener('click', () => {
            localStorage.setItem('productsView', 'grid');
            applyView('grid');
        });
    }

    // Offcanvas add/edit form
    const offcanvasEl = document.getElementById('productOffcanvas');
    const offcanvas = offcanvasEl ? new bootstrap.Offcanvas(offcanvasEl) : null;
    const form = document.getElementById('productForm');
    const formMethod = document.getElementById('formMethod');
    const offcanvasTitle = document.getElementById('offcanvasTitle');
    const imagePreview = document.getElementById('imagePreview');
    const dropzonePlaceholder = document.getElementById('dropzonePlaceholder');
    const imageInput = document.getElementById('imageInput');
    const categorySelect = document.getElementById('field_category_id');
    const newCategoryInput = document.getElementById('field_new_category');

    const storeUrl = form?.dataset.storeUrl;

    function resetForm() {
        form.reset();
        form.action = storeUrl;
        formMethod.value = 'POST';
        document.getElementById('field_product_id').value = '';
        offcanvasTitle.textContent = 'Додати товар';
        imagePreview.classList.add('d-none');
        imagePreview.src = '';
        dropzonePlaceholder.classList.remove('d-none');
        newCategoryInput.classList.add('d-none');
        newCategoryInput.value = '';
        document.getElementById('field_is_active').checked = true;
        document.getElementById('field_min_stock_alert').value = 5;
        setCustomSelectValue(categorySelect, categorySelect.value);
        setCustomSelectValue(document.getElementById('field_unit'), 'шт');
    }

    function fillForm(product) {
        offcanvasTitle.textContent = 'Редагувати товар';
        form.action = product.update_url;
        formMethod.value = 'PUT';
        document.getElementById('field_product_id').value = product.id;
        document.getElementById('field_name').value = product.name || '';
        setCustomSelectValue(categorySelect, product.category_id);
        document.getElementById('field_description').value = product.description || '';
        document.getElementById('field_price').value = product.price || '';
        document.getElementById('field_wholesale_price').value = product.wholesale_price || '';
        setCustomSelectValue(document.getElementById('field_unit'), product.unit || 'шт');
        document.getElementById('field_sku').value = product.sku || '';
        document.getElementById('field_stock_quantity').value = product.stock_quantity ?? 0;
        document.getElementById('field_min_stock_alert').value = product.min_stock_alert ?? 5;
        document.getElementById('field_is_active').checked = !!product.is_active;
        newCategoryInput.classList.add('d-none');

        if (product.image_url) {
            imagePreview.src = product.image_url;
            imagePreview.classList.remove('d-none');
            dropzonePlaceholder.classList.add('d-none');
        } else {
            imagePreview.classList.add('d-none');
            dropzonePlaceholder.classList.remove('d-none');
        }
    }

    document.getElementById('addProductBtn')?.addEventListener('click', () => {
        resetForm();
        offcanvas?.show();
    });
    document.getElementById('emptyAddProductBtn')?.addEventListener('click', () => {
        resetForm();
        offcanvas?.show();
    });

    if (offcanvasEl?.dataset.openOnLoad) {
        offcanvas?.show();
    }

    document.querySelectorAll('.product-row, .product-card').forEach((el) => {
        el.addEventListener('click', () => {
            const product = JSON.parse(el.dataset.product);
            fillForm(product);
            offcanvas?.show();
        });
    });

    document.querySelectorAll('.edit-product-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const row = btn.closest('[data-product]');
            const product = JSON.parse(row.dataset.product);
            fillForm(product);
            offcanvas?.show();
        });
    });

    // Category "add new" toggle
    categorySelect?.addEventListener('change', () => {
        if (categorySelect.value === '__new__') {
            newCategoryInput.classList.remove('d-none');
            newCategoryInput.focus();
        } else {
            newCategoryInput.classList.add('d-none');
            newCategoryInput.value = '';
        }
    });

    // Dropzone drag & drop + preview
    const dropzone = document.getElementById('dropzone');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('d-none');
            dropzonePlaceholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }

    imageInput?.addEventListener('change', () => showPreview(imageInput.files[0]));

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
            imageInput.files = e.dataTransfer.files;
            showPreview(file);
        }
    });

    // Delete confirmation
    document.querySelectorAll('.delete-product-form').forEach((deleteForm) => {
        deleteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Видалити товар?',
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

    // Toggle active status (AJAX)
    document.querySelectorAll('.toggle-active').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const id = checkbox.dataset.id;
            fetch(`/products/${id}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                },
            })
                .then((res) => {
                    if (!res.ok) throw new Error('failed');
                    return res.json();
                })
                .catch(() => {
                    checkbox.checked = !checkbox.checked;
                });
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
