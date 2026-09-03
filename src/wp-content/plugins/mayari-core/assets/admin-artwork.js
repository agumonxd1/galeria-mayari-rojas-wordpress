(() => {
  const discipline = document.querySelector('[name="gmr_discipline"]');
  const updateConditionalFields = () => {
    if (!discipline) return;
    const option = discipline.options[discipline.selectedIndex];
    const slug = option?.dataset.slug || '';

    document.querySelectorAll('[data-gmr-disciplines]').forEach((element) => {
      const allowed = element.dataset.gmrDisciplines.split(',');
      element.hidden = Boolean(slug) && !allowed.includes(slug);
    });
  };

  discipline?.addEventListener('change', updateConditionalFields);
  updateConditionalFields();

  document.querySelectorAll('[data-gmr-inline-term]').forEach((control) => {
    const toggle = control.querySelector('[data-gmr-term-toggle]');
    const form = control.querySelector('[data-gmr-term-form]');
    const input = control.querySelector('[data-gmr-term-name]');
    const save = control.querySelector('[data-gmr-term-save]');
    const status = control.querySelector('[data-gmr-term-status]');
    const select = control.closest('.gmr-field')?.querySelector('select');

    toggle?.addEventListener('click', () => {
      const willOpen = form.hidden;
      form.hidden = !willOpen;
      toggle.setAttribute('aria-expanded', String(willOpen));
      if (willOpen) input.focus();
    });

    const createTerm = async () => {
      const name = input.value.trim();
      if (!name || !select || !window.gmrArtworkAdmin) return;
      save.disabled = true;
      status.textContent = 'Guardando…';

      const data = new FormData();
      data.append('action', 'gmr_create_artwork_term');
      data.append('nonce', window.gmrArtworkAdmin.nonce);
      data.append('taxonomy', save.dataset.taxonomy);
      data.append('name', name);

      try {
        const response = await fetch(window.gmrArtworkAdmin.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.data?.message || window.gmrArtworkAdmin.error);
        const option = new Option(result.data.name, result.data.id, true, true);
        option.dataset.slug = result.data.slug;
        if (!select.multiple) Array.from(select.options).forEach((item) => { item.selected = false; });
        select.add(option);
        option.selected = true;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        input.value = '';
        status.textContent = 'Opción agregada';
        window.setTimeout(() => { form.hidden = true; toggle.setAttribute('aria-expanded', 'false'); status.textContent = ''; }, 900);
      } catch (error) {
        status.textContent = error.message || window.gmrArtworkAdmin.error;
      } finally {
        save.disabled = false;
      }
    };

    save?.addEventListener('click', createTerm);
    input?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') { event.preventDefault(); createTerm(); }
    });
  });
})();
