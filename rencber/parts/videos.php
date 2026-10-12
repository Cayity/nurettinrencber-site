<?php
/**
 * Videolar bölümü: hafif YouTube gömme (tıklayınca yüklenir).
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$videos = rencber_videos();
if ( ! $videos ) {
	return;
}
?>
<section class="section section-videos" id="videolar">
	<div class="wrap">
		<?php rencber_section_head( __( 'YouTube', 'rencber' ), rencber_mod( 'title_videos' ), rencber_mod( 'url_youtube' ), __( 'Kanala abone ol', 'rencber' ) ); ?>
		<ul class="video-grid">
			<?php foreach ( $videos as $i => $v ) : ?>
				<?php
				$label = $v['title'] ? $v['title'] : __( 'Videoyu oynat', 'rencber' );
				$thumb = 'https://i.ytimg.com/vi/' . $v['id'] . '/' . ( 0 === $i ? 'maxresdefault' : 'hqdefault' ) . '.jpg';
				?>
				<li class="video<?php echo 0 === $i ? ' is-featured' : ''; ?>">
					<button class="video-lite" type="button" data-yt-lite="<?php echo esc_attr( $v['id'] ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" decoding="async">
						<span class="video-play"><?php echo rencber_icon( 'play', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</button>
					<?php if ( $v['title'] ) : ?>
						<p class="video-title"><?php echo esc_html( preg_replace( '/^' . preg_quote( rencber_mod( 'artist_name' ), '/' ) . '\s*-\s*/u', '', $v['title'] ) ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
