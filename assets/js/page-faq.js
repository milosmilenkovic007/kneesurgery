(() => {
  function normalizeText(value) {
    return (value || '').toString().toLowerCase().trim();
  }

  function initFaqPage(root) {
    const searchInput = root.querySelector('[data-hj-faq-search]');
    const sections = Array.from(root.querySelectorAll('[data-hj-faq-section]'));
    const resultNode = root.querySelector('[data-hj-faq-results]');
    const emptyNode = root.querySelector('[data-hj-faq-empty]');

    if (!searchInput || !sections.length || !resultNode) {
      return;
    }

    const allLabel = resultNode.textContent.trim();

    function updateResults() {
      const query = normalizeText(searchInput.value);
      let visibleItems = 0;

      sections.forEach((section) => {
        const items = Array.from(section.querySelectorAll('[data-hj-faq-item]'));
        let sectionVisible = 0;

        items.forEach((item) => {
          const haystack = item.dataset.faqSearch || '';
          const matches = query === '' || haystack.includes(query);

          item.hidden = !matches;

          if (matches) {
            sectionVisible += 1;
            visibleItems += 1;
          } else {
            item.open = false;
          }
        });

        section.hidden = sectionVisible === 0;
      });

      if (query === '') {
        resultNode.textContent = allLabel;
      } else {
        resultNode.textContent = String(visibleItems);
      }

      if (emptyNode) {
        emptyNode.hidden = visibleItems !== 0;
      }
    }

    searchInput.addEventListener('input', updateResults);
    updateResults();
  }

  function init() {
    document.querySelectorAll('[data-hj-faq-page]').forEach(initFaqPage);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();