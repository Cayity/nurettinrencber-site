<?php
/**
 * Diskografi bölümü: albümler, single'lar ve Home Concert kayıtları.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$albums  = rencber_albums( 'album' );
$singles = rencber_albums( 'single' );
$homes   = rencber_albums( 'home' );
if ( ! $albums && ! $singles && ! $homes ) {
	return;
}
$heading = isset( $args['heading'] ) ? $args['heading'] : true;
$total   = count( $albums ) + count( $singles ) + count( $homes );

/**
 * Kapaklı kart.
 */
$card = function ( $a ) {
	$cover  = rencber_album_cover( $a->ID );
	$credit = get_post_meta( $a->ID, '_al_credit', true );
	?>
	<li class="album">
		<a href="<?php echo esc_url( get_permalink( $a ) ); ?>">
			<span class="album-cover">
				<?php if ( $cover ) : ?>
					<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $a ) . ' kapak' ); ?>" loading="lazy" decoding="async" width="600" height="600">
				<?php endif; ?>
			</span>
			<span class="album-year"><?php echo esc_html( get_post_meta( $a->ID, '_al_year', true ) ); ?></span>
			<span class="album-title"><?php echo esc_html( get_the_title( $a ) ); ?></span>
			<?php if ( $credit ) : ?>
				<span class="album-credit"><?php echo esc_html( sprintf( /* translators: %s: sanatçı */ __( '%s ile', 'rencber' ), $credit ) ); ?></span>
			<?php endif; ?>
		</a>
	</li>
	<?php
};
?>
<section class="section section-albums" id="albumler">
	<div class="wrap">
		<?php
		if ( $heading ) {
			/* translators: %d: kayıt sayısı */
			rencber_section_head( sprintf( __( 'Diskografi · %d yayın', 'rencber' ), $total ), rencber_mod( 'title_albums' ) );
		}
		?>

		<?php if ( $singles ) : ?>
			<div class="disco-group">
				<h3 class="disco-title"><?php esc_html_e( 'Single’lar', 'rencber' ); ?> <span><?php echo (int) count( $singles ); ?></span></h3>
				<ul class="album-grid is-singles">
					<?php array_map( $card, $singles ); ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $albums ) : ?>
			<div class="disco-group">
				<h3 class="disco-title"><?php esc_html_e( 'Stüdyo albümleri', 'rencber' ); ?> <span><?php echo (int) count( $albums ); ?></span></h3>
				<ul class="album-grid">
					<?php array_map( $card, $albums ); ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $homes ) : ?>
			<div class="disco-group">
				<h3 class="disco-title"><?php esc_html_e( 'Home Concert serisi', 'rencber' ); ?> <span><?php echo (int) count( $homes ); ?></span></h3>
				<ul class="home-list">
					<?php foreach ( $homes as $h ) : ?>
						<?php
						$cover = rencber_album_cover( $h->ID, 'thumbnail' );
						$name  = trim( preg_replace( '/\s*\(Home Concert\)\s*/i', ' ', get_the_title( $h ) ) );
						?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $h ) ); ?>">
								<?php if ( $cover ) : ?>
									<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy" decoding="async" width="56" height="56">
								<?php endif; ?>
								<span class="home-name"><?php echo esc_html( $name ); ?></span>
								<span class="home-year"><?php echo esc_html( get_the_date( 'M Y', $h ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
