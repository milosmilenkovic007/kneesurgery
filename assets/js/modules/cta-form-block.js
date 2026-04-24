(function () {
  function setActiveDot(dots, index) {
    dots.forEach(function (dot, idx) {
      var isActive = idx === index;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  function updateReadMore(slide) {
    if (!slide) {
      return;
    }

    var copy = slide.querySelector('[data-cfb-copy]');
    var toggle = slide.querySelector('[data-cfb-read-more]');

    if (!copy || !toggle) {
      return;
    }

    if (!copy.classList.contains('is-clamped')) {
      toggle.hidden = false;
      toggle.textContent = 'Read less';
      return;
    }

    var isOverflowing = copy.scrollHeight > copy.clientHeight + 2;
    toggle.hidden = !isOverflowing;
    toggle.textContent = 'Read more';
  }

  function init(root) {
    var slides = Array.from(root.querySelectorAll('[data-cfb-slide]'));
    var dots = Array.from(root.querySelectorAll('[data-cfb-dot]'));
    var prevButton = root.querySelector('[data-cfb-prev]');
    var nextButton = root.querySelector('[data-cfb-next]');

    if (!slides.length) {
      return;
    }

    slides.forEach(function (slide) {
      var copy = slide.querySelector('[data-cfb-copy]');
      var toggle = slide.querySelector('[data-cfb-read-more]');

      if (!copy || !toggle) {
        return;
      }

      toggle.addEventListener('click', function () {
        var expanded = copy.classList.toggle('is-clamped') === false;
        toggle.textContent = expanded ? 'Read less' : 'Read more';

        if (!expanded) {
          requestAnimationFrame(function () {
            updateReadMore(slide);
          });
        }
      });
    });

    var activeIndex = 0;

    function showSlide(index) {
      activeIndex = (index + slides.length) % slides.length;
      slides.forEach(function (slide, idx) {
        slide.classList.toggle('is-active', idx === activeIndex);
      });
      if (dots.length) {
        setActiveDot(dots, activeIndex);
      }

      requestAnimationFrame(function () {
        updateReadMore(slides[activeIndex]);
      });
    }

    dots.forEach(function (dot, index) {
      dot.addEventListener('click', function () {
        showSlide(index);
      });
    });

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        showSlide(activeIndex - 1);
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        showSlide(activeIndex + 1);
      });
    }

    showSlide(0);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-cta-form-block.is-rating').forEach(init);
  });
})();