(function () {
  function closePanel(root, toggle, panel) {
    if (!root || !toggle || !panel) {
      return;
    }

    toggle.setAttribute('aria-expanded', 'false');
    panel.hidden = true;
    document.body.classList.remove('hj-header-menu-open');
  }

  function openPanel(root, toggle, panel) {
    if (!root || !toggle || !panel) {
      return;
    }

    toggle.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    document.body.classList.add('hj-header-menu-open');
  }

  function initHeader(root) {
    const toggle = root.querySelector('[data-hj-header-toggle]');
    const panel = root.querySelector('[data-hj-header-panel]');
    const closeButtons = root.querySelectorAll('[data-hj-header-close]');
    const panelLinks = root.querySelectorAll('.hj-site-header__mobile-menu a:not(.hj-site-header__mobile-submenu-toggle), .hj-site-header__cta--mobile');

    if (!toggle || !panel) {
      return;
    }

    panel.querySelectorAll('.hj-site-header__mobile-menu > .menu-item-has-children').forEach(function (item, index) {
      const existingToggle = item.querySelector(':scope > .hj-site-header__mobile-submenu-toggle');
      const submenu = item.querySelector(':scope > .sub-menu');
      const anchor = item.querySelector(':scope > a');

      if (existingToggle || !submenu || !anchor) {
        return;
      }

      const submenuId = 'hj-mobile-submenu-' + index;
      submenu.id = submenuId;

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'hj-site-header__mobile-submenu-toggle';
      button.setAttribute('aria-expanded', 'false');
      button.setAttribute('aria-controls', submenuId);
      button.setAttribute('aria-label', 'Toggle submenu');

      anchor.insertAdjacentElement('afterend', button);

      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const isExpanded = item.classList.contains('is-expanded');
        item.classList.toggle('is-expanded', !isExpanded);
        button.setAttribute('aria-expanded', String(!isExpanded));
      });
    });

    toggle.addEventListener('click', function () {
      const isOpen = toggle.getAttribute('aria-expanded') === 'true';

      if (isOpen) {
        closePanel(root, toggle, panel);
        return;
      }

      openPanel(root, toggle, panel);
    });

    closeButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        closePanel(root, toggle, panel);
      });
    });

    panelLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        closePanel(root, toggle, panel);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closePanel(root, toggle, panel);
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024) {
        closePanel(root, toggle, panel);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-hj-header]').forEach(initHeader);
  });
})();