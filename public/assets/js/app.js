/* Rental Evidence: progressive enhancements.
   Every feature here is optional: forms still work with JavaScript turned off. */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    setupConfirmForms();
    setupAjaxDelete();
    setupDropzone();
    setupLightbox();
    setupCompareSliders();
    setupSubmitOnce();

    document.querySelector('[data-print]')?.addEventListener('click', () => window.print());
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* Ask before destructive actions. */
function setupConfirmForms() {
    document.querySelectorAll('form[data-confirm]:not([data-ajax-delete])').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
}

/* Delete a photo without reloading the page. */
function setupAjaxDelete() {
    document.querySelectorAll('form[data-ajax-delete]').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (!confirm(form.dataset.confirm)) return;

            const card = form.closest('[data-photo-card]');
            const button = form.querySelector('button');
            button.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrfToken() },
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error('Delete failed');

                card.classList.add('is-removing');
                setTimeout(() => card.remove(), 250);
            } catch {
                button.disabled = false;
                alert('The photo could not be deleted. Refresh the page and try again.');
            }
        });
    });
}

/* Drag-and-drop area with thumbnails and size checks before upload. */
function setupDropzone() {
    const zone = document.querySelector('[data-dropzone]');
    if (!zone) return;

    const input = zone.querySelector('input[type=file]');
    const previews = document.querySelector('[data-previews]');
    const maxBytes = Number(input.dataset.maxMb) * 1024 * 1024;
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];

    ['dragenter', 'dragover'].forEach(evt =>
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach(evt =>
        zone.addEventListener(evt, () => zone.classList.remove('is-over')));

    zone.addEventListener('drop', e => {
        e.preventDefault();
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
    });

    input.addEventListener('change', () => {
        previews.querySelectorAll('img').forEach(img => URL.revokeObjectURL(img.src));
        previews.innerHTML = '';
        let problems = [];

        [...input.files].forEach(file => {
            const item = document.createElement('div');
            item.className = 'preview';

            let problem = '';
            if (!allowed.includes(file.type)) problem = 'Not a JPG, PNG or WebP';
            else if (file.size > maxBytes) problem = `Over ${input.dataset.maxMb} MB`;

            if (problem) {
                item.classList.add('is-invalid');
                problems.push(`${file.name}: ${problem}`);
            } else {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = '';
                item.append(img);
            }
            const name = document.createElement('span');
            name.textContent = problem ? `${file.name} (${problem})` : file.name;
            item.append(name);
            previews.append(item);
        });

        input.setCustomValidity(problems.length ? `Remove these files first: ${problems.join('; ')}` : '');
    });
}

/* Click a photo to see it full screen. Uses the native <dialog> element. */
function setupLightbox() {
    const links = document.querySelectorAll('[data-lightbox]');
    if (!links.length) return;

    const dialog = document.createElement('dialog');
    dialog.className = 'lightbox';
    dialog.innerHTML = `
        <button type="button" class="lightbox-close" aria-label="Close">&times;</button>
        <img alt="">
        <p></p>`;
    document.body.append(dialog);

    const img = dialog.querySelector('img');
    const caption = dialog.querySelector('p');
    dialog.querySelector('.lightbox-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });

    links.forEach(link => link.addEventListener('click', e => {
        e.preventDefault();
        img.src = link.href;
        img.alt = link.querySelector('img')?.alt ?? '';
        caption.textContent = link.dataset.caption || '';
        dialog.showModal();
    }));
}

/* Before/after slider plus thumbnails to switch which photos are compared. */
function setupCompareSliders() {
    document.querySelectorAll('[data-compare]').forEach(section => {
        const slider = section.querySelector('[data-slider]');
        const range = slider.querySelector('input[type=range]');

        const update = () => slider.style.setProperty('--pos', `${range.value}%`);
        range.addEventListener('input', update);
        update();

        section.querySelectorAll('[data-swap]').forEach(thumb => {
            thumb.addEventListener('click', () => {
                const stage = thumb.dataset.swap;
                slider.querySelector(`[data-slot="${stage}"]`).src = thumb.dataset.src;
                section.querySelector(`[data-caption="${stage}"]`).textContent = thumb.dataset.text;
                section.querySelectorAll(`[data-swap="${stage}"]`)
                       .forEach(t => t.classList.toggle('is-active', t === thumb));
            });
        });
    });
}

/* Stop double-submits and show progress on slow uploads. */
function setupSubmitOnce() {
    document.querySelectorAll('form[data-validate], form[data-upload]').forEach(form => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type=submit]');
            if (!button) return;
            setTimeout(() => {
                button.disabled = true;
                if (form.matches('[data-upload]')) button.textContent = 'Uploading…';
            }, 0);
        });
    });
}
