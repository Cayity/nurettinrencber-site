<?php
/**
 * Site alt bilgisi.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<div class="wrap footer-inner">
		<p class="footer-name"><?php echo esc_html( rencber_mod( 'artist_name' ) ); ?></p>

		<ul class="footer-platforms">
			<?php foreach ( rencber_platforms() as $p ) : ?>
				<li><a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener me"><?php echo rencber_icon( $p['icon'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $p['label'] ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<div class="footer-bottom">
			<p><?php echo esc_html( rencber_footer_text() ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => 'nav',
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
				) );
			}
			?>
		</div>
	</div>
</footer>

<button class="to-top" type="button" data-to-top aria-label="<?php esc_attr_e( 'Sayfanın başına dön', 'rencber' ); ?>">
	<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 5.2 4.6 12.6l1.4 1.4L11 9V20h2V9l5 5 1.4-1.4z"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
