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
    const button = root.querySelector('[data-pjm-scroll-next]');
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

  function buildVideo(type, src) {
    if (!src) {
      return null;
    }

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

  function init(root) {
    const modal = root.querySelector('[data-pjm-modal]');
    const modalBody = root.querySelector('[data-pjm-modal-body]');
    const openButton = root.querySelector('[data-pjm-open]');
    const closeButtons = root.querySelectorAll('[data-pjm-close]');

    initNextBlockScroll(root);

    if (!modal || !modalBody || !openButton) {
      return;
    }

    function closeModal() {
      clearModal(modalBody);
      modal.hidden = true;
      document.body.classList.remove('hj-pjm-modal-open');
      document.documentElement.classList.remove('hj-pjm-modal-open');
    }

    openButton.addEventListener('click', function () {
      const type = openButton.getAttribute('data-video-type') || 'embed';
      const src = openButton.getAttribute('data-video-src') || '';
      const media = buildVideo(type, src);
      if (!media) {
        return;
      }

      clearModal(modalBody);
      modalBody.appendChild(media);
      modal.hidden = false;
      document.body.classList.add('hj-pjm-modal-open');
      document.documentElement.classList.add('hj-pjm-modal-open');
    });

    closeButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeModal();
      });

      button.addEventListener('touchend', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeModal();
      }, { passive: false });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !modal.hidden) {
        closeModal();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-patient-journey-media').forEach(init);
  });
})();