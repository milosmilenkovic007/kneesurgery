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
    if (!body) {
      return;
    }

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
    const modal = root.querySelector('[data-vg-modal]');
    const modalBody = root.querySelector('[data-vg-modal-body]');
    const openButtons = root.querySelectorAll('[data-vg-open]');
    const closeButtons = root.querySelectorAll('[data-vg-close]');

    if (!modal || !modalBody || !openButtons.length) {
      return;
    }

    function closeModal() {
      clearModal(modalBody);
      modal.hidden = true;
      document.body.classList.remove('hj-vg-modal-open');
      document.documentElement.classList.remove('hj-vg-modal-open');
    }

    openButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const type = button.getAttribute('data-video-type') || 'embed';
        const src = button.getAttribute('data-video-src') || '';
        const media = buildVideo(type, src);

        if (!media) {
          return;
        }

        clearModal(modalBody);
        modalBody.appendChild(media);
        modal.hidden = false;
        document.body.classList.add('hj-vg-modal-open');
        document.documentElement.classList.add('hj-vg-modal-open');
      });
    });

    closeButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeModal();
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !modal.hidden) {
        closeModal();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hj-video-grid').forEach(init);
  });
})();