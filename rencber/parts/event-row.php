<?php
/**
 * Tek konser satırı. $args['post'] gerekir.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$e        = rencber_event( $args['post'] );
$d        = rencber_date_parts( $e['ts'] );
$statuses = rencber_event_statuses();
$off      = in_array( $e['status'], array( 'EventCancelled', 'EventPostponed' ), true );
?>
<li class="event<?php echo $off ? ' is-off' : ''; ?>">
	<time class="event-date" datetime="<?php echo esc_attr( $e['date'] . ( $e['time'] ? 'T' . $e['time'] : '' ) ); ?>">
		<span class="event-month"><?php echo esc_html( $d['month'] ); ?></span>
		<span class="event-day"><?php echo esc_html( $d['day'] ); ?></span>
	</time>
	<div class="event-info">
		<h3 class="event-city"><a href="<?php echo esc_url( $e['url'] ); ?>"><?php echo esc_html( $e['city'] ); ?></a></h3>
		<p class="event-meta">
			<span><?php echo esc_html( $d['short'] . ( $e['time'] ? ' ' . $e['time'] : '' ) ); ?></span>
			<span><?php echo esc_html( $e['venue'] ); ?></span>
			<?php if ( 'EventScheduled' !== $e['status'] ) : ?>
				<span class="event-status"><?php echo esc_html( $statuses[ $e['status'] ] ); ?></span>
			<?php endif; ?>
		</p>
	</div>
	<div class="event-buy">
		<?php if ( $e['soldout'] ) : ?>
			<span class="event-price is-soldout"><?php esc_html_e( 'Tükendi', 'rencber' ); ?></span>
		<?php elseif ( '' !== $e['price'] ) : ?>
			<span class="event-price"><b><?php echo esc_html( rencber_price( $e['price'], $e['currency'] ) ); ?></b> <?php esc_html_e( 'ile başlayan', 'rencber' ); ?></span>
		<?php endif; ?>
		<?php if ( $e['ticket_url'] && ! $e['soldout'] && ! $off ) : ?>
			<a class="btn btn-solid btn-sm" href="<?php echo esc_url( $e['ticket_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Bilet al', 'rencber' ); ?></a>
		<?php else : ?>
			<a class="btn btn-line btn-sm" href="<?php echo esc_url( $e['url'] ); ?>"><?php esc_html_e( 'Detaylar', 'rencber' ); ?></a>
		<?php endif; ?>
	</div>
</li>
