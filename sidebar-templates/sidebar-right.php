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
		<img src="<?php echo get_template_directory_uri();?>/imgs/ur-text.svg" class="img-fluid ur-logo" alt="University of Richmond.">
		<a href="https://law.richmond.edu/">
			<img src="<?php echo get_template_directory_uri();?>/imgs/law-logo.svg" class="img-fluid logo" alt="UR Law School logo.">
		</a>
<?php endif; ?>
<?php dynamic_sidebar( 'right-sidebar' ); ?>

</div><!-- #right-sidebar -->
