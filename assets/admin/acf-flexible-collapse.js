(function ($) {
  var modulesSelector = [
    '.acf-field[data-key="field_hj_modules_fc"]',
    '.acf-field[data-key="field_hj_modules_fc_service"]'
  ].join(',');

  function collapseField($field) {
    if (!$field.length || $field.data('hjModulesCollapsed')) {
      return;
    }

    var $openLayouts = $field.find('.layout').not('.acf-clone').not('.-collapsed');

    if (!$openLayouts.length) {
      $field.data('hjModulesCollapsed', true);
      return;
    }

    var $collapseLink = $field.find('.acf-actions a').filter(function () {
      return $.trim($(this).text()) === 'Collapse All';
    }).first();

    if ($collapseLink.length) {
      $collapseLink.trigger('click');
      $field.data('hjModulesCollapsed', true);
      return;
    }

    $openLayouts.each(function () {
      var $handle = $(this).children('.acf-fc-layout-handle');

      if ($handle.length) {
        $handle.trigger('click');
      }
    });

    $field.data('hjModulesCollapsed', true);
  }

  function initCollapse() {
    $(modulesSelector).each(function () {
      collapseField($(this));
    });
  }

  $(function () {
    initCollapse();

    window.setTimeout(initCollapse, 150);
    window.setTimeout(initCollapse, 500);
    $(window).on('load', initCollapse);

    if (window.acf && acf.addAction) {
      acf.addAction('ready', function () {
        window.setTimeout(initCollapse, 0);
        window.setTimeout(initCollapse, 200);
      });
    }
  });
})(jQuery);