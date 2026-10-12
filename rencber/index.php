<?php
/**
 * Yedek şablon (yazılar, arama, arşiv).
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: arama terimi */
	$title = sprintf( __( '“%s” için sonuçlar', 'rencber' ), get_search_query() );
} elseif ( is_archive() ) {
	$title = wp_strip_all_tags( get_the_archive_title() );
} else {
	$title = is_home() && get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Haberler', 'rencber' );
}
get_template_part( 'parts/page-head', null, array( 'title' => $title ) );
?>
<div class="section">
	<div class="wrap narrow">
		<?php if ( have_posts() ) : ?>
			<ul class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li>
						<a href="<?php the_permalink(); ?>">
							<span class="post-date"><?php echo esc_html( get_the_date() ); ?></span>
							<span class="post-title"><?php the_title(); ?></span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Burada henüz bir şey yok.', 'rencber' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
