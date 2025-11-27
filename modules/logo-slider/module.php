<?php
$prefix = trim((string) get_sub_field('title_prefix'));
$accent = trim((string) get_sub_field('title_accent'));
$note   = get_sub_field('note');
$logos  = get_sub_field('logos') ?: [];
$uid = uniqid('hj-ls-');
?>
<section class="hj-logo-slider" id="<?php echo esc_attr($uid); ?>" aria-label="Logo Slider">
  <div class="hj-ls-wrap">
    <div class="hj-ls-head">
      <?php if ($prefix || $accent): ?>
        <h2 class="hj-ls-title hj-hd-title">
          <?php if ($prefix): ?><span class="muted"><?php echo esc_html($prefix); ?></span> <?php endif; ?>
          <?php if ($accent): ?><span class="accent-italic"><?php echo esc_html($accent); ?></span><?php endif; ?>
        </h2>
      <?php endif; ?>
      <?php if ($note): ?><p class="hj-ls-note"><?php echo esc_html($note); ?></p><?php endif; ?>
    </div>
    <div class="hj-ls-divider" aria-hidden="true"></div>

    <?php if (!empty($logos)): ?>
      <div class="hj-ls-viewport">
        <div class="hj-ls-track" role="list">
          <?php foreach ($logos as $i => $logo): $img = $logo['image'] ?? null; if (!$img) continue; $url = $logo['url'] ?? ''; $alt = $logo['alt'] ?? ($img['alt'] ?? ''); $maxw = $logo['max_width'] ?? 0; $style = $maxw ? 'style="max-width:' . intval($maxw) . 'px"' : ''; ?>
            <?php if ($url): ?><a class="hj-ls-item" role="listitem" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" <?php echo $style; ?>>
            <?php else: ?><span class="hj-ls-item" role="listitem" <?php echo $style; ?>><?php endif; ?>
              <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($alt); ?>" draggable="false" />
            <?php if ($url): ?></a><?php else: ?></span><?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
  (function(){
    const root = document.getElementById('<?php echo esc_js($uid); ?>');
    if(!root) return;
    const track = root.querySelector('.hj-ls-track');
    if(!track) return;

    let isDown = false, startX = 0, scrollL = 0, paused = false, raf=0;
    const onDown = (e)=>{ isDown = true; track.classList.add('is-dragging'); startX = (e.pageX || e.touches?.[0]?.pageX || 0); scrollL = track.scrollLeft; };
    const onMove = (e)=>{ if(!isDown) return; const x = (e.pageX || e.touches?.[0]?.pageX || 0); const walk = (startX - x); track.scrollLeft = scrollL + walk; };
    const onUp = ()=>{ isDown = false; track.classList.remove('is-dragging'); };
    track.addEventListener('mousedown', onDown); track.addEventListener('touchstart', onDown, {passive:true});
    window.addEventListener('mousemove', onMove, {passive:false}); window.addEventListener('touchmove', onMove, {passive:false});
    window.addEventListener('mouseup', onUp); window.addEventListener('touchend', onUp);

    // Prevent click-through after drag
    track.addEventListener('click', function(e){ if(track.classList.contains('is-dragging')){ e.preventDefault(); e.stopPropagation(); track.classList.remove('is-dragging'); } }, true);

    // Auto-run marquee: duplicate items and smoothly scroll
    try{
      const clone = track.cloneNode(true);
      while(clone.firstChild){ track.appendChild(clone.firstChild); }
      const half = Math.floor(track.scrollWidth/2);
      const speed = 0.5; // px per frame
      function tick(){
        if(!paused && !isDown){
          track.scrollLeft += speed;
          if(track.scrollLeft >= half){ track.scrollLeft = 0; }
        }
        raf = requestAnimationFrame(tick);
      }
      tick();
      track.addEventListener('mouseenter', ()=>{ paused = true; });
      track.addEventListener('mouseleave', ()=>{ paused = false; });
      document.addEventListener('visibilitychange', ()=>{ paused = document.hidden; });
    }catch(err){}
  })();
  </script>
</section>
