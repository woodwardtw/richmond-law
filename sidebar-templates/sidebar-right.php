<?php
/**
 * The right sidebar containing the main widget area
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'right-sidebar' ) ) {
	return;
}

// when both sidebars turned on reduce col size to 3 from 4.
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );
?>

<?php if ( 'both' === $sidebar_pos ) : ?>
	<div class="col-md-3 widget-area" id="right-sidebar">
		
<?php else : ?>
	<div class="col-md-3 widget-area" id="right-sidebar">
		<a class="img-link fluid" href="https://law.richmond.edu/">
			<img src="<?php echo get_template_directory_uri();?>/imgs/2021_school_logo_law.svg" class="img-fluid ur-logo" alt="University of Richmond School of Law.">
		</a>
<?php endif; ?>
<?php dynamic_sidebar( 'right-sidebar' ); ?>

</div><!-- #right-sidebar -->
