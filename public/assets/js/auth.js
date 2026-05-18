document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.querySelector('.js-register-form');
    const loginForm = document.querySelector('.js-login-form');
    const profileForm = document.querySelector('.js-profile-form');

    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            if (!validateRegisterForm(registerForm)) {
                event.preventDefault();
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            if (!validateLoginForm(loginForm)) {
                event.preventDefault();
            }
        });
    }

    if (profileForm) {
        profileForm.addEventListener('submit', (event) => {
            if (!validateProfileForm(profileForm)) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-category-bar] a[data-type]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            const grid = document.querySelector('[data-car-grid]');

            if (!grid) {
                return;
            }

            event.preventDefault();
            const payload = await fetchCarsByType(link.dataset.type);

            if (!payload || !payload.success) {
                window.location.href = link.href;
                return;
            }

            grid.innerHTML = payload.cars.length
                ? payload.cars.map(renderCarCard).join('')
                : '<p class="empty-state">No cars found for this category.</p>';

            history.pushState({}, '', link.href);
        });
    });
});

function validateRegisterForm(form) {
    clearFormErrors(form);
    let valid = true;

    valid = requireValue(form, 'name', 'Name is required.') && valid;
    valid = requireValue(form, 'email', 'Email is required.') && valid;
    valid = requireValue(form, 'password', 'Password is required.') && valid;

    if (form.elements.email.value && !isEmail(form.elements.email.value)) {
        showFieldError(form.elements.email, 'Enter a valid email address.');
        valid = false;
    }

    if (form.elements.password.value.length < 8) {
        showFieldError(form.elements.password, 'Password must be at least 8 characters.');
        valid = false;
    }

    if (form.elements.password.value !== form.elements.confirm_password.value) {
        showFieldError(form.elements.confirm_password, 'Passwords do not match.');
        valid = false;
    }

    return valid;
}

function validateLoginForm(form) {
    clearFormErrors(form);
    let valid = true;

    valid = requireValue(form, 'email', 'Email is required.') && valid;
    valid = requireValue(form, 'password', 'Password is required.') && valid;

    if (form.elements.email.value && !isEmail(form.elements.email.value)) {
        showFieldError(form.elements.email, 'Enter a valid email address.');
        valid = false;
    }

    return valid;
}

function validateProfileForm(form) {
    clearFormErrors(form);
    let valid = true;

    valid = requireValue(form, 'name', 'Name is required.') && valid;
    valid = requireValue(form, 'email', 'Email is required.') && valid;

    if (form.elements.email.value && !isEmail(form.elements.email.value)) {
        showFieldError(form.elements.email, 'Enter a valid email address.');
        valid = false;
    }

    const newPassword = form.elements.new_password.value;

    if (newPassword !== '') {
        if (form.elements.current_password.value === '') {
            showFieldError(form.elements.current_password, 'Current password is required.');
            valid = false;
        }

        if (newPassword.length < 8) {
            showFieldError(form.elements.new_password, 'New password must be at least 8 characters.');
            valid = false;
        }

        if (newPassword !== form.elements.confirm_password.value) {
            showFieldError(form.elements.confirm_password, 'New passwords do not match.');
            valid = false;
        }
    }

    const picture = form.elements.profile_picture;

    if (picture && picture.files.length > 0) {
        const file = picture.files[0];
        const allowed = ['image/jpeg', 'image/png'];

        if (!allowed.includes(file.type)) {
            showFieldError(picture, 'Profile picture must be JPEG or PNG.');
            valid = false;
        }

        if (file.size > 2 * 1024 * 1024) {
            showFieldError(picture, 'Profile picture must be 2MB or smaller.');
            valid = false;
        }
    }

    return valid;
}

function clearFormErrors(form) {
    form.querySelectorAll('.field-error').forEach((error) => error.remove());
}

function renderCarCard(car) {
    const baseUrl = document.querySelector('meta[name="app-base-url"]')?.content || `${window.location.origin}/`;
    const image = car.image_path
        ? `<img src="${escapeHtml(new URL(car.image_path, baseUrl).toString())}" alt="${escapeHtml(car.name)}">`
        : '<div class="image-placeholder">No image</div>';

    return `
        <article class="car-card">
            <a href="${escapeHtml(car.url)}">${image}</a>
            <div class="car-card-body">
                <p class="eyebrow">${escapeHtml(car.type)}</p>
                <h3><a href="${escapeHtml(car.url)}">${escapeHtml(car.name)}</a></h3>
                <p>${escapeHtml(car.model)}</p>
                <strong>BDT ${Number(car.price_per_day).toFixed(2)} / day</strong>
            </div>
        </article>
    `;
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}
