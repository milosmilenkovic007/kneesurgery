(function ($) {
  function bindUploader(context) {
    context.find('.odw-upload').off('click').on('click', function (e) {
      e.preventDefault();
      var button = $(this);
      var frame = wp.media({
        title: 'Select or Upload Image',
        library: { type: 'image' },
        button: { text: 'Use this image' },
        multiple: false
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        var wrap = button.closest('p');
        wrap.find('.odw-image-id').val(attachment.id);
        var preview = wrap.find('.odw-preview');
        preview.attr('src', attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url);
        preview.show();
        wrap.find('.odw-remove').show();
      });

      frame.open();
    });

    context.find('.odw-remove').off('click').on('click', function (e) {
      e.preventDefault();
      var wrap = $(this).closest('p');
      wrap.find('.odw-image-id').val('');
      wrap.find('.odw-preview').attr('src', '').hide();
      $(this).hide();
    });
  }

  // initial bind
  $(document).ready(function () { bindUploader($(document)); });

  // for widgets added dynamically
  $(document).on('widget-added widget-updated', function (e, widget) {
    bindUploader($(widget));
  });

})(jQuery);
