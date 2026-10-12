<?php
/**
 * Tek konser sayfası (Google Etkinlikler bu sayfayı okur).
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$e        = rencber_event( get_the_ID() );
	$d        = $e['ts'] ? rencber_date_parts( $e['ts'] ) : null;
	$statuses = rencber_event_statuses();
	$past     = $e['date'] && $e['date'] < wp_date( 'Y-m-d' );
	$map      = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $e['venue'] . ' ' . $e['address'] . ' ' . $e['city'] ) );

	get_template_part( 'parts/page-head', null, array(
		'eyebrow' => $d ? $d['long'] : '',
		'title'   => get_the_title(),
	) );
	?>
	<div class="section">
		<div class="wrap event-single">
			<div class="event-card">
				<?php if ( $d ) : ?>
					<time class="event-date is-large" datetime="<?php echo esc_attr( $e['date'] ); ?>">
						<span class="event-month"><?php echo esc_html( $d['month'] ); ?></span>
						<span class="event-day"><?php echo esc_html( $d['day'] ); ?></span>
					</time>
				<?php endif; ?>
				<dl class="event-facts">
					<?php if ( $d ) : ?>
						<div><dt><?php esc_html_e( 'Tarih', 'rencber' ); ?></dt><dd><?php echo esc_html( $d['long'] . ( $e['time'] ? ' · ' . $e['time'] : '' ) ); ?></dd></div>
					<?php endif; ?>
					<div><dt><?php esc_html_e( 'Mekân', 'rencber' ); ?></dt><dd><?php echo esc_html( $e['venue'] ); ?><?php echo $e['address'] ? '<br><small>' . esc_html( $e['address'] ) . '</small>' : ''; ?></dd></div>
					<div><dt><?php esc_html_e( 'Şehir', 'rencber' ); ?></dt><dd><?php echo esc_html( $e['city'] ); ?></dd></div>
					<?php if ( '' !== $e['price'] || $e['soldout'] ) : ?>
						<div><dt><?php esc_html_e( 'Bilet', 'rencber' ); ?></dt><dd><?php echo $e['soldout'] ? esc_html__( 'Tükendi', 'rencber' ) : esc_html( rencber_price( $e['price'], $e['currency'] ) . ' ' . __( 'ile başlayan fiyatlar', 'rencber' ) ); ?><?php echo $e['vendor'] ? ' · ' . esc_html( $e['vendor'] ) : ''; ?></dd></div>
					<?php endif; ?>
					<?php if ( 'EventScheduled' !== $e['status'] ) : ?>
						<div><dt><?php esc_html_e( 'Durum', 'rencber' ); ?></dt><dd class="event-status"><?php echo esc_html( $statuses[ $e['status'] ] ); ?></dd></div>
					<?php endif; ?>
				</dl>
				<div class="event-actions">
					<?php if ( $past ) : ?>
						<p class="empty-note"><?php esc_html_e( 'Bu konser gerçekleşti.', 'rencber' ); ?></p>
					<?php elseif ( $e['ticket_url'] && ! $e['soldout'] && 'EventCancelled' !== $e['status'] ) : ?>
						<a class="btn btn-solid" href="<?php echo esc_url( $e['ticket_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Bilet al', 'rencber' ); ?></a>
					<?php endif; ?>
					<a class="btn btn-line" href="<?php echo esc_url( $map ); ?>" target="_blank" rel="noopener"><?php echo rencber_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Yol tarifi', 'rencber' ); ?></a>
				</div>
			</div>

			<div class="prose">
				<?php the_content(); ?>
				<p><a class="section-link" href="<?php echo esc_url( get_post_type_archive_link( 'etkinlik' ) ); ?>"><?php esc_html_e( 'Tüm konserler', 'rencber' ); ?> <?php echo rencber_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
