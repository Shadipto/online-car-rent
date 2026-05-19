document.addEventListener('DOMContentLoaded', () => {
    const blogForm = document.querySelector('.js-blog-form');

    if (blogForm) {
        blogForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitBlogPost(blogForm);
        });
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-delete-blog');

        if (button) {
            deleteBlogPost(button);
        }
    });
});

async function submitBlogPost(form) {
    clearFormErrors(form);
    const message = document.querySelector('[data-blog-message]');
    const title = form.elements.title;
    const content = form.elements.content;
    let valid = true;

    if (title.value.trim() === '') {
        showFieldError(title, 'Title is required.');
        valid = false;
    }

    if (content.value.trim() === '') {
        showFieldError(content, 'Content is required.');
        valid = false;
    }

    if (!valid) {
        return;
    }

    const response = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: new FormData(form),
    });
    const payload = await response.json();

    if (message) {
        message.textContent = payload.message;
    }

    if (!payload.success) {
        return;
    }

    document.querySelector('[data-empty-blog]')?.remove();
    document.querySelector('[data-blog-list]')?.insertAdjacentHTML('afterbegin', payload.html);
    form.reset();
}

async function deleteBlogPost(button) {
    if (!window.confirm('Delete this blog post?')) {
        return;
    }

    button.disabled = true;
    const data = new FormData();
    data.append('_csrf', document.querySelector('meta[name="csrf-token"]').content);

    const response = await fetch(resolveBlogUrl(`api/blogs/${button.dataset.blogId}`), {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: data,
    });
    const payload = await response.json();

    if (payload.success) {
        document.querySelector(`[data-blog-post="${button.dataset.blogId}"]`)?.remove();

        if (!document.querySelector('[data-blog-post]')) {
            document.querySelector('[data-blog-list]').innerHTML = '<p class="empty-state" data-empty-blog>No blog posts yet.</p>';
        }

        return;
    }

    alert(payload.message);
    button.disabled = false;
}

function resolveBlogUrl(path) {
    const baseUrl = document.querySelector('meta[name="app-base-url"]')?.content || `${window.location.origin}/`;
    return new URL(path, baseUrl).toString();
}
