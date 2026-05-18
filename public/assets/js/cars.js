document.addEventListener('DOMContentLoaded', () => {
    const carForm = document.querySelector('.js-car-form');

    if (carForm) {
        carForm.addEventListener('submit', (event) => {
            if (!validateCarForm(carForm)) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('.js-delete-member').forEach((button) => {
        button.addEventListener('click', () => deleteMember(button));
    });
});

function validateCarForm(form) {
    clearFormErrors(form);
    let valid = true;

    valid = requireValue(form, 'name', 'Name is required.') && valid;
    valid = requireValue(form, 'model', 'Model is required.') && valid;
    valid = requireValue(form, 'type', 'Type is required.') && valid;
    valid = requireValue(form, 'price_per_day', 'Price per day is required.') && valid;

    const price = Number(form.elements.price_per_day.value);

    if (!Number.isFinite(price) || price <= 0) {
        showFieldError(form.elements.price_per_day, 'Price per day must be greater than zero.');
        valid = false;
    }

    const image = form.elements.image;

    if (image && image.files.length > 0) {
        const file = image.files[0];
        const allowed = ['image/jpeg', 'image/png'];

        if (!allowed.includes(file.type)) {
            showFieldError(image, 'Car image must be JPEG or PNG.');
            valid = false;
        }

        if (file.size > 2 * 1024 * 1024) {
            showFieldError(image, 'Car image must be 2MB or smaller.');
            valid = false;
        }
    }

    return valid;
}

async function deleteMember(button) {
    if (!window.confirm('Delete this member? Their related orders and blogs will also be removed by the database rules.')) {
        return;
    }

    button.disabled = true;
    const message = document.querySelector('[data-member-message]');

    const response = await fetch(resolveAdminUrl(`api/members/${button.dataset.memberId}`), {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
    const payload = await response.json();

    if (message) {
        message.textContent = payload.message;
    }

    if (payload.success) {
        document.querySelector(`[data-member-row="${button.dataset.memberId}"]`)?.remove();
        return;
    }

    button.disabled = false;
}

function resolveAdminUrl(path) {
    const baseUrl = document.querySelector('meta[name="app-base-url"]')?.content || `${window.location.origin}/`;
    return new URL(path, baseUrl).toString();
}
