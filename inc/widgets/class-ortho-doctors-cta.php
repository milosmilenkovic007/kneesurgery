<?php
if (!defined('ABSPATH')) exit;

class Ortho_Doctors_CTA_Widget extends WP_Widget {

  public function __construct() {
    parent::__construct(
      'ortho_doctors_cta',
      __('Doctors CTA (Ortho)', 'hello-elementor-child'),
      ['description' => __('Card with doctors image and a CTA button for the Ortho sidebar.', 'hello-elementor-child')]
    );
  }

  public function widget($args, $instance) {
    $title      = isset($instance['title']) ? $instance['title'] : __('Get a free consultation', 'hello-elementor-child');
    $image_id   = isset($instance['image_id']) ? intval($instance['image_id']) : 0;
    $button_txt = isset($instance['button_txt']) ? $instance['button_txt'] : __('Book now', 'hello-elementor-child');
    $button_url = isset($instance['button_url']) ? esc_url($instance['button_url']) : home_url('/contact/');

    echo $args['before_widget'];

    echo '<section class="odw-card">';
      echo '<div class="odw-media">';
        if ($image_id) {
          $src = wp_get_attachment_image_src($image_id, 'large');
          if ($src) {
            echo '<img src="'.esc_url($src[0]).'" alt="'.esc_attr(get_bloginfo('name')).'" loading="lazy" decoding="async">';
          }
        } else {
          // fallback: prazan sivi placeholder
          echo '<div class="odw-ph" aria-hidden="true"></div>';
        }
      echo '</div>';

      echo '<div class="odw-body">';
        echo '<h3 class="odw-title">'.esc_html($title).'</h3>';
        echo '<a class="btn btn-primary odw-btn" href="'.esc_url($button_url).'">'.esc_html($button_txt).' →</a>';
      echo '</div>';
    echo '</section>';

    echo $args['after_widget'];
  }

  public function form($instance) {
    $title      = isset($instance['title']) ? $instance['title'] : __('Get a free consultation', 'hello-elementor-child');
    $image_id   = isset($instance['image_id']) ? intval($instance['image_id']) : 0;
    $button_txt = isset($instance['button_txt']) ? $instance['button_txt'] : __('Book now', 'hello-elementor-child');
    $button_url = isset($instance['button_url']) ? $instance['button_url'] : home_url('/contact/');
    $img_src    = $image_id ? wp_get_attachment_image_src($image_id, 'medium') : false;
    ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'hello-elementor-child'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
             name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
             value="<?php echo esc_attr($title); ?>">
    </p>

    <p>
      <label><?php _e('Doctors Image', 'hello-elementor-child'); ?></label><br>
      <img class="odw-preview" src="<?php echo $img_src ? esc_url($img_src[0]) : ''; ?>"
           style="max-width:100%;height:auto;<?php echo $img_src ? '' : 'display:none;'; ?>">
      <input type="hidden" class="odw-image-id" id="<?php echo esc_attr($this->get_field_id('image_id')); ?>"
             name="<?php echo esc_attr($this->get_field_name('image_id')); ?>" value="<?php echo esc_attr($image_id); ?>">
      <button type="button" class="button button-secondary odw-upload"
              data-target="#<?php echo esc_attr($this->get_field_id('image_id')); ?>">
        <?php echo $img_src ? __('Change image', 'hello-elementor-child') : __('Choose image', 'hello-elementor-child'); ?>
      </button>
      <button type="button" class="button button-link-delete odw-remove" <?php echo $img_src ? '' : 'style="display:none"'; ?>>
        <?php _e('Remove', 'hello-elementor-child'); ?>
      </button>
    </p>

    <p>
      <label for="<?php echo esc_attr($this->get_field_id('button_txt')); ?>"><?php _e('Button Text', 'hello-elementor-child'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_txt')); ?>"
             name="<?php echo esc_attr($this->get_field_name('button_txt')); ?>" type="text"
             value="<?php echo esc_attr($button_txt); ?>">
    </p>

    <p>
      <label for="<?php echo esc_attr($this->get_field_id('button_url')); ?>"><?php _e('Button URL', 'hello-elementor-child'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_url')); ?>"
             name="<?php echo esc_attr($this->get_field_name('button_url')); ?>" type="url"
             value="<?php echo esc_attr($button_url); ?>">
    </p>
    <?php
  }

  public function update($new, $old) {
    $instance = [];
    $instance['title']      = sanitize_text_field($new['title'] ?? '');
    $instance['image_id']   = intval($new['image_id'] ?? 0);
    $instance['button_txt'] = sanitize_text_field($new['button_txt'] ?? '');
    $instance['button_url'] = esc_url_raw($new['button_url'] ?? '');
    return $instance;
  }
}
