<?php
/**
 * Navbar branding
 *
 * @package Understrap
 * @since 1.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! has_custom_logo() ) { ?>

	<?php if ( is_front_page() && is_home() ) : ?>

		<a rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="url">
			<img src="<?php echo get_template_directory_uri(); ?>/imgs/vv-logo-white.svg" class="img-fluid" id="header-logo" alt="<?php bloginfo( 'name' ); ?>"/>
		</a>

	<?php else : ?>

		<a rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="url">
			<img src="<?php echo get_template_directory_uri(); ?>/imgs/vv-logo-white.svg" class="img-fluid"  id="header-logo" alt="<?php bloginfo( 'name' ); ?>"/>
		</a>
		<div class="tagline">
			<?php bloginfo( 'description' ); ?>
		</div>

	<?php endif; ?>

	<?php
} else {
	the_custom_logo();
}
