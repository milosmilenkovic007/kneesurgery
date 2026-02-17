<?php
if (!defined('ABSPATH')) exit;

get_header();
the_post();

$doctor_id = get_the_ID();

$doctor_title     = function_exists('get_field') ? get_field('doctor_title', $doctor_id) : '';
$short_bio        = function_exists('get_field') ? get_field('short_bio', $doctor_id) : '';
$education        = function_exists('get_field') ? get_field('education', $doctor_id) : [];
$medical_expertise = function_exists('get_field') ? get_field('medical_expertise', $doctor_id) : [];
$clinical_focus   = function_exists('get_field') ? get_field('clinical_focus', $doctor_id) : [];
$current_position = function_exists('get_field') ? get_field('current_position', $doctor_id) : '';
$treatments       = function_exists('get_field') ? get_field('treatments', $doctor_id) : [];

function hj_doc_render_repeater_list($rows) {
  if (empty($rows) || !is_array($rows)) {
    return;
  }

  echo '<ul class="hj-doc-list">';
  foreach ($rows as $row) {
    $item = is_array($row) && isset($row['item']) ? $row['item'] : '';
    if ('' === trim((string) $item)) {
      continue;
    }
    echo '<li>' . wp_kses_post($item) . '</li>';
  }
  echo '</ul>';
}
?>

<main id="primary" class="hj-doctor-single">
  <article <?php post_class('hj-doc'); ?> id="post-<?php the_ID(); ?>">
    <section class="hj-doc-hero">
      <div class="hj-doc-container">
        <div class="hj-doc-hero__grid">
          <div class="hj-doc-hero__media">
            <?php if (has_post_thumbnail()): ?>
              <figure class="hj-doc-photo">
                <?php the_post_thumbnail('large', ['loading' => 'eager', 'decoding' => 'async']); ?>
              </figure>
            <?php else: ?>
              <div class="hj-doc-photo hj-doc-photo--placeholder" aria-hidden="true"></div>
            <?php endif; ?>
          </div>

          <header class="hj-doc-hero__header">
            <h1 class="hj-doc-name"><?php the_title(); ?></h1>

            <?php if (!empty($doctor_title)): ?>
              <div class="hj-doc-title"><?php echo esc_html($doctor_title); ?></div>
            <?php endif; ?>

            <?php if (!empty($short_bio)): ?>
              <div class="hj-doc-bio"><?php echo wp_kses_post($short_bio); ?></div>
            <?php endif; ?>
          </header>
        </div>
      </div>
    </section>

    <section class="hj-doc-sections">
      <div class="hj-doc-container">
        <div class="hj-doc-cv">
          <?php if (!empty($current_position)): ?>
            <section class="hj-doc-card" aria-label="Current position">
              <h2 class="hj-doc-h2"><?php esc_html_e('Current Position', 'hello-elementor-child'); ?></h2>
              <div class="hj-doc-text"><?php echo wp_kses_post($current_position); ?></div>
            </section>
          <?php endif; ?>

          <?php if (!empty($education)): ?>
            <section class="hj-doc-card" aria-label="Education">
              <h2 class="hj-doc-h2"><?php esc_html_e('Education', 'hello-elementor-child'); ?></h2>
              <?php hj_doc_render_repeater_list($education); ?>
            </section>
          <?php endif; ?>

          <?php if (!empty($medical_expertise)): ?>
            <section class="hj-doc-card" aria-label="Medical expertise">
              <h2 class="hj-doc-h2"><?php esc_html_e('Medical Expertise', 'hello-elementor-child'); ?></h2>
              <?php hj_doc_render_repeater_list($medical_expertise); ?>
            </section>
          <?php endif; ?>

          <?php if (!empty($clinical_focus)): ?>
            <section class="hj-doc-card" aria-label="Clinical focus">
              <h2 class="hj-doc-h2"><?php esc_html_e('Clinical Focus', 'hello-elementor-child'); ?></h2>
              <?php hj_doc_render_repeater_list($clinical_focus); ?>
            </section>
          <?php endif; ?>

          <?php if (!empty($treatments) && is_array($treatments)): ?>
            <section class="hj-doc-card" aria-label="Treatments">
              <h2 class="hj-doc-h2"><?php esc_html_e('Treatments', 'hello-elementor-child'); ?></h2>
              <ul class="hj-doc-links">
                <?php foreach ($treatments as $treatment_id):
                  $treatment_id = (int) $treatment_id;
                  if (!$treatment_id) continue;
                  $link = get_permalink($treatment_id);
                  $label = get_the_title($treatment_id);
                  if (!$link || !$label) continue;
                ?>
                  <li><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($label); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </article>
</main>

<?php
get_footer();
