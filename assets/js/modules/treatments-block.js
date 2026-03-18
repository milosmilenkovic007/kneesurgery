document.addEventListener('DOMContentLoaded', function () {
  var blocks = document.querySelectorAll('.hj-treatments-block');

  blocks.forEach(function (block) {
    var toggles = block.querySelectorAll('.hj-tb-section__toggle');

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
      });
    });
  });
});
