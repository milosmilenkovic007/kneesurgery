(function () {
  function setActiveDot(dots, index) {
    dots.forEach(function (dot, idx) {
      var isActive = idx === index;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  function init(root) {
    var slides = Array.from(root.querySelectorAll('[data-cfb-slide]'));
    var dots = Array.from(root.querySelectorAll('[data-cfb-dot]'));
    var prevButton = root.querySelector('[data-cfb-prev]');
    var nextButton = root.querySelector('[data-cfb-next]');

    if (!slides.length) {
      return;
    }

    var activeIndex = 0;
    var timer = null;

    function showSlide(index) {
      activeIndex = (index + slides.length) % slides.length;
      slides.forEach(function (slide, idx) {
        slide.classList.toggle('is-active', idx === activeIndex);
      });
      if (dots.length) {
        setActiveDot(dots, activeIndex);
      }
    }

    function startAutoplay() {
      if (slides.length <= 1) return;
      timer = window.setInterval(function () {
        showSlide((activeIndex + 1) % slides.length);
      }, 5000);
    }

    function stopAutoplay() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot, index) {
      dot.addEventListener('click', function () {
        stopAutoplay();
        showSlide(index);
        startAutoplay();
      });
    });

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        stopAutoplay();
        showSlide(activeIndex - 1);
        startAutoplay();
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        stopAutoplay();
        showSlide(activeIndex + 1);
        startAutoplay();
      });
    }

    root.addEventListener('mouseenter', stopAutoplay);
    root.addEventListener('mouseleave', startAutoplay);

    showSlide(0);
    startAutoplay();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-cta-form-block.is-rating').forEach(init);
  });
})();