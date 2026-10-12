<?php
/**
 * Açılış: tam ekran arka plan videosu.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$type   = rencber_mod( 'hero_video_type' );
$yt_id  = rencber_youtube_id( rencber_mod( 'hero_youtube' ) );
$mp4    = rencber_image_url( rencber_mod( 'hero_mp4' ), 'full' );
$poster = rencber_image_url( rencber_mod( 'hero_poster' ), 'full' );
if ( ! $poster && $yt_id ) {
	$poster = 'https://i.ytimg.com/vi/' . $yt_id . '/maxresdefault.jpg';
}
if ( 'mp4' === $type && ! $mp4 ) {
	$type = $yt_id ? 'youtube' : 'image';
}
if ( 'youtube' === $type && ! $yt_id ) {
	$type = 'image';
}
$overlay = max( 0, min( 90, (int) rencber_mod( 'hero_overlay' ) ) ) / 100;
$next    = rencber_upcoming_events( 1 );
$title   = rencber_mod( 'hero_title' );
?>
<section class="hero" data-hero="<?php echo esc_attr( $type ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
	<div class="hero-media">
		<?php if ( $poster && 'image' === $type ) : ?>
			<img class="hero-poster" src="<?php echo esc_url( $poster ); ?>" alt="" fetchpriority="high" decoding="async">
		<?php elseif ( $poster ) : ?>
			<?php // Video varken fotoğraf indirilmez; yalnızca "hareketi azalt" kullanıcıları için yedek. ?>
			<img class="hero-poster" data-src="<?php echo esc_url( $poster ); ?>" alt="" decoding="async">
		<?php endif; ?>

		<?php if ( 'mp4' === $type ) : ?>
			<video class="hero-video" autoplay muted loop playsinline preload="auto" data-hero-video>
				<source src="<?php echo esc_url( $mp4 ); ?>" type="video/mp4">
			</video>
		<?php elseif ( 'youtube' === $type ) : ?>
			<div class="hero-yt" data-yt="<?php echo esc_attr( $yt_id ); ?>"></div>
		<?php endif; ?>
	</div>
	<div class="hero-shade" style="--overlay:<?php echo esc_attr( $overlay ); ?>"></div>

	<div class="wrap hero-content">
		<?php if ( rencber_mod( 'hero_kicker' ) ) : ?>
			<p class="hero-kicker"><?php echo esc_html( rencber_mod( 'hero_kicker' ) ); ?></p>
		<?php endif; ?>
		<h1 class="hero-title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( rencber_mod( 'hero_text' ) ) : ?>
			<p class="hero-text"><?php echo wp_kses_post( rencber_mod( 'hero_text' ) ); ?></p>
		<?php endif; ?>
		<div class="hero-actions">
			<?php if ( rencber_mod( 'hero_btn1_text' ) ) : ?>
				<a class="btn btn-solid" href="<?php echo esc_url( rencber_mod( 'hero_btn1_url' ) ); ?>"><?php echo esc_html( rencber_mod( 'hero_btn1_text' ) ); ?></a>
			<?php endif; ?>
			<?php if ( rencber_mod( 'hero_btn2_text' ) ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( rencber_mod( 'hero_btn2_url' ) ); ?>"><?php echo rencber_icon( 'play', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( rencber_mod( 'hero_btn2_text' ) ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="wrap hero-foot">
		<?php if ( $next ) : ?>
			<?php
			$e = rencber_event( $next[0] );
			$d = rencber_date_parts( $e['ts'] );
			?>
			<a class="hero-next" href="<?php echo esc_url( $e['url'] ); ?>">
				<span class="hero-next-label"><?php esc_html_e( 'Sıradaki konser', 'rencber' ); ?></span>
				<span class="hero-next-body"><b><?php echo esc_html( $d['day'] . ' ' . $d['month'] ); ?></b> · <?php echo esc_html( $e['city'] ); ?> · <?php echo esc_html( $e['venue'] ); ?></span>
			</a>
		<?php else : ?>
			<span></span>
		<?php endif; ?>

		<div class="hero-platforms">
			<?php foreach ( rencber_platforms( 'music' ) as $p ) : ?>
				<a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $p['label'] ); ?>"><?php echo rencber_icon( $p['icon'], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endforeach; ?>
			<?php if ( 'image' !== $type ) : ?>
				<button class="hero-toggle" type="button" data-hero-toggle aria-pressed="false" aria-label="<?php esc_attr_e( 'Arka plan videosunu durdur', 'rencber' ); ?>">
					<?php echo rencber_icon( 'pause', 18 ) . rencber_icon( 'play', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</section>
