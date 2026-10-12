<?php
/**
 * /konserler/ — gelecek konserler (?gecmis ile geçmiş konserler).
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$past = isset( $_GET['gecmis'] ); // phpcs:ignore WordPress.Security.NonceVerification

get_header();
get_template_part( 'parts/page-head', null, array(
	'eyebrow' => $past ? __( 'Arşiv', 'rencber' ) : __( 'Takvim', 'rencber' ),
	'title'   => $past ? __( 'Geçmiş konserler', 'rencber' ) : rencber_mod( 'title_events' ),
) );
?>
<section class="section section-events">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<ol class="event-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/event-row', null, array( 'post' => get_post() ) );
				endwhile;
				?>
			</ol>
		<?php else : ?>
			<p class="empty-note"><?php echo esc_html( $past ? __( 'Kayıtlı geçmiş konser yok.', 'rencber' ) : rencber_mod( 'events_empty' ) ); ?></p>
		<?php endif; ?>

		<p class="archive-switch">
			<?php if ( $past ) : ?>
				<a class="section-link" href="<?php echo esc_url( get_post_type_archive_link( 'etkinlik' ) ); ?>"><?php esc_html_e( 'Yaklaşan konserler', 'rencber' ); ?> <?php echo rencber_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php else : ?>
				<a class="section-link" href="<?php echo esc_url( add_query_arg( 'gecmis', '1', get_post_type_archive_link( 'etkinlik' ) ) ); ?>" rel="nofollow"><?php esc_html_e( 'Geçmiş konserler', 'rencber' ); ?> <?php echo rencber_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
		</p>
	</div>
</section>
<?php
get_footer();
