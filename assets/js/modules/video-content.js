(function () {
  function buildVideo(type, src) {
    if (!src) {
      return null;
    }

    if (type === 'file') {
      var video = document.createElement('video');
      video.controls = true;
      video.autoplay = true;
      video.playsInline = true;
      video.src = src;
      return video;
    }

    var iframe = document.createElement('iframe');
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
      var child = body.firstChild;
      if (child.tagName === 'VIDEO') {
        child.pause();
        child.removeAttribute('src');
        child.load();
      }
      body.removeChild(child);
    }
  }

  function init(root) {
    var modal = root.querySelector('[data-vc-modal]');
    var modalBody = root.querySelector('[data-vc-modal-body]');
    var openButton = root.querySelector('[data-vc-open]');
    var closeButtons = root.querySelectorAll('[data-vc-close]');

    if (!modal || !modalBody || !openButton) {
      return;
    }

    function closeModal() {
      clearModal(modalBody);
      modal.hidden = true;
      document.body.classList.remove('hj-vc-modal-open');
      document.documentElement.classList.remove('hj-vc-modal-open');
    }

    openButton.addEventListener('click', function () {
      var type = openButton.getAttribute('data-video-type') || 'embed';
      var src = openButton.getAttribute('data-video-src') || '';
      var media = buildVideo(type, src);
      if (!media) {
        return;
      }

      clearModal(modalBody);
      modalBody.appendChild(media);
      modal.hidden = false;
      document.body.classList.add('hj-vc-modal-open');
      document.documentElement.classList.add('hj-vc-modal-open');
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
    document.querySelectorAll('.hj-video-content').forEach(init);
  });
})();