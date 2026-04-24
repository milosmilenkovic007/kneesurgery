(function () {
  function setActiveDot(dots, index) {
    dots.forEach(function (dot, dotIndex) {
      var isActive = dotIndex === index;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  function init(root) {
    var track = root.querySelector('[data-rt-track]');
    var slides = Array.from(root.querySelectorAll('[data-rt-slide]'));
    var dots = Array.from(root.querySelectorAll('[data-rt-dot]'));
    var prevButton = root.querySelector('[data-rt-prev]');
    var nextButton = root.querySelector('[data-rt-next]');

    slides.forEach(function (slide) {
      var copy = slide.querySelector('[data-rt-copy]');
      var toggle = slide.querySelector('[data-rt-read-more]');

      if (!copy || !toggle) {
        return;
      }

      requestAnimationFrame(function () {
        var isOverflowing = copy.scrollHeight > copy.clientHeight + 2;
        toggle.hidden = !isOverflowing;
      });

      toggle.addEventListener('click', function () {
        var expanded = copy.classList.toggle('is-clamped') === false;
        toggle.textContent = expanded ? 'Read less' : 'Read more';
      });
    });

    if (!track || slides.length <= 1) {
      if (prevButton) prevButton.style.display = 'none';
      if (nextButton) nextButton.style.display = 'none';
      return;
    }

    function getActiveIndex() {
      var closest = 0;
      var distance = Number.POSITIVE_INFINITY;

      slides.forEach(function (slide, index) {
        var diff = Math.abs(track.scrollLeft - slide.offsetLeft);
        if (diff < distance) {
          distance = diff;
          closest = index;
        }
      });

      return closest;
    }

    function updateControls(index) {
      setActiveDot(dots, index);
      if (prevButton) prevButton.disabled = index <= 0;
      if (nextButton) nextButton.disabled = index >= slides.length - 1;
    }

    function goTo(index) {
      var slide = slides[index];
      if (!slide) {
        return;
      }

      track.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
      updateControls(index);
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var index = parseInt(dot.getAttribute('data-rt-dot') || '0', 10);
        goTo(isNaN(index) ? 0 : index);
      });
    });

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        goTo(Math.max(0, getActiveIndex() - 1));
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        goTo(Math.min(slides.length - 1, getActiveIndex() + 1));
      });
    }

    var raf = null;
    track.addEventListener('scroll', function () {
      if (raf) {
        cancelAnimationFrame(raf);
      }

      raf = requestAnimationFrame(function () {
        updateControls(getActiveIndex());
      });
    }, { passive: true });

    updateControls(0);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-reviews-trustindex.is-source-google').forEach(init);
  });
})();