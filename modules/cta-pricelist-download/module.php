<?php
$title    = get_sub_field('title');
$subtitle = get_sub_field('subtitle');
$generate = (bool) get_sub_field('generate_from_pricelist');
$pdf      = get_sub_field('pdf_file'); // array
$btn_lbl  = get_sub_field('btn_label') ?: 'Download Pricelist (PDF)';
$email_lbl= get_sub_field('email_label') ?: 'Send me by Email';
$note     = get_sub_field('note');
$secondary= get_sub_field('secondary');

$uid = uniqid('hj-cpd-');
$href = '';
$download_name = '';
if (!$generate && !empty($pdf) && is_array($pdf)) {
  $href = $pdf['url'] ?? '';
  if (!empty($pdf['filename'])) { $download_name = $pdf['filename']; }
  if (!$download_name && !empty($href)) { $download_name = basename(parse_url($href, PHP_URL_PATH)); }
}
if ($generate){
  $post_id = get_queried_object_id();
  $href = function_exists('hj_get_pricelist_pdf_url') ? hj_get_pricelist_pdf_url($post_id) : '';
  $download_name = 'healing-journey-pricelist-' . $post_id . '.pdf';
}
?>
<section class="hj-cta-pricelist" id="<?php echo esc_attr($uid); ?>" aria-label="Download Pricelist">
  <div class="hj-cpd-wrap">
    <div class="content">
      <?php if ($title): ?>
        <h2 class="hj-cpd-title hj-hd-title hj-flex-h2"><?php echo esc_html($title); ?></h2>
      <?php endif; ?>
      <?php if ($subtitle): ?>
        <p class="hj-cpd-sub"><?php echo esc_html($subtitle); ?></p>
      <?php endif; ?>

      <div class="hj-cpd-cta">
        <?php if ($href): ?>
          <a class="btn-primary" href="<?php echo esc_url($href); ?>" download="<?php echo esc_attr($download_name); ?>">
            <span class="ic" aria-hidden="true"><img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/pdf-93.png'); ?>" alt="" /></span>
            <?php echo esc_html($btn_lbl); ?>
          </a>
        <?php endif; ?>

        <button class="btn-secondary btn-email-toggle" type="button"><?php echo esc_html($email_lbl); ?> →</button>

        <?php if (!empty($secondary['label']) && !empty($secondary['url'])): ?>
          <a class="btn-secondary" href="<?php echo esc_url($secondary['url']); ?>"><?php echo esc_html($secondary['label']); ?></a>
        <?php endif; ?>

        <?php if ($note): ?><div class="note">*<?php echo esc_html($note); ?></div><?php endif; ?>
      </div>

      <form class="hj-cpd-email" data-generate="<?php echo $generate ? '1':'0'; ?>" data-pdf-id="<?php echo isset($pdf['ID']) ? intval($pdf['ID']) : 0; ?>">
        <input type="email" name="email" required placeholder="Enter your email" />
        <button type="submit" class="btn-primary">Send</button>
        <span class="status" aria-live="polite"></span>
      </form>
    </div>

    <div class="side-ill" aria-hidden="true">
      <div class="badge">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/pdf-93.png'); ?>" alt="" />
      </div>
    </div>
  </div>
</section>

<script>
(function(){
  const wrap = document.currentScript.previousElementSibling; // section
  const form = wrap.querySelector('.hj-cpd-email');
  const toggle = wrap.querySelector('.btn-email-toggle');
  if(toggle && form){
    toggle.addEventListener('click', ()=>{
      form.classList.toggle('is-open');
      if(form.classList.contains('is-open')){ form.querySelector('input[type=email]').focus(); }
    });
  }
  if(form){
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const email = form.querySelector('input[type=email]').value.trim();
      const status = form.querySelector('.status');
      if(!email){ return; }
      status.textContent = 'Sending…';
      const data = new URLSearchParams();
      data.append('action','hj_send_pricelist_pdf');
      data.append('email', email);
      data.append('post_id', '<?php echo esc_js(get_queried_object_id()); ?>');
      data.append('generate', form.dataset.generate);
      data.append('pdf_id', form.dataset.pdfId || '0');
      data.append('_ajax_nonce', '<?php echo esc_js( wp_create_nonce('hj_send_pricelist_pdf') ); ?>');
      try{
        const res = await fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:data.toString() });
        const json = await res.json();
        status.textContent = json.success ? 'Sent!' : (json.data || 'Error');
      }catch(err){ status.textContent = 'Error'; }
    });
  }
})();
</script>
