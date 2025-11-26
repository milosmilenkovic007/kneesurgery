<?php
$w_title = get_sub_field('welcome_title');
$w_sub   = get_sub_field('welcome_subtitle');
$w_btn   = get_sub_field('start_label') ?: 'Start';

$q_title = get_sub_field('question_title');
$q_sub   = get_sub_field('question_subtitle');
$options = get_sub_field('options') ?: [];

$u_title = get_sub_field('upload_title');
$u_sub   = get_sub_field('upload_subtitle');

$b_title = get_sub_field('booking_title');
$b_sub   = get_sub_field('booking_subtitle');
$b_cta   = get_sub_field('booking_cta');

$uid = uniqid('hj-candidate-');
?>
<section class="hj-candidate" id="<?php echo esc_attr($uid); ?>" aria-hidden="true" hidden>
  <div class="hj-cand-overlay" data-close></div>
  <div class="hj-cand-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($uid); ?>-title">
    <button class="hj-cand-close" type="button" aria-label="Close" data-close>×</button>

    <!-- Step 1: Welcome -->
    <div class="hj-cand-step is-active" data-step="1">
      <h2 id="<?php echo esc_attr($uid); ?>-title" class="title"><?php echo esc_html($w_title); ?></h2>
      <?php if ($w_sub): ?><p class="sub"><?php echo esc_html($w_sub); ?></p><?php endif; ?>
      <div class="actions">
        <button class="btn-primary" data-next><?php echo esc_html($w_btn); ?></button>
      </div>
    </div>

    <!-- Step 2: Options -->
    <div class="hj-cand-step" data-step="2" hidden>
      <h2 class="title"><?php echo esc_html($q_title); ?></h2>
      <?php if ($q_sub): ?><p class="sub"><?php echo esc_html($q_sub); ?></p><?php endif; ?>
      <div class="options">
        <?php foreach ($options as $i => $opt): $label = $opt['label'] ?? ''; $img = $opt['image'] ?? null; $src = $img['url'] ?? ''; ?>
          <button type="button" class="opt" data-value="<?php echo esc_attr($label); ?>">
            <?php if ($src): ?><img src="<?php echo esc_url($src); ?>" alt="" /><?php endif; ?>
            <span class="lbl"><?php echo esc_html($label); ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next disabled>Continue</button>
      </div>
    </div>

    <!-- Step 3: Upload -->
    <div class="hj-cand-step" data-step="3" hidden>
      <h2 class="title"><?php echo esc_html($u_title); ?></h2>
      <?php if ($u_sub): ?><p class="sub"><?php echo esc_html($u_sub); ?></p><?php endif; ?>
      <div class="uploads">
        <?php $tips = ['Smile','Left','Right']; for ($i=1;$i<=3;$i++): $tip = $tips[$i-1]; ?>
          <div class="upload-cell">
            <div class="drop-title" aria-hidden="true"><?php echo esc_html($tip); ?></div>
            <label class="drop" data-index="<?php echo $i; ?>">
              <input type="file" accept="image/*" capture="environment" />
              <span class="hint">Tap to add photo</span>
              <div class="cta">
                <button type="button" class="btn-ghost open-camera" aria-label="Open camera">
                  <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/capture.png'); ?>" alt="" />
                </button>
                <button type="button" class="btn-ghost open-upload" aria-label="Upload photo">
                  <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/upload.png'); ?>" alt="" />
                </button>
              </div>
              <div class="controls" hidden>
                <button type="button" class="btn-ctrl retake" aria-label="Retake">↺</button>
                <button type="button" class="btn-ctrl remove" aria-label="Remove">✕</button>
              </div>
              <div class="status" aria-live="polite"></div>
              <div class="cam" hidden>
                <video playsinline autoplay></video>
                <div class="cam-actions">
                  <button type="button" class="btn-primary cam-snap">Snap</button>
                  <button type="button" class="btn-secondary cam-cancel">Cancel</button>
                </div>
              </div>
            </label>
          </div>
        <?php endfor; ?>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 4: Booking -->
    <div class="hj-cand-step" data-step="4" hidden>
      <h2 class="title"><?php echo esc_html($b_title); ?></h2>
      <?php if ($b_sub): ?><p class="sub"><?php echo esc_html($b_sub); ?></p><?php endif; ?>
      <?php if (!empty($b_cta['label']) && !empty($b_cta['url'])): ?>
        <div class="actions">
          <a class="btn-primary" href="<?php echo esc_url($b_cta['url']); ?>"><?php echo esc_html($b_cta['label']); ?> →</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('<?php echo esc_js($uid); ?>');
    if(!root) return;
    const steps = Array.from(root.querySelectorAll('.hj-cand-step'));
    const nextBtns = root.querySelectorAll('[data-next]');
    const prevBtns = root.querySelectorAll('[data-prev]');
    const closeEls = root.querySelectorAll('[data-close]');
    let idx = 0; // 0-based
    let lastFocus = null;
    let isOpen = false;

    function getFocusable(){
      return Array.from(root.querySelectorAll('a[href],button,textarea,input,select,[tabindex]:not([tabindex="-1"])'))
        .filter(el=>!el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
    }

    function onKeydown(e){
      if(!isOpen) return;
      if(e.key === 'Escape'){ e.preventDefault(); doClose(); return; }
      if(e.key === 'Tab'){
        const list = getFocusable();
        if(list.length===0) return;
        const first = list[0];
        const last = list[list.length-1];
        if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
        else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
      }
    }

    function doClose(){
      root.setAttribute('hidden','');
      root.setAttribute('aria-hidden','true');
      document.documentElement.classList.remove('hj-cand-open');
      isOpen = false;
      document.removeEventListener('keydown', onKeydown, true);
      if(lastFocus && typeof lastFocus.focus === 'function'){
        lastFocus.focus();
      }
    }

    function show(i){
      steps.forEach((s,k)=>{
        const active = k===i;
        s.classList.toggle('is-active', active);
        if(active){ s.removeAttribute('hidden'); } else { s.setAttribute('hidden',''); }
      });
      idx = i;
    }

    nextBtns.forEach(b=> b.addEventListener('click', ()=>{
      if(idx < steps.length-1) show(idx+1);
    }));
    prevBtns.forEach(b=> b.addEventListener('click', ()=>{ if(idx>0) show(idx-1); }));
    closeEls.forEach(b=> b.addEventListener('click', doClose));

    // Step 2 selection logic
    const step2 = root.querySelector('[data-step="2"]');
    if(step2){
      const cont = step2.querySelector('[data-next]');
      step2.addEventListener('click', function(e){
        const btn = e.target.closest('.opt');
        if(!btn) return;
        step2.querySelectorAll('.opt').forEach(o=>o.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        cont.removeAttribute('disabled');
      });
    }

    // Open on #candidate click
    function openModal(){
      root.removeAttribute('hidden');
      root.setAttribute('aria-hidden','false');
      document.documentElement.classList.add('hj-cand-open');
      show(0);
      isOpen = true;
      lastFocus = document.activeElement;
      setTimeout(()=>{ const f=getFocusable(); if(f[0]) f[0].focus(); }, 0);
      document.addEventListener('keydown', onKeydown, true);
    }

    function maybeIntercept(e){
      const a = e.target.closest('[data-candidate], a[href*="#candidate"]');
      if(!a) return;
      e.preventDefault();
      openModal();
    }
    // Capture phase to reliably cancel default navigation
    document.addEventListener('click', maybeIntercept, true);

    // Open if URL already has #candidate (direct link) on load
    if (window.location.hash === '#candidate') {
      openModal();
    }
    // Also react on hash changes
    window.addEventListener('hashchange', function(){
      if (window.location.hash === '#candidate') { openModal(); }
    });

    // Step 3: upload handling (preview + AJAX upload)
    // Image compression helper
    async function compressImage(file, maxW=1280, maxH=1280, quality=0.8){
      return new Promise((resolve)=>{
        try{
          const img = new Image();
          img.onload = function(){
            let { width, height } = img;
            const ratio = Math.min(maxW/width, maxH/height, 1);
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
            const canvas = document.createElement('canvas');
            canvas.width = width; canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob((blob)=>{ resolve(blob || file); }, 'image/jpeg', quality);
          };
          img.onerror = function(){ resolve(file); };
          img.src = URL.createObjectURL(file);
        }catch(e){ resolve(file); }
      });
    }

    function applyPreview(wrap, blob){
      const url = URL.createObjectURL(blob);
      wrap.style.backgroundImage = 'url(' + url + ')';
      wrap.style.backgroundSize = 'cover';
      wrap.style.backgroundPosition = 'center';
      wrap.classList.add('has-preview');
      const ctrls = wrap.querySelector('.controls');
      if(ctrls){ ctrls.hidden = false; }
      const cta = wrap.querySelector('.cta');
      if(cta){ cta.hidden = true; }
    }

    async function uploadFile(file){
      const fd = new FormData();
      fd.append('action','hj_upload_candidate');
      fd.append('_ajax_nonce','<?php echo esc_js( wp_create_nonce('hj_candidate_upload') ); ?>');
      fd.append('file', file);
      const res = await fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', { method:'POST', body: fd });
      return res.json();
    }

    // Bind change (Upload dialog)
    root.querySelectorAll('.drop input[type="file"]').forEach(inp=>{
      inp.addEventListener('change', async function(){
        const file = this.files && this.files[0];
        if(!file) return;
        const wrap = this.closest('.drop');
        // compress
        const blob = await compressImage(file);
        const uploadFileObj = new File([blob], 'candidate.jpg', { type: 'image/jpeg' });
        // preview
        applyPreview(wrap, blob);
        try{
          const json = await uploadFile(uploadFileObj);
          if(json && json.success && json.data){ wrap.dataset.attachmentId = json.data.id; }
        }catch(err){ console.error('Upload error', err); }
      });
    });

    // Open upload via button
    root.addEventListener('click', function(e){
      const up = e.target.closest('.open-upload');
      if(up){
        const wrap = up.closest('.drop');
        const input = wrap.querySelector('input[type="file"]');
        input.click();
        e.preventDefault();
      }
    });

    // Camera helpers
    async function startCamera(wrap){
      const cam = wrap.querySelector('.cam');
      cam.hidden = false;
      wrap.classList.add('cam-open');
      const cta = wrap.querySelector('.cta'); if(cta) cta.hidden = true;
      const video = cam.querySelector('video');
      try{
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        video.srcObject = stream;
        cam.dataset.stream = 'on';
      }catch(err){
        console.warn('Camera not available', err);
        cam.hidden = true; wrap.classList.remove('cam-open');
        const status = wrap.querySelector('.status');
        if(status){ status.textContent = 'Camera unavailable. Use HTTPS/allow camera or use Upload.'; setTimeout(()=>status.textContent='', 5000); }
      }
    }
    function stopCamera(wrap){
      const cam = wrap.querySelector('.cam');
      const video = cam.querySelector('video');
      const stream = video.srcObject;
      if(stream && stream.getTracks){ stream.getTracks().forEach(t=>t.stop()); }
      video.srcObject = null;
      cam.hidden = true; wrap.classList.remove('cam-open');
      if(!wrap.classList.contains('has-preview')){ const cta = wrap.querySelector('.cta'); if(cta) cta.hidden = false; }
    }

    // Open camera
    root.addEventListener('click', function(e){
      const btn = e.target.closest('.open-camera');
      if(btn){
        const wrap = btn.closest('.drop');
        startCamera(wrap);
        e.preventDefault();
      }
    });

    // Camera snap/cancel
    root.addEventListener('click', async function(e){
      const cancel = e.target.closest('.cam-cancel');
      if(cancel){
        const wrap = cancel.closest('.drop');
        stopCamera(wrap);
        e.preventDefault();
        return;
      }
      const snap = e.target.closest('.cam-snap');
      if(snap){
        const wrap = snap.closest('.drop');
        const video = wrap.querySelector('.cam video');
        try{
          const w = video.videoWidth || 1280; const h = video.videoHeight || 720;
          const canvas = document.createElement('canvas');
          const max = 1280; const ratio = Math.min(max/w, max/h, 1);
          canvas.width = Math.round(w*ratio); canvas.height = Math.round(h*ratio);
          canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
          const blob = await new Promise(r=>canvas.toBlob(b=>r(b), 'image/jpeg', .85));
          // preview & upload
          applyPreview(wrap, blob);
          const uploadFileObj = new File([blob], 'candidate.jpg', { type: 'image/jpeg' });
          const json = await uploadFile(uploadFileObj);
          if(json && json.success && json.data){ wrap.dataset.attachmentId = json.data.id; }
        }catch(err){ console.error('Snap error', err); }
        stopCamera(wrap);
        e.preventDefault();
      }
    });

    // Retake / Remove controls
    root.addEventListener('click', function(e){
      const btnRemove = e.target.closest('.btn-ctrl.remove');
      if(btnRemove){
        const wrap = btnRemove.closest('.drop');
        wrap.style.backgroundImage = '';
        wrap.classList.remove('has-preview');
        wrap.querySelector('.controls').hidden = true;
        const cta = wrap.querySelector('.cta'); if(cta) cta.hidden = false;
        wrap.removeAttribute('data-attachment-id');
        const input = wrap.querySelector('input[type="file"]');
        input.value = '';
        e.preventDefault();
        return;
      }
      const btnRetake = e.target.closest('.btn-ctrl.retake');
      if(btnRetake){
        const wrap = btnRetake.closest('.drop');
        // prefer camera if available
        if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){ startCamera(wrap); }
        else { const input = wrap.querySelector('input[type="file"]'); input.click(); }
        e.preventDefault();
      }
    });
  });
  </script>
</section>
