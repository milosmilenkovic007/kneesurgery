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
        <?php for ($i=1;$i<=3;$i++): ?>
          <label class="drop">
            <input type="file" accept="image/*" capture="environment" />
            <span class="hint">Tap to add photo</span>
          </label>
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
    closeEls.forEach(b=> b.addEventListener('click', ()=>{ root.setAttribute('hidden',''); root.setAttribute('aria-hidden','true'); document.documentElement.classList.remove('hj-cand-open'); }));

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
    function maybeIntercept(e){
      const a = e.target.closest('a[href*="#candidate"]');
      if(!a) return;
      e.preventDefault();
      root.removeAttribute('hidden');
      root.setAttribute('aria-hidden','false');
      document.documentElement.classList.add('hj-cand-open');
      show(0);
    }
    document.addEventListener('click', maybeIntercept);
  });
  </script>
</section>
