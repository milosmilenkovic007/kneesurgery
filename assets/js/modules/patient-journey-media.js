(function () {
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