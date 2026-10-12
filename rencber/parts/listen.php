<?php
/**
 * Dinle bölümü: platform butonları + Spotify oynatıcısı.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$music      = rencber_platforms( 'music' );
$spotify_id = rencber_mod( 'spotify_embed' ) ? rencber_spotify_artist_id( rencber_mod( 'url_spotify' ) ) : '';
?>
<section class="section section-listen" id="dinle">
	<div class="wrap listen-grid<?php echo $spotify_id ? '' : ' no-embed'; ?>">
		<div class="listen-copy">
			<?php rencber_section_head( __( 'Dijital platformlar', 'rencber' ), rencber_mod( 'title_listen' ) ); ?>
			<?php if ( rencber_mod( 'listen_text' ) ) : ?>
				<p class="lead"><?php echo wp_kses_post( rencber_mod( 'listen_text' ) ); ?></p>
			<?php endif; ?>
			<ul class="platform-list">
				<?php foreach ( $music as $key => $p ) : ?>
					<li>
						<a class="platform platform-<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener">
							<?php echo rencber_icon( $p['icon'], 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="platform-name"><?php echo esc_html( $p['label'] ); ?></span>
							<span class="platform-go"><?php echo 'youtube' === $key ? esc_html__( 'İzle', 'rencber' ) : esc_html__( 'Dinle', 'rencber' ); ?> <?php echo rencber_icon( 'arrow', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( $spotify_id ) : ?>
			<div class="listen-embed">
				<iframe title="<?php esc_attr_e( 'Spotify oynatıcısı', 'rencber' ); ?>" src="https://open.spotify.com/embed/artist/<?php echo esc_attr( $spotify_id ); ?>?utm_source=generator&amp;theme=0" width="100%" height="460" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
			</div>
		<?php endif; ?>
	</div>
</section>
