document.addEventListener('DOMContentLoaded', () => {
    const tabs = [...document.querySelectorAll('.tabs [role="tab"]')];
    const title = document.getElementById('auth-title');
    const note  = document.querySelector('.note');

    function activate(tab) {
        tabs.forEach(t => {
            const isActive = (t === tab);
            const panel = document.getElementById(t.dataset.panel);

            t.setAttribute('aria-selected', isActive);
            t.classList.toggle('is-active', isActive);

            panel.hidden = !isActive;
        });

        if (title && tab.dataset.title) title.textContent = tab.dataset.title;
        if (note  && tab.dataset.note)  note.textContent  = tab.dataset.note;

        const firstField = document
            .getElementById(tab.dataset.panel)
            .querySelector('input,select,textarea,button');
        if (firstField) firstField.focus();
    }

    tabs.forEach(t => t.addEventListener('click', () => activate(t)));

    activate(tabs.find(t => t.classList.contains('is-active')) || tabs[0]);

    document.querySelectorAll('[data-toggle="password"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.target;
            const input = document.getElementById(id);
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            btn.classList.toggle('is-on', show);
            input.focus();

            try {
                const len = input.value.length;
                input.setSelectionRange(len, len);
            } catch (_) {}
        });
    });
});
