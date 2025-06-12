<?php
/**
 * Single case partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">
			<?php //understrap_posted_on(); ?>

		</div><!-- .entry-meta -->

	</header><!-- .entry-header -->


	<div class="entry-content">

		<?php
		//the_content();
			echo ur_law_basics_table();
			echo url_law_case_citation();		
			echo ur_law_holding('holding', 'h2');
			echo ur_law_basic_html('procedural_postures', 'h3');
			echo ur_law_briefs_repeater();
			echo ur_law_coverage_repeater();	
			echo ur_law_audio();	
			//understrap_link_pages();
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
