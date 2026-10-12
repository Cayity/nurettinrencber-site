<?php
/**
 * Konserler bölümü.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$events = rencber_upcoming_events( max( 1, (int) rencber_mod( 'events_limit' ) ) );
?>
<section class="section section-events" id="konserler">
	<div class="wrap">
		<?php rencber_section_head( __( 'Takvim', 'rencber' ), rencber_mod( 'title_events' ), get_post_type_archive_link( 'etkinlik' ), __( 'Tüm konserler', 'rencber' ) ); ?>

		<?php if ( $events ) : ?>
			<ol class="event-list">
				<?php
				foreach ( $events as $ev ) {
					get_template_part( 'parts/event-row', null, array( 'post' => $ev ) );
				}
				?>
			</ol>
			<?php // Sayfa önbellekten gelip tüm konserler geçmişte kalırsa main.js bunu gösterir. ?>
			<p class="empty-note" data-events-empty hidden><?php echo esc_html( rencber_mod( 'events_empty' ) ); ?></p>
		<?php else : ?>
			<p class="empty-note"><?php echo esc_html( rencber_mod( 'events_empty' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
