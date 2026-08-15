(() => {
  const discipline = document.querySelector('[name="gmr_discipline"]');
  if (!discipline) return;

  const updateConditionalFields = () => {
    const option = discipline.options[discipline.selectedIndex];
    const slug = option?.dataset.slug || '';

    document.querySelectorAll('[data-gmr-disciplines]').forEach((element) => {
      const allowed = element.dataset.gmrDisciplines.split(',');
      element.hidden = Boolean(slug) && !allowed.includes(slug);
    });
  };

  discipline.addEventListener('change', updateConditionalFields);
  updateConditionalFields();
})();

