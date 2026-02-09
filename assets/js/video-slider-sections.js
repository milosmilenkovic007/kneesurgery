(function(){
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

    if (rightCol) {
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            root.classList.toggle('is-sticky-ready', entry.isIntersecting && entry.intersectionRatio === 1);
          });
        }, {threshold: [1]});

        observer.observe(rightCol);
      } else {
        root.classList.add('is-sticky-ready');
      }

      root.addEventListener('wheel', (e) => {
        if (!root.classList.contains('is-sticky-ready')) return;
        if (!e.deltaY) return;

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
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hj-vss').forEach(init);
  });
})();
