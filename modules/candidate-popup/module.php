<?php
$w_title = get_sub_field('welcome_title');
$w_sub   = get_sub_field('welcome_subtitle');
$w_btn   = get_sub_field('start_label') ?: 'Start';
$w_logo  = get_sub_field('welcome_logo');

$q_title = get_sub_field('question_title');
$q_sub   = get_sub_field('question_subtitle');
$options = get_sub_field('options') ?: [];
$desc_label = get_sub_field('description_label') ?: 'Describe your dental needs in your own words';
$desc_placeholder = get_sub_field('description_placeholder') ?: 'Tell us more about your specific concerns...';

$u_title = get_sub_field('upload_title');
$u_sub   = get_sub_field('upload_subtitle');
$u_cover = get_sub_field('upload_cover');
$u_cover_url = is_array($u_cover) ? ($u_cover['url'] ?? '') : '';
$u_covers = get_sub_field('upload_covers') ?: [];

$m_title = get_sub_field('medical_title');
$m_sub   = get_sub_field('medical_subtitle');
$age_title = get_sub_field('age_title') ?: ($m_title ?: 'Your Medical Background');
$age_sub   = get_sub_field('age_subtitle') ?: $m_sub;
$chronic_title = get_sub_field('chronic_title') ?: 'Do you have any chronic illnesses?';
$chronic_sub   = get_sub_field('chronic_subtitle') ?: 'This information helps our medical team assess your health profile and plan your treatment safely.';
$meds_title = get_sub_field('medications_title') ?: 'Are You Taking Any Medications?';
$meds_sub   = get_sub_field('medications_subtitle') ?: 'This information ensures that your treatment plan is safe and medically appropriate.';
$xray_title = get_sub_field('xray_title') ?: 'X-ray (optional)';
$xray_sub   = get_sub_field('xray_subtitle') ?: '';

$ff_sc   = get_sub_field('ff_shortcode');
$ff_map  = get_sub_field('ff_map') ?: [];

$b_title = get_sub_field('booking_title');
$b_sub   = get_sub_field('booking_subtitle');
$b_cta   = get_sub_field('booking_cta');

