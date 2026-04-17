document.addEventListener('DOMContentLoaded', function () {
  var blocks = document.querySelectorAll('.hj-treatments-block');

  function setActiveDot(dots, index) {
    dots.forEach(function (dot, dotIndex) {
      var isActive = dotIndex === index;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  function initSlider(slider) {
    if (!slider || slider.dataset.tbSliderReady === 'true') {
      return;
    }

    var track = slider.querySelector('[data-tb-track]');
    var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-tb-slide]'));
    var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-tb-dot]'));
    var prevButton = slider.querySelector('[data-tb-prev]');
    var nextButton = slider.querySelector('[data-tb-next]');
    var controls = slider.querySelector('[data-tb-controls]');
    var hint = slider.querySelector('[data-tb-hint]');
    var positions = [];

    if (!track || !slides.length) {
      return;
    }

    slider.dataset.tbSliderReady = 'true';

    function getActiveIndex() {
      var candidates = positions.length ? positions : slides.map(function (slide) {
        return slide.offsetLeft;
      });
      var closest = 0;
      var distance = Number.POSITIVE_INFINITY;

      candidates.forEach(function (position, index) {
        var diff = Math.abs(track.scrollLeft - position);
        if (diff < distance) {
          distance = diff;
          closest = index;
        }
      });

      return closest;
    }

    function updateControls(index) {
      setActiveDot(dots, index);

      if (prevButton) {
        prevButton.disabled = index <= 0;
      }

      if (nextButton) {
        nextButton.disabled = index >= positions.length - 1;
      }
    }

    function buildPositions() {
      var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      var seen = [];

      positions = slides.map(function (slide) {
        return Math.max(0, Math.min(slide.offsetLeft, maxScroll));
      }).filter(function (position) {
        var isDuplicate = seen.some(function (value) {
          return Math.abs(value - position) < 2;
        });

        if (!isDuplicate) {
          seen.push(position);
        }

        return !isDuplicate;
      });

      dots.forEach(function (dot, dotIndex) {
        dot.hidden = dotIndex >= positions.length;
      });

      return maxScroll > 2 && positions.length > 1;
    }

    function sync() {
      var hasOverflow = buildPositions();

      if (!hasOverflow) {
        if (controls) {
          controls.hidden = true;
        }

        if (prevButton) {
          prevButton.disabled = true;
        }

        if (nextButton) {
          nextButton.disabled = true;
        }

        return;
      }

      if (controls) {
        controls.hidden = false;
      }

      updateControls(getActiveIndex());
    }

    function dismissHint() {
      if (!hint) {
        return;
      }

      slider.classList.add('is-interacted');
    }

    function goTo(index) {
      var position = positions[index];

      if (typeof position !== 'number') {
        return;
      }

      track.scrollTo({
        left: position,
        behavior: 'smooth'
      });

      updateControls(index);
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var index = parseInt(dot.getAttribute('data-tb-dot') || '0', 10);
        goTo(isNaN(index) ? 0 : index);
      });
    });

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        dismissHint();
        goTo(Math.max(0, getActiveIndex() - 1));
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        dismissHint();
        goTo(Math.min(slides.length - 1, getActiveIndex() + 1));
      });
    }

    var frameId = null;
    track.addEventListener('scroll', function () {
      if (Math.abs(track.scrollLeft) > 12) {
        dismissHint();
      }

      if (frameId) {
        cancelAnimationFrame(frameId);
      }

      frameId = requestAnimationFrame(function () {
        updateControls(getActiveIndex());
      });
    }, { passive: true });

    track.addEventListener('pointerdown', dismissHint, { passive: true });

    window.addEventListener('resize', sync);
    slider._tbSync = sync;
    sync();
  }

  blocks.forEach(function (block) {
    var toggles = block.querySelectorAll('.hj-tb-section__toggle');
    var sliders = block.querySelectorAll('[data-tb-slider]');

    sliders.forEach(initSlider);

    toggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var section = toggle.closest('.hj-tb-section');
        var panelId = toggle.getAttribute('aria-controls');
        var panel = panelId ? document.getElementById(panelId) : null;
        if (!section || !panel) {
          return;
        }

        var isCollapsed = section.classList.contains('is-collapsed');
        section.classList.toggle('is-collapsed', !isCollapsed);
        toggle.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
        panel.hidden = !isCollapsed;

        if (isCollapsed) {
          requestAnimationFrame(function () {
            var panelSliders = panel.querySelectorAll('[data-tb-slider]');
            panelSliders.forEach(function (slider) {
              if (typeof slider._tbSync === 'function') {
                slider._tbSync();
              }
            });
          });
        }
      });
    });
  });
});
