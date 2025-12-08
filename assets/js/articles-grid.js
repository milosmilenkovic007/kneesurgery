(function(){
  if (typeof window.hjAG === 'undefined') { return; }

  function parseConfig(section){
    const raw = section.dataset.config || '{}';
    try { return JSON.parse(raw); } catch (e) { return {}; }
  }

  function setActive(buttons, current){
    buttons.forEach(btn => {
      const isActive = btn === current;
      btn.classList.toggle('is-active', isActive);
      btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  async function fetchPosts(config, term){
    const body = new URLSearchParams({
      action: 'hj_ag_filter',
      nonce: window.hjAG.nonce,
      term: term || '0',
      config: JSON.stringify(config || {})
    });

    const res = await fetch(window.hjAG.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    });
    return res.json();
  }

  function bindFilters(section){
    const buttons = Array.from(section.querySelectorAll('.hj-ag-filter'));
    if (!buttons.length) { return; }
    const grid = section.querySelector('.hj-ag-grid');
    if (!grid) { return; }
    const config = parseConfig(section);

    buttons.forEach(btn => {
      btn.addEventListener('click', async () => {
        if (grid.classList.contains('is-loading')) { return; }
        setActive(buttons, btn);
        grid.classList.add('is-loading');
        try {
          const term = btn.dataset.term || '0';
          const json = await fetchPosts(config, term);
          if (json && json.success && json.data && typeof json.data.html !== 'undefined') {
            grid.innerHTML = json.data.html;
          }
        } catch (e) {
          console.error('Articles Grid filter error', e);
        } finally {
          grid.classList.remove('is-loading');
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hj-articles-grid').forEach(bindFilters);
  });
})();
