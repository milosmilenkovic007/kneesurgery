(function(){
  const INSTANCES = [];
  let WHEEL_BOUND = false;

  function bindGlobalWheel(){
    if (WHEEL_BOUND) return;
    WHEEL_BOUND = true;

    window.addEventListener('wheel', (e) => {
      if (!e.deltaY) return;

      // Pick the active (pinned) instance that's closest to the sticky top.
      let active = null;
      let bestScore = Infinity;

      for (const inst of INSTANCES) {
        if (!inst || !inst.isActive || !inst.isActive()) continue;
        const top = inst.wrap.getBoundingClientRect().top;
        const score = Math.abs(top - 20);
        if (score < bestScore) {
          bestScore = score;
          active = inst;
        }
      }

      if (!active) return;

      const rightCol = active.rightCol;
      const maxScroll = rightCol.scrollHeight - rightCol.clientHeight;
      if (maxScroll <= 0) return;

      const atTop = rightCol.scrollTop <= 0;
      const atBottom = rightCol.scrollTop >= maxScroll - 1;
      const scrollingDown = e.deltaY > 0;
      const canScroll = (scrollingDown && !atBottom) || (!scrollingDown && !atTop);

      if (canScroll) {
        e.preventDefault();
        rightCol.scrollTop = Math.max(0, Math.min(maxScroll, rightCol.scrollTop + e.deltaY));
      }
    }, {passive: false});
  }

  function setActiveDot(dots, index){
    dots.forEach((btn, i) => {
      const active = i === index;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function init(root){
    const track = root.querySelector('[data-vss-track]');
    const slides = Array.from(root.querySelectorAll('[data-vss-slide]'));
    const dotsWrap = root.querySelector('[data-vss-dots]');
    const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('[data-vss-dot]')) : [];
    const rightCol = root.querySelector('[data-vss-right]');
    const wrap = root.querySelector('.hj-vss-wrap');

    if (track && slides.length > 1 && dots.length) {
      function goTo(idx){
        const slide = slides[idx];
        if (!slide) return;
        slide.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});
        setActiveDot(dots, idx);
      }

      dots.forEach((btn) => {
        btn.addEventListener('click', () => {
          const idx = parseInt(btn.getAttribute('data-vss-dot') || '0', 10);
          goTo(isNaN(idx) ? 0 : idx);
        });
      });

      let raf = null;
      track.addEventListener('scroll', () => {
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
          const slideW = slides[0].getBoundingClientRect().width;
          const idx = slideW ? Math.round(track.scrollLeft / slideW) : 0;
          setActiveDot(dots, Math.max(0, Math.min(slides.length - 1, idx)));
        });
      }, {passive: true});
    }

    if (rightCol && wrap) {
      const mq = window.matchMedia('(min-width: 981px)');
      const syncSticky = () => root.classList.toggle('is-sticky-ready', mq.matches);
      syncSticky();

      if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', syncSticky);
      } else if (typeof mq.addListener === 'function') {
        mq.addListener(syncSticky);
      }

      const isActive = () => {
        if (!mq.matches) return false;
        if (!root.classList.contains('is-sticky-ready')) return false;

        // Must be visible in viewport.
        const rect = root.getBoundingClientRect();
        if (rect.bottom <= 0 || rect.top >= window.innerHeight) return false;

        // Must be pinned (sticky wrapper near the top).
        const wrapTop = wrap.getBoundingClientRect().top;
        return wrapTop >= 0 && wrapTop <= 24;
      };

      INSTANCES.push({ root, wrap, rightCol, isActive });
      bindGlobalWheel();
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hj-vss').forEach(init);
  });
})();
