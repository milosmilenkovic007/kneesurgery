(function () {
  function getScrollTopWithOffset(target) {
    const header = document.querySelector('.elementor-location-header');
    const headerHeight = header ? header.getBoundingClientRect().height : 0;
    const targetTop = target.getBoundingClientRect().top + window.pageYOffset;

    return Math.max(targetTop - headerHeight - 12, 0);
  }

  function getNextBlock(root) {
    let next = root ? root.nextElementSibling : null;

    while (next) {
      if (next.offsetParent !== null || next.getClientRects().length > 0) {
        return next;
      }

      next = next.nextElementSibling;
    }

    return null;
  }

  function initNextBlockScroll(root) {
    const button = root.querySelector('[data-hh-scroll-next]');
    if (!button) {
      return;
    }

    button.addEventListener('click', function (event) {
      const nextBlock = getNextBlock(root);
      if (!nextBlock) {
        return;
      }

      event.preventDefault();

      window.scrollTo({
        top: getScrollTopWithOffset(nextBlock),
        behavior: 'smooth'
      });
    });
  }

  function animateCounter(node) {
    if (!node || node.dataset.hhCounted === '1') {
      return;
    }

    const target = parseFloat(node.getAttribute('data-hh-count-to') || '0');
    if (!Number.isFinite(target)) {
      return;
    }

    node.dataset.hhCounted = '1';

    const duration = 1400;
    const start = performance.now();
    const decimals = String(target).includes('.') ? String(target).split('.')[1].length : 0;

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = target * eased;
      node.textContent = decimals > 0 ? value.toFixed(decimals) : Math.round(value).toString();

      if (progress < 1) {
        requestAnimationFrame(tick);
      } else {
        node.textContent = decimals > 0 ? target.toFixed(decimals) : Math.round(target).toString();
      }
    }

    requestAnimationFrame(tick);
  }

  function initCounters(root) {
    const counters = Array.from(root.querySelectorAll('[data-hh-count-to]'));
    if (!counters.length) {
      return;
    }

    if (!('IntersectionObserver' in window)) {
      counters.forEach(animateCounter);
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        animateCounter(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.35 });

    counters.forEach((counter) => observer.observe(counter));
  }

  function initSlider(root) {
    const slider = root.querySelector('[data-hh-slider]');
    if (!slider) {
      return;
    }

    const slides = Array.from(slider.querySelectorAll('[data-hh-slide]'));
    if (slides.length < 2) {
      return;
    }

    let index = 0;
    window.setInterval(() => {
      slides[index].classList.remove('is-active');
      index = (index + 1) % slides.length;
      slides[index].classList.add('is-active');
    }, 3200);
  }

  function initHomeHero(root) {
    initCounters(root);
    initSlider(root);
    initNextBlockScroll(root);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-home-hero').forEach(initHomeHero);
  });
})();