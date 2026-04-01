(function () {
  function setButtonState(button, disabled, hidden) {
    if (!button) {
      return;
    }

    button.disabled = disabled;
    button.hidden = !!hidden;
  }

  function setActiveDot(dots, index) {
    dots.forEach(function (dot, dotIndex) {
      var isActive = dotIndex === index;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  function init(root) {
    var track = root.querySelector('[data-rd-track]');
    var slides = Array.from(root.querySelectorAll('[data-rd-slide]'));
    var dotsContainer = root.querySelector('[data-rd-dots]');
    var prevButton = root.querySelector('[data-rd-prev]');
    var nextButton = root.querySelector('[data-rd-next]');

    if (!track || slides.length === 0) {
      return;
    }

    var dots = [];
    var pages = [];
    var activePage = 0;
  var slidesPerView = 1;
  var dragState = null;
  var suppressClick = false;

    function getSlidesPerView() {
      var firstSlide = slides[0];

      if (!firstSlide) {
        return 1;
      }

      var slideWidth = firstSlide.getBoundingClientRect().width;
      if (!slideWidth) {
        return 1;
      }

      return Math.max(1, Math.round(track.getBoundingClientRect().width / slideWidth));
    }

    function getTargetScrollLeft(slide) {
      if (!slide) {
        return 0;
      }

      if (slidesPerView === 1) {
        return Math.max(0, slide.offsetLeft - ((track.clientWidth - slide.clientWidth) / 2));
      }

      return slide.offsetLeft;
    }

    function buildPages() {
      slidesPerView = getSlidesPerView();
      var nextPages = [];

      for (var index = 0; index < slides.length; index += slidesPerView) {
        nextPages.push(index);
      }

      if (nextPages.length === 0) {
        nextPages.push(0);
      }

      pages = nextPages;
    }

    function renderDots() {
      if (!dotsContainer) {
        return;
      }

      dotsContainer.innerHTML = '';
      dots = [];

      if (pages.length <= 1) {
        dotsContainer.hidden = true;
        return;
      }

      dotsContainer.hidden = false;

      pages.forEach(function (_, pageIndex) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'hj-rd-dot';
        dot.setAttribute('data-rd-dot', String(pageIndex));
        dot.setAttribute('aria-label', 'Go to doctor group ' + (pageIndex + 1));
        dot.setAttribute('aria-pressed', 'false');
        dot.addEventListener('click', function () {
          goToPage(pageIndex);
        });
        dotsContainer.appendChild(dot);
        dots.push(dot);
      });
    }

    function getPageIndexFromScroll() {
      var closestPage = 0;
      var closestDistance = Number.POSITIVE_INFINITY;

      pages.forEach(function (slideIndex, pageIndex) {
        var slide = slides[slideIndex];
        if (!slide) {
          return;
        }

        var distance = Math.abs(track.scrollLeft - getTargetScrollLeft(slide));
        if (distance < closestDistance) {
          closestDistance = distance;
          closestPage = pageIndex;
        }
      });

      return closestPage;
    }

    function updateControls() {
      activePage = getPageIndexFromScroll();
      setButtonState(prevButton, activePage <= 0, pages.length <= 1);
      setButtonState(nextButton, activePage >= pages.length - 1, pages.length <= 1);
      setActiveDot(dots, activePage);
    }

    function goToPage(pageIndex) {
      var safePageIndex = Math.max(0, Math.min(pageIndex, pages.length - 1));
      var slide = slides[pages[safePageIndex]];

      if (!slide) {
        return;
      }

      track.scrollTo({
        left: getTargetScrollLeft(slide),
        behavior: 'smooth'
      });
      activePage = safePageIndex;
      setActiveDot(dots, activePage);
      setButtonState(prevButton, activePage <= 0, pages.length <= 1);
      setButtonState(nextButton, activePage >= pages.length - 1, pages.length <= 1);
    }

    function refresh() {
      buildPages();
      renderDots();
      updateControls();
    }

    function startDrag(clientX, clientY) {
      dragState = {
        startX: clientX,
        startY: clientY,
        startScrollLeft: track.scrollLeft,
        isHorizontal: false,
        hasMoved: false
      };

      track.classList.add('is-dragging');
    }

    function moveDrag(clientX, clientY) {
      if (!dragState) {
        return false;
      }

      var deltaX = clientX - dragState.startX;
      var deltaY = clientY - dragState.startY;

      if (!dragState.isHorizontal) {
        if (Math.abs(deltaX) < 6 && Math.abs(deltaY) < 6) {
          return false;
        }

        if (Math.abs(deltaX) <= Math.abs(deltaY)) {
          return false;
        }

        dragState.isHorizontal = true;
      }

      dragState.hasMoved = true;
      suppressClick = true;
      track.scrollLeft = dragState.startScrollLeft - deltaX;
      return true;
    }

    function endDrag() {
      if (!dragState) {
        return;
      }

      var shouldSnap = dragState.hasMoved;
      dragState = null;
      track.classList.remove('is-dragging');

      if (shouldSnap) {
        updateControls();
        window.setTimeout(function () {
          suppressClick = false;
        }, 80);
      } else {
        suppressClick = false;
      }
    }

    function onMouseDown(event) {
      if (event.button !== 0) {
        return;
      }

      startDrag(event.clientX, event.clientY);
      event.preventDefault();
    }

    function onMouseMove(event) {
      if (!dragState) {
        return;
      }

      if (moveDrag(event.clientX, event.clientY)) {
        event.preventDefault();
      }
    }

    function getTouchPoint(event) {
      if (event.touches && event.touches[0]) {
        return event.touches[0];
      }

      if (event.changedTouches && event.changedTouches[0]) {
        return event.changedTouches[0];
      }

      return null;
    }

    function onTouchStart(event) {
      var touch = getTouchPoint(event);
      if (!touch) {
        return;
      }

      startDrag(touch.clientX, touch.clientY);
    }

    function onTouchMove(event) {
      var touch = getTouchPoint(event);
      if (!touch) {
        return;
      }

      if (moveDrag(touch.clientX, touch.clientY)) {
        event.preventDefault();
      }
    }

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        goToPage(activePage - 1);
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        goToPage(activePage + 1);
      });
    }

    var raf = null;
    track.addEventListener('scroll', function () {
      if (raf) {
        cancelAnimationFrame(raf);
      }

      raf = requestAnimationFrame(updateControls);
    }, { passive: true });

    track.addEventListener('mousedown', onMouseDown);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', endDrag);
    track.addEventListener('mouseleave', endDrag);

    track.addEventListener('touchstart', onTouchStart, { passive: true });
    track.addEventListener('touchmove', onTouchMove, { passive: false });
    track.addEventListener('touchend', endDrag);
    track.addEventListener('touchcancel', endDrag);

    track.addEventListener('click', function (event) {
      if (!suppressClick) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      suppressClick = false;
    }, true);

    track.addEventListener('dragstart', function (event) {
      event.preventDefault();
    });

    window.addEventListener('resize', function () {
      refresh();
    });

    refresh();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-related-doctors').forEach(init);
  });
})();