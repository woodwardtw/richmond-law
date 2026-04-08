<?php
/**
 * UnderStrap functions and definitions
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = 'inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/acf.php',                          // Load ACF functions.
	'/custom-data.php',                          // Load custom post types and taxonomies.
	'/block-editor.php',                    // Load Block Editor functions.
	'/importer.php',                    //importer stuff
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once get_theme_file_path( $understrap_inc_dir . $file );
}

//Deal with term names that have "term-" prefix
add_filter('get_the_archive_title', function($title) {
    return preg_replace('/term-/', '', $title);
});


//extra widget for recent posts
class Recent_Cases_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'recent_cases_widget',
            'Recent Cases',
            ['description' => 'Displays the most recent Cases posts.']
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] ?? 'Recent Cases' );
        $count = $instance['number'] ?? 5;

        $query = new WP_Query([
            'post_type'      => 'case',
            'posts_per_page' => (int) $count,
			'orderby'   => 'modified',
    		'order'     => 'DESC',
            'post_status'    => 'publish',
        ]);

        echo $args['before_widget'];

        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }

        if ( $query->have_posts() ) {
            echo '<ul>';
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
            wp_reset_postdata();
        } else {
            echo '<p>No cases found.</p>';
        }

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title  = esc_attr( $instance['title'] ?? 'Recent Cases' );
        $number = (int) ( $instance['number'] ?? 5 );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">Number of posts:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>"
                   name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $number; ?>" min="1" max="20">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        return [
            'title'  => sanitize_text_field( $new_instance['title'] ),
            'number' => (int) $new_instance['number'],
        ];
    }
}

add_action( 'widgets_init', function() {
    register_widget( 'Recent_Cases_Widget' );
});