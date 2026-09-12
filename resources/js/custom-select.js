function syncCustomSelect(wrapper) {
    const select = wrapper.querySelector('select');
    const label = wrapper.querySelector('.custom-select-label');
    if (!select || !label) return;

    const items = wrapper.querySelectorAll('.dropdown-item[data-value]');
    let matched = false;

    items.forEach((item) => {
        const isMatch = item.dataset.value === select.value;
        item.classList.toggle('active', isMatch);
        if (isMatch) {
            label.textContent = item.textContent.trim();
            matched = true;
        }
    });

    if (!matched && select.selectedOptions.length) {
        label.textContent = select.selectedOptions[0].textContent.trim();
    }
}

export function setCustomSelectValue(select, value) {
    select.value = value;
    const wrapper = select.closest('[data-custom-select]');
    if (wrapper) syncCustomSelect(wrapper);
    select.dispatchEvent(new Event('change', { bubbles: true }));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-custom-select]').forEach(syncCustomSelect);
});

document.addEventListener('click', (e) => {
    const item = e.target.closest('[data-custom-select] .dropdown-item[data-value]');
    if (!item) return;

    e.preventDefault();
    const wrapper = item.closest('[data-custom-select]');
    const select = wrapper.querySelector('select');
    if (!select) return;

    setCustomSelectValue(select, item.dataset.value);
});

// Keep visuals in sync if a select's value is changed programmatically elsewhere.
document.addEventListener('change', (e) => {
    const wrapper = e.target.closest?.('[data-custom-select]');
    if (wrapper) syncCustomSelect(wrapper);
});
