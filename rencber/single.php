<?php
/**
 * Tek yazı.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'parts/page-head' );
	?>
	<div class="section">
		<article class="wrap narrow prose">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'rencber-wide', array( 'class' => 'page-image' ) );
			}
			the_content();
			?>
		</article>
	</div>
	<?php
endwhile;

get_footer();
