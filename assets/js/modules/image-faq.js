(function () {
  function pulseImage(root) {
    var media = root.closest('.hj-image-faq');

    if (!media) {
      return;
    }

    var imageWrap = media.querySelector('.hj-ifaq-media');

    if (!imageWrap) {
      return;
    }

    imageWrap.classList.remove('is-animating');
    window.requestAnimationFrame(function () {
      imageWrap.classList.add('is-animating');
      window.setTimeout(function () {
        imageWrap.classList.remove('is-animating');
      }, 520);
    });
  }

  function keepItemInView(item) {
    var summary = item.querySelector('summary');

    if (!summary) {
      return;
    }

    window.requestAnimationFrame(function () {
      try {
        summary.focus({ preventScroll: true });
      } catch (error) {
        summary.focus();
      }

      item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  function initAccordion(root) {
    var items = Array.from(root.querySelectorAll('.hj-ifaq-item'));

    if (!items.length) {
      return;
    }

    items.forEach(function (item) {
      item.addEventListener('toggle', function () {
        if (!item.open) {
          return;
        }

        items.forEach(function (otherItem) {
          if (otherItem !== item) {
            otherItem.open = false;
          }
        });

        keepItemInView(item);
        pulseImage(root);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-image-faq-accordion]').forEach(initAccordion);
  });
})();