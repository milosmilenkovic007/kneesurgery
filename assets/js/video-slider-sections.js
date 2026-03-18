(function(){
  const INSTANCES = [];
  let WHEEL_BOUND = false;

  function buildVideo(type, src) {
    if (!src) return null;

    if (type === 'file') {
      const video = document.createElement('video');
      video.controls = true;
      video.autoplay = true;
      video.playsInline = true;
      video.src = src;
      return video;
    }

    const iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.loading = 'eager';
    iframe.allow = 'autoplay; fullscreen; picture-in-picture';
    iframe.allowFullscreen = true;
    iframe.title = 'Video';
    return iframe;
  }

  function clearModal(body) {
    if (!body) return;

    while (body.firstChild) {
      const child = body.firstChild;
      if (child.tagName === 'VIDEO') {
        child.pause();
        child.removeAttribute('src');
        child.load();
      }
      body.removeChild(child);
    }
  }

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
    const prevButton = root.querySelector('[data-vss-prev]');
    const nextButton = root.querySelector('[data-vss-next]');
    const rightCol = root.querySelector('[data-vss-right]');
    const wrap = root.querySelector('.hj-vss-wrap');
    const modal = root.querySelector('[data-vss-modal]');
    const modalBody = root.querySelector('[data-vss-modal-body]');
    const openButtons = Array.from(root.querySelectorAll('[data-vss-open]'));
    const closeButtons = modal ? Array.from(root.querySelectorAll('[data-vss-close]')) : [];

    if (track && slides.length > 1 && dots.length) {
      function getActiveIndex(){
        let closest = 0;
        let distance = Number.POSITIVE_INFINITY;

        slides.forEach((slide, index) => {
          const diff = Math.abs(track.scrollLeft - slide.offsetLeft);
          if (diff < distance) {
            distance = diff;
            closest = index;
          }
        });

        return closest;
      }

      function updateControls(index){
        setActiveDot(dots, index);
        if (prevButton) prevButton.disabled = index <= 0;
        if (nextButton) nextButton.disabled = index >= slides.length - 1;
      }

      function goTo(idx){
        const slide = slides[idx];
        if (!slide) return;
        track.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
        updateControls(idx);
      }

      dots.forEach((btn) => {
        btn.addEventListener('click', () => {
          const idx = parseInt(btn.getAttribute('data-vss-dot') || '0', 10);
          goTo(isNaN(idx) ? 0 : idx);
        });
      });

      if (prevButton) {
        prevButton.addEventListener('click', () => {
          goTo(Math.max(0, getActiveIndex() - 1));
        });
      }

      if (nextButton) {
        nextButton.addEventListener('click', () => {
          goTo(Math.min(slides.length - 1, getActiveIndex() + 1));
        });
      }

      let raf = null;
      track.addEventListener('scroll', () => {
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
          updateControls(getActiveIndex());
        });
      }, {passive: true});

      updateControls(0);
    }

    if (modal && modalBody && openButtons.length) {
      function closeModal() {
        clearModal(modalBody);
        modal.hidden = true;
        document.body.classList.remove('hj-vss-modal-open');
      }

      openButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const type = button.getAttribute('data-video-type') || 'embed';
          const src = button.getAttribute('data-video-src') || '';
          const media = buildVideo(type, src);
          if (!media) return;

          clearModal(modalBody);
          modalBody.appendChild(media);
          modal.hidden = false;
          document.body.classList.add('hj-vss-modal-open');
        });
      });

      closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
          closeModal();
        }
      });
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

    // Main accordion: allow only one open at a time
    const mainAccordions = Array.from(root.querySelectorAll('.hj-vss-main-accordion'));
    if (mainAccordions.length) {
      mainAccordions.forEach((acc) => {
        acc.addEventListener('toggle', () => {
          if (!acc.open) return;
          mainAccordions.forEach((other) => {
            if (other !== acc) {
              other.open = false;
            }
          });
        });
      });
    }

    // Inner accordions: only one open per list, default closed in markup
    const innerLists = Array.from(root.querySelectorAll('.hj-vss-accordion'));
    innerLists.forEach((list) => {
      const items = Array.from(list.querySelectorAll('details'));
      items.forEach((acc) => {
        acc.addEventListener('toggle', () => {
          if (!acc.open) return;
          items.forEach((other) => {
            if (other !== acc) {
              other.open = false;
            }
          });
        });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hj-vss').forEach(init);
  });
})();
