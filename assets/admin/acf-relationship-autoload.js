(function($){
  $(function(){
    // Target the Articles Grid 'Posts' relationship field by key
    var selector = '.acf-field-relationship[data-key="field_hj_ag_posts"]';
    function triggerLoad($field){
      var $search = $field.find('.acf-relationship .filters input[type="search"], .acf-relationship .filters .search');
      if($search.length){
        // Nudge ACF to run its AJAX load by simulating a quick search
        var prev = $search.val();
        $search.val(' ').trigger('keyup');
        setTimeout(function(){ $search.val(prev).trigger('keyup'); }, 60);
      } else {
        // Fallback: force refresh
        try{ acf.doAction('refresh'); }catch(e){}
      }
    }

    // Initial on load
    $(selector).each(function(){ triggerLoad($(this)); });

    // Also when switching the mode button group (manual vs category)
    $(document).on('click', '.acf-button-group[data-key="field_hj_ag_mode"] label', function(){
      setTimeout(function(){ $(selector).each(function(){ triggerLoad($(this)); }); }, 100);
    });
  });
})(jQuery);
