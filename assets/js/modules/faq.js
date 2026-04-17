(() => {
  function initFaqAccordion(list) {
    const items = Array.from(list.querySelectorAll('[data-hj-faq-item]'));

    if (items.length < 2) {
      return;
    }

    items.forEach((item) => {
      item.addEventListener('toggle', () => {
        if (!item.open) {
          return;
        }

        items.forEach((otherItem) => {
          if (otherItem !== item) {
            otherItem.open = false;
          }
        });
      });
    });
  }

  function init() {
    document.querySelectorAll('[data-hj-faq-accordion]').forEach(initFaqAccordion);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();