(function () {
  function positionDesktopSubmenu(item) {
    if (!item || window.innerWidth <= 1024) {
      return;
    }

    const submenu = item.querySelector(':scope > .sub-menu');

    if (!submenu) {
      return;
    }

    const viewportPadding = 24;
    const itemRect = item.getBoundingClientRect();
    const panelWidth = submenu.getBoundingClientRect().width;

    if (!panelWidth) {
      return;
    }

    const minCenter = viewportPadding + (panelWidth / 2);
    const maxCenter = window.innerWidth - viewportPadding - (panelWidth / 2);
    const preferredCenter = window.innerWidth / 2;
    const clampedCenter = Math.min(Math.max(preferredCenter, minCenter), maxCenter);
    const left = clampedCenter - itemRect.left;

    submenu.style.left = left + 'px';
  }

  function resetDesktopSubmenuPosition(item) {
    if (!item) {
      return;
    }

    const submenu = item.querySelector(':scope > .sub-menu');

    if (!submenu) {
      return;
    }

    submenu.style.left = '';
  }

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
    const desktopItems = root.querySelectorAll('.hj-site-header__menu > .menu-item-has-children');

    if (!toggle || !panel) {
      return;
    }

    panel.querySelectorAll('.hj-site-header__mobile-menu .menu-item-has-children').forEach(function (item, index) {
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

      const toggleSubmenu = function () {
        const isExpanded = item.classList.contains('is-expanded');
        item.classList.toggle('is-expanded', !isExpanded);
        button.setAttribute('aria-expanded', String(!isExpanded));
      };

      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        toggleSubmenu();
      });

      anchor.addEventListener('click', function (event) {
        if (window.innerWidth > 1024) {
          return;
        }

        event.preventDefault();
        toggleSubmenu();
      });
    });

    desktopItems.forEach(function (item) {
      const reposition = function () {
        positionDesktopSubmenu(item);
      };

      item.addEventListener('mouseenter', reposition);
      item.addEventListener('focusin', reposition);
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
      link.addEventListener('click', function (event) {
        if (event.defaultPrevented) {
          return;
        }

        closePanel(root, toggle, panel);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closePanel(root, toggle, panel);
      }
    });

    window.addEventListener('resize', function () {
      desktopItems.forEach(function (item) {
        if (window.innerWidth > 1024) {
          positionDesktopSubmenu(item);
          return;
        }

        resetDesktopSubmenuPosition(item);
      });

      if (window.innerWidth > 1024) {
        closePanel(root, toggle, panel);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-hj-header]').forEach(initHeader);
  });
})();