(function ($) {
  function isBlank(value) {
    return $.trim(String(value || '')) === '';
  }

  function setFieldValue($field, value) {
    if (!$field.length) {
      return;
    }

    var $input = $field.find('input, textarea, select').first();

    if (!$input.length) {
      return;
    }

    $input.val(value).trigger('change');
  }

  function getRepeaterRows($layout) {
    return $layout.find('.acf-field[data-name="items"] .acf-repeater tbody > tr').not('.acf-clone');
  }

  function rowHasContent($row) {
    var title = $row.find('.acf-field[data-name="title"] textarea, .acf-field[data-name="title"] input').first().val();
    var description = $row.find('.acf-field[data-name="description"] textarea').first().val();
    var iconKey = $row.find('.acf-field[data-name="icon_key"] select').first().val();

    return !isBlank(title) || !isBlank(description) || !isBlank(iconKey);
  }

  function populateMedicalManagementGrid($layout) {
    var defaults = window.hjAcfModuleDefaults && window.hjAcfModuleDefaults.medicalManagementGrid;

    if (!defaults) {
      return;
    }

    setFieldValue($layout.find('.acf-field[data-name="title"]').first(), defaults.title || '');
    setFieldValue($layout.find('.acf-field[data-name="title_accent"]').first(), defaults.title_accent || '');
    setFieldValue($layout.find('.acf-field[data-name="title_suffix"]').first(), defaults.title_suffix || '');
    setFieldValue($layout.find('.acf-field[data-name="intro"]').first(), defaults.intro || '');

    var $repeater = $layout.find('.acf-field[data-name="items"]').first();
    if (!$repeater.length) {
      return;
    }

    var $rows = getRepeaterRows($layout);
    var hasMeaningfulContent = false;

    $rows.each(function () {
      if (rowHasContent($(this))) {
        hasMeaningfulContent = true;
      }
    });

    if (hasMeaningfulContent) {
      return;
    }

    $rows.remove();

    var defaultsItems = Array.isArray(defaults.items) ? defaults.items : [];
    if (!defaultsItems.length) {
      return;
    }

    var $addButton = $repeater.find('.acf-actions .acf-button, .acf-actions a.button').first();
    if (!$addButton.length) {
      return;
    }

    defaultsItems.forEach(function () {
      $addButton.trigger('click');
    });

    $rows = getRepeaterRows($layout);

    $rows.each(function (index) {
      var item = defaultsItems[index] || {};
      var $row = $(this);

      setFieldValue($row.find('.acf-field[data-name="icon_key"]').first(), item.icon_key || '');
      setFieldValue($row.find('.acf-field[data-name="title"]').first(), item.title || '');
      setFieldValue($row.find('.acf-field[data-name="description"]').first(), item.description || '');
    });
  }

  function initModuleDefaults($context) {
    $context.find('.layout[data-layout="medical_management_grid"]').each(function () {
      populateMedicalManagementGrid($(this));
    });
  }

  $(function () {
    initModuleDefaults($(document));

    if (window.acf && acf.addAction) {
      acf.addAction('ready', function ($el) {
        initModuleDefaults($el || $(document));
      });

      acf.addAction('append', function ($el) {
        initModuleDefaults($el || $(document));
      });
    }
  });
})(jQuery);