$uid = uniqid('hj-candidate-');
?>
<section class="hj-candidate" id="<?php echo esc_attr($uid); ?>" aria-hidden="true" hidden>
  <div class="hj-cand-overlay" data-close></div>
  <div class="hj-cand-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($uid); ?>-title">
    <button class="hj-cand-close" type="button" aria-label="Close" data-close>×</button>
    <div class="hj-cand-progress" aria-hidden="true"></div>

    <!-- Step 1: Welcome -->
    <div class="hj-cand-step is-active" data-step="1">
      <h2 id="<?php echo esc_attr($uid); ?>-title" class="title"><?php echo esc_html($w_title); ?></h2>
      <?php if (!empty($w_logo['url'])): ?><div class="brand"><img src="<?php echo esc_url($w_logo['url']); ?>" alt="" /></div><?php endif; ?>
      <?php if ($w_sub): ?><p class="sub"><?php echo esc_html($w_sub); ?></p><?php endif; ?>
      <div class="actions">
        <button class="btn-primary" data-next><?php echo esc_html($w_btn); ?></button>
      </div>
    </div>

    <!-- Step 2: Dental Needs Options + Description -->
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

      <div class="desc-section">
        <label class="desc-field">
          <span class="desc-lbl"><?php echo esc_html($desc_label); ?></span>
          <textarea class="desc-input" rows="4" placeholder="<?php echo esc_attr($desc_placeholder); ?>" data-desc="needs"></textarea>
        </label>
      </div>

      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 3: Age -->
    <div class="hj-cand-step" data-step="3" hidden>
      <h2 class="title"><?php echo esc_html($age_title); ?></h2>
      <?php if($age_sub): ?><p class="sub"><?php echo esc_html($age_sub); ?></p><?php endif; ?>
      <div class="med-grid">
        <label class="med-field">
          <input type="number" min="1" max="99" inputmode="numeric" pattern="[0-9]*" class="mf-input mf-age" data-med="age" placeholder="Age" />
        </label>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 4: Chronic illnesses -->
    <div class="hj-cand-step" data-step="4" hidden>
      <h2 class="title"><?php echo esc_html($chronic_title); ?></h2>
      <?php if($chronic_sub): ?><p class="sub"><?php echo esc_html($chronic_sub); ?></p><?php endif; ?>
      <div class="med-grid">
        <div class="med-field">
          <div class="mf-row">
            <label><input type="radio" name="med-chronic" value="No" checked> No</label>
            <label><input type="radio" name="med-chronic" value="Yes"> Yes</label>
          </div>
          <input type="text" class="mf-input mf-chronic-details" data-med="chronic_details" placeholder="Type your chronic illnesses" disabled hidden />
        </div>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 5: Medications -->
    <div class="hj-cand-step" data-step="5" hidden>
      <h2 class="title"><?php echo esc_html($meds_title); ?></h2>
      <?php if($meds_sub): ?><p class="sub"><?php echo esc_html($meds_sub); ?></p><?php endif; ?>
      <div class="med-grid">
        <div class="med-field">
          <div class="mf-row">
            <label><input type="radio" name="med-meds" value="No" checked> No</label>
            <label><input type="radio" name="med-meds" value="Yes"> Yes</label>
          </div>
          <input type="text" class="mf-input mf-meds-details" data-med="meds_details" placeholder="Type your medications" disabled hidden />
        </div>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 6: Upload photos -->
    <div class="hj-cand-step" data-step="6" hidden>
      <h2 class="title"><?php echo esc_html($u_title); ?></h2>
      <?php if ($u_sub): ?><p class="sub"><?php echo esc_html($u_sub); ?></p><?php endif; ?>
      <div class="uploads">
        <?php 
          $tips = ['Smile','Anterior','Left Buccal','Right Buccal','Mandibular Occlusal','Maxillary Occlusal'];
          $map = [
            'Smile' => 'cover_smile',
            'Anterior' => 'cover_anterior',
            'Left Buccal' => 'cover_left_buccal',
            'Right Buccal' => 'cover_right_buccal',
            'Mandibular Occlusal' => 'cover_mandibular_occlusal',
            'Maxillary Occlusal' => 'cover_maxillary_occlusal',
          ];
          for ($i=1;$i<=6;$i++): $tip = $tips[$i-1]; 
            $field = $map[$tip] ?? '';
            $coverArr = $field && isset($u_covers[$field]) ? $u_covers[$field] : null;
            $coverUrl = is_array($coverArr) ? ($coverArr['url'] ?? '') : '';
            if(!$coverUrl) { $coverUrl = $u_cover_url; }
        ?>
          <div class="upload-cell">
            <div class="drop-title" aria-hidden="true"><?php echo esc_html($tip); ?></div>
            <?php
              $style_attr = '';
              if ($coverUrl) {
                $style_value = "--drop-cover: url('" . esc_url($coverUrl) . "')";
                $style_attr = ' style="' . esc_attr($style_value) . '"';
              }
            ?>
            <label class="drop" data-index="<?php echo esc_attr($i); ?>"<?php echo $style_attr; ?>>
                <input type="file" accept="image/*" />
              <span class="hint">Tap to add photo</span>
              <div class="cta">
                <button type="button" class="btn-ghost open-camera" aria-label="Open camera" title="Take photo" data-tip="Take photo">
                  <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/capture.png'); ?>" alt="" />
                </button>
                <button type="button" class="btn-ghost open-upload" aria-label="Upload photo" title="Upload file" data-tip="Upload file">
                  <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/upload.png'); ?>" alt="" />
                </button>
              </div>
              <div class="controls" hidden>
                <button type="button" class="btn-ctrl remove" aria-label="Remove" title="Remove" data-tip="Remove">✕</button>
              </div>
              <div class="status" aria-live="polite"></div>
              <div class="cam" hidden>
                <video playsinline autoplay></video>
                <div class="cam-actions">
                  <button type="button" class="cam-ico cam-snap" aria-label="Take photo" title="Take photo" data-tip="Take photo">
                    <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/capture.png'); ?>" alt="" />
                  </button>
                  <button type="button" class="cam-ico cam-cancel" aria-label="Cancel" title="Cancel" data-tip="Cancel">✕</button>
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

    <!-- Step 7: X-ray (optional) -->
    <div class="hj-cand-step" data-step="7" hidden>
      <h2 class="title"><?php echo esc_html($xray_title); ?></h2>
      <?php if($xray_sub): ?><p class="sub"><?php echo esc_html($xray_sub); ?></p><?php endif; ?>
      <div class="uploads uploads--single">
        <div class="upload-cell">
          <div class="drop-title" aria-hidden="true">X-ray (optional)</div>
          <label class="drop" data-index="xray">
            <input type="file" accept="application/pdf,image/jpeg,image/png" />
            <span class="hint hint--xray">PDF, JPG, PNG</span>
            <div class="cta">
              <button type="button" class="btn-ghost open-upload" aria-label="Upload X-ray" title="Upload file" data-tip="Upload file">
                <img class="ic" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/upload.png'); ?>" alt="" />
              </button>
            </div>
            <div class="controls" hidden>
              <button type="button" class="btn-ctrl remove" aria-label="Remove" title="Remove" data-tip="Remove">✕</button>
            </div>
            <div class="status" aria-live="polite"></div>
          </label>
        </div>
      </div>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary" data-next>Continue</button>
      </div>
    </div>

    <!-- Step 8: Booking / Submit -->
    <div class="hj-cand-step" data-step="8" hidden>
      <h2 class="title"><?php echo esc_html($b_title); ?></h2>
      <?php if ($b_sub): ?><p class="sub"><?php echo esc_html($b_sub); ?></p><?php endif; ?>
      <?php /* ACF CTA removed – we submit via Fluent Form button below */ ?>

      <?php if ($ff_sc): ?>
        <div class="ff-wrap">
          <?php echo do_shortcode($ff_sc); ?>
        </div>
      <?php endif; ?>
      <div class="actions">
        <button class="btn-secondary" data-prev>Back</button>
        <button class="btn-primary btn-submit-custom" type="button" data-submit-form>Book a Free Consultation →</button>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('<?php echo esc_js($uid); ?>');
    if(!root) return;
    const steps = Array.from(root.querySelectorAll('.hj-cand-step'));
    const progressWrap = root.querySelector('.hj-cand-progress');
    const nextBtns = root.querySelectorAll('[data-next]');
    const prevBtns = root.querySelectorAll('[data-prev]');
    const closeEls = root.querySelectorAll('[data-close]');
    let idx = 0; // 0-based
    let lastFocus = null;
    let isOpen = false;

    function buildProgress(){
      if(!progressWrap) return;
      progressWrap.style.setProperty('--cand-steps', steps.length);
      progressWrap.innerHTML = '';
      steps.forEach((_, i)=>{
        const seg = document.createElement('span');
        seg.className = 'seg';
        seg.dataset.idx = i;
        progressWrap.appendChild(seg);
      });
    }

    function updateProgress(activeIdx){
      if(!progressWrap) return;
      progressWrap.querySelectorAll('.seg').forEach((seg, i)=>{
        seg.classList.toggle('is-active', i === activeIdx);
        seg.classList.toggle('is-complete', i < activeIdx);
        seg.classList.toggle('is-upcoming', i > activeIdx);
      });
    }

    buildProgress();

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
      updateProgress(idx);

      // When we arrive at step 8, attempt to populate Fluent Form
      if(steps[idx] && steps[idx].dataset.step === '8'){
        setTimeout(populateFluentForm, 150);
      }
    }

    function showError(stepEl, msg){
      if(!stepEl) return;
      let box = stepEl.querySelector('.hj-error');
      if(!box){
        box = document.createElement('div');
        box.className = 'hj-error';
        const actions = stepEl.querySelector('.actions');
        if(actions) actions.insertAdjacentElement('beforebegin', box); else stepEl.appendChild(box);
      }
      box.textContent = msg || '';
    }

    function clearError(stepEl){ const box = stepEl?.querySelector('.hj-error'); if(box) box.textContent=''; }

    function canProceed(curIdx){
      const cur = steps[curIdx]; if(!cur) return true;
      clearError(cur);
      const stepNo = cur.dataset.step;
      if(stepNo === '2'){
        // require primary concern
        const opt = cur.querySelector('.opt.is-selected');
        if(!opt){ showError(cur, 'Please select an option.'); return false; }
        return true;
      }
      if(stepNo === '3'){
        const ageInput = cur.querySelector('[data-med="age"]');
        const ageVal = (ageInput?.value || '').trim();
        const ageNum = parseInt(ageVal, 10);
        if(!ageVal || isNaN(ageNum) || ageNum < 1 || ageNum > 99){ showError(cur, 'Please enter a valid age (1–99).'); return false; }
        return true;
      }
      if(stepNo === '4'){
        const chronicYes = cur.querySelector('input[name="med-chronic"]:checked')?.value === 'Yes';
        const chronicTxt = (cur.querySelector('.mf-chronic-details')?.value || '').trim();
        if(chronicYes && !chronicTxt){ showError(cur, 'Please describe your chronic illnesses.'); return false; }
        return true;
      }
      if(stepNo === '5'){
        const medsYes = cur.querySelector('input[name="med-meds"]:checked')?.value === 'Yes';
        const medsTxt = (cur.querySelector('.mf-meds-details')?.value || '').trim();
        if(medsYes && !medsTxt){ showError(cur, 'Please list your medications and doses.'); return false; }
        return true;
      }
      if(stepNo === '6'){
        const drops = cur.querySelectorAll('.drop');
        let has = false;
        drops.forEach(d=>{ if(d.dataset.index !== 'xray' && d.dataset.url) has = true; });
        if(!has){ showError(cur, 'Please add at least one photo.'); return false; }
        return true;
      }
      return true;
    }

    nextBtns.forEach(b=> b.addEventListener('click', ()=>{
      if(idx < steps.length-1){ if(canProceed(idx)) show(idx+1); }
    }));
    prevBtns.forEach(b=> b.addEventListener('click', ()=>{ if(idx>0) show(idx-1); }));
    closeEls.forEach(b=> b.addEventListener('click', doClose));

    // Custom submit button for step 8
    const submitBtn = root.querySelector('[data-submit-form]');
    if(submitBtn){
      submitBtn.addEventListener('click', function(){
        const ffForm = root.querySelector('[data-step="8"] .ff-wrap form');
        if(ffForm){
          const realSubmit = ffForm.querySelector('button[type="submit"]');
          if(realSubmit){ realSubmit.click(); }
        }
      });
    }

    // Step 2 selection logic (primary concern)
    const step2 = root.querySelector('[data-step="2"]');
    if(step2){
      step2.addEventListener('click', function(e){
        const btn = e.target.closest('.opt');
        if(!btn) return;
        step2.querySelectorAll('.opt').forEach(o=>o.classList.remove('is-selected'));
        btn.classList.add('is-selected');
      });
    }

    // Step 3: age input; Step 4/5 medical toggles
    const step3 = root.querySelector('[data-step="3"]');
    if(step3){
      const ageInput = step3.querySelector('[data-med="age"]');
      if(ageInput){
        ageInput.addEventListener('input', ()=>{
          const v = (ageInput.value || '').replace(/\D/g,'').slice(0,2);
          ageInput.value = v;
        });
      }
    }

    const step4 = root.querySelector('[data-step="4"]');
    if(step4){
      const chronicRadios = step4.querySelectorAll('input[name="med-chronic"]');
      const chronicDetails = step4.querySelector('.mf-chronic-details');
      function toggleChronic(){ const yes = step4.querySelector('input[name="med-chronic"]:checked')?.value === 'Yes'; chronicDetails.disabled = !yes; chronicDetails.hidden = !yes; if(!yes) chronicDetails.value=''; }
      chronicRadios.forEach(r=>r.addEventListener('change',toggleChronic));
      toggleChronic();
    }

    const step5 = root.querySelector('[data-step="5"]');
    if(step5){
      const medsRadios = step5.querySelectorAll('input[name="med-meds"]');
      const medsDetails = step5.querySelector('.mf-meds-details');
      function toggleMeds(){ const yes = step5.querySelector('input[name="med-meds"]:checked')?.value === 'Yes'; medsDetails.disabled = !yes; medsDetails.hidden = !yes; if(!yes) medsDetails.value=''; }
      medsRadios.forEach(r=>r.addEventListener('change',toggleMeds));
      toggleMeds();
    }

    // Open on #candidate click
    function openModal(){
      root.removeAttribute('hidden');
      root.setAttribute('aria-hidden','false');
      document.documentElement.classList.add('hj-cand-open');
      show(0);
      isOpen = true;
      lastFocus = document.activeElement;
      updateProgress(0);
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

    // Step 6/7: upload handling (preview + AJAX upload)
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

    // Canvas -> Blob helper with fallback for older Safari/iOS
    function canvasToBlob(canvas, type='image/jpeg', quality=.85){
      return new Promise(resolve=>{
        if (canvas.toBlob) {
          canvas.toBlob(b=>resolve(b), type, quality);
        } else {
          // Fallback: toDataURL then convert
          const dataURL = canvas.toDataURL(type, quality);
          const byteString = atob(dataURL.split(',')[1]);
          const mime = dataURL.split(',')[0].match(/:(.*?);/)[1] || type;
          const ab = new ArrayBuffer(byteString.length);
          const ia = new Uint8Array(ab);
          for (let i=0; i<byteString.length; i++) ia[i] = byteString.charCodeAt(i);
          resolve(new Blob([ab], {type: mime}));
        }
      });
    }

    function showProgress(wrap, percent){
      let prog = wrap.querySelector('.upload-progress');
      if(!prog){
        prog = document.createElement('div');
        prog.className = 'upload-progress';
        prog.innerHTML = '<div class="upload-progress-bar"></div><span class="upload-progress-text">Uploading...</span>';
        wrap.appendChild(prog);
      }
      const bar = prog.querySelector('.upload-progress-bar');
      const txt = prog.querySelector('.upload-progress-text');
      if(bar) bar.style.width = percent + '%';
      if(txt) txt.textContent = percent < 100 ? 'Uploading... ' + percent + '%' : 'Processing...';
      prog.hidden = false;
    }

    function hideProgress(wrap){
      const prog = wrap.querySelector('.upload-progress');
      if(prog) prog.hidden = true;
    }

    function applyPreview(wrap, fileOrBlob){
      const type = fileOrBlob.type || '';
      wrap.classList.add('has-preview');
      const ctrls = wrap.querySelector('.controls'); if(ctrls){ ctrls.hidden = false; }
      const cta = wrap.querySelector('.cta'); if(cta){ cta.hidden = true; }
      const status = wrap.querySelector('.status');
      if(type.startsWith('image/')){
        const url = URL.createObjectURL(fileOrBlob);
        wrap.style.backgroundImage = 'url(' + url + ')';
        wrap.style.backgroundSize = 'cover';
        wrap.style.backgroundPosition = 'center';
      } else {
        wrap.style.backgroundImage = '';
        if(status){ status.textContent = 'File added'; }
      }
    }

    async function uploadFile(file, onProgress){
      return new Promise((resolve, reject)=>{
        const fd = new FormData();
        fd.append('action','hj_upload_candidate');
        fd.append('_ajax_nonce','<?php echo esc_js( wp_create_nonce('hj_candidate_upload') ); ?>');
        fd.append('file', file);
        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', (e)=>{
          if(e.lengthComputable && onProgress){
            const percent = Math.round((e.loaded / e.total) * 100);
            onProgress(percent);
          }
        });
        xhr.addEventListener('load', ()=>{
          try{
            const json = JSON.parse(xhr.responseText);
            resolve(json);
          }catch(err){ reject(err); }
        });
        xhr.addEventListener('error', ()=>reject(new Error('Upload failed')));
        xhr.open('POST', '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>');
        xhr.send(fd);
      });
    }

    // Bind change (Upload dialog)
    root.querySelectorAll('.drop input[type="file"]').forEach(inp=>{
      inp.addEventListener('change', async function(){
        const file = this.files && this.files[0];
        if(!file) return;
        const wrap = this.closest('.drop');
        let toSend = file;
        showProgress(wrap, 0);
        // compress only images
        if(file.type && file.type.startsWith('image/')){
          const blob = await compressImage(file);
          toSend = new File([blob], 'candidate.jpg', { type: 'image/jpeg' });
        }
        // preview
        applyPreview(wrap, file);
        try{
          const json = await uploadFile(toSend, (percent)=>showProgress(wrap, percent));
          if(json && json.success && json.data){ wrap.dataset.attachmentId = json.data.id; if(json.data.url){ wrap.dataset.url = json.data.url; } }
          hideProgress(wrap);
        }catch(err){ console.error('Upload error', err); hideProgress(wrap); }
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

    // Prevent label default file-open when clicking anywhere on the tile
    // Allow only the upload icon to trigger the dialog (and camera icon for capture)
    root.addEventListener('click', function(e){
      const drop = e.target.closest('.drop');
      if(!drop) return;
      const isUpload = !!e.target.closest('.open-upload');
      const isCamera = !!e.target.closest('.open-camera');
      const isCtrl   = !!e.target.closest('.controls, .btn-ctrl, .cam');
      const isInput  = e.target.matches('input[type="file"]');
      if(!(isUpload || isCamera || isCtrl || isInput)){
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);

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
        if(status){ 
          status.innerHTML = 'Camera unavailable.<br><strong>Please use Upload button instead.</strong>'; 
          status.style.color = '#dc2626';
          status.style.fontSize = '13px';
          status.style.fontWeight = '600';
          status.style.marginTop = '8px';
        }
        const cta = wrap.querySelector('.cta'); if(cta) cta.hidden = false;
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
          showProgress(wrap, 0);
          const w = video.videoWidth || 1280; const h = video.videoHeight || 720;
          const canvas = document.createElement('canvas');
          const max = 1280; const ratio = Math.min(max/w, max/h, 1);
          canvas.width = Math.round(w*ratio); canvas.height = Math.round(h*ratio);
          canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
          const blob = await canvasToBlob(canvas, 'image/jpeg', .85);
          // preview & upload
          applyPreview(wrap, blob);
          const uploadFileObj = new File([blob], 'candidate.jpg', { type: 'image/jpeg' });
          const json = await uploadFile(uploadFileObj, (percent)=>showProgress(wrap, percent));
          if(json && json.success && json.data){ wrap.dataset.attachmentId = json.data.id; if(json.data.url){ wrap.dataset.url = json.data.url; } }
          hideProgress(wrap);
        }catch(err){ console.error('Snap error', err); hideProgress(wrap); }
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
      // retake/rotate removed
    });

    // Collect data and fill Fluent Form
    function collectData(){
      const data = {};
      // primary concern
      const selected = root.querySelector('[data-step="2"] .opt.is-selected');
      data.option = selected ? selected.getAttribute('data-value') : '';
      // dental needs description
      const descInput = root.querySelector('[data-step="2"] [data-desc="needs"]');
      data.description = descInput?.value || '';
      // medical
      const s3 = root.querySelector('[data-step="3"]');
      if(s3){ data.age = s3.querySelector('[data-med="age"]')?.value || ''; }
      const s4 = root.querySelector('[data-step="4"]');
      if(s4){
        const chronicYN = s4.querySelector('input[name="med-chronic"]:checked')?.value || 'No';
        data.chronic_yesno = chronicYN;
        data.chronic_details = s4.querySelector('.mf-chronic-details')?.value || '';
      }
      const s5 = root.querySelector('[data-step="5"]');
      if(s5){
        const medsYN = s5.querySelector('input[name="med-meds"]:checked')?.value || 'No';
        data.meds_yesno = medsYN;
        data.meds_details = s5.querySelector('.mf-meds-details')?.value || '';
      }
      // photos (urls preferred) + X-ray
      const photoDrops = root.querySelectorAll('[data-step="6"] .drop');
      data.photos = [];
      photoDrops.forEach(d=>{
        const u = d.dataset.url || '';
        if(d.dataset.index !== 'xray'){ data.photos.push(u); }
      });
      const xrayDrop = root.querySelector('[data-step="7"] .drop[data-index="xray"]');
      data.x_ray = xrayDrop ? (xrayDrop.dataset.url || '') : '';
      return data;
    }

    function populateFluentForm(){
      const data = collectData();
      const ff = root.querySelector('[data-step="8"] .ff-wrap form');
      if(!ff) return;

      const map = <?php echo wp_json_encode($ff_map); ?> || {};
      const defaults = { age:'age', chronic_yesno:'chronic_yesno', chronic_details:'chronic_details', meds_yesno:'meds_yesno', meds_details:'meds_details', option:'concern', description:'description', photo1:'photo_smile', photo2:'photo_anterior', photo3:'photo_left', photo4:'photo_right', photo5:'photo_mandibular', photo6:'photo_maxillary', x_ray:'x_ray' };
      const nameFor = (k)=> (map[k] && map[k].length ? map[k] : defaults[k]);

      function setField(name, value){
        if(!name) return;
        let el = ff.querySelector(`[name="${name}"]`);
        if(!el){ el = ff.querySelector(`[data-name="${name}"] input, [data-name="${name}"] textarea`); }
        if(!el){ el = ff.querySelector(`input[name*="${name}"]`); }
        if(!el) return;
        if(el.type === 'radio'){
          const radio = ff.querySelector(`input[type=radio][name="${el.name}"][value="${value}"]`)
                    || ff.querySelector(`input[type=radio][name="${el.name}"][value="${String(value).toLowerCase()}"]`)
                    || ff.querySelector(`input[type=radio][name="${el.name}"][value="${String(value).toUpperCase()}"]`);
          if(radio){ radio.checked = true; radio.dispatchEvent(new Event('change', {bubbles:true})); }
        }else{
          el.value = value;
          el.dispatchEvent(new Event('input', {bubbles:true}));
          el.dispatchEvent(new Event('change', {bubbles:true}));
        }
      }

      setField(nameFor('age'), data.age);
      setField(nameFor('chronic_yesno'), data.chronic_yesno);
      setField(nameFor('chronic_details'), data.chronic_details);
      setField(nameFor('meds_yesno'), data.meds_yesno);
      setField(nameFor('meds_details'), data.meds_details);
      setField(nameFor('option'), data.option);
      setField(nameFor('description'), data.description);
      for(let i=0;i<6;i++){
        setField(nameFor('photo'+(i+1)), data.photos[i] || '');
      }
      setField(nameFor('x_ray'), data.x_ray || '');
    }
  });
  </script>
</section>
