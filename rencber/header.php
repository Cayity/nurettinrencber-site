<?php
/**
 * Site başlığı.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<!-- iOS metindeki tarih/adres/telefonları kendiliğinden link yapmasın (tel: ve mailto: linkleri elle verildi). -->
	<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
	<meta name="theme-color" content="#0f0d0b">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#icerik"><?php esc_html_e( 'İçeriğe geç', 'rencber' ); ?></a>

<header class="site-header" data-header>
	<div class="wrap header-inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			$logo_id = get_theme_mod( 'custom_logo' );
			if ( $logo_id ) {
				echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'brand-logo', 'alt' => rencber_mod( 'artist_name' ) ) );
			} else {
				$name  = trim( (string) rencber_mod( 'artist_name' ) );
				$parts = explode( ' ', $name );
				$last  = array_pop( $parts );
				echo '<span class="brand-text">' . esc_html( implode( ' ', $parts ) ) . ' <b>' . esc_html( $last ) . '</b></span>';
			}
			?>
		</a>

		<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Ana menü', 'rencber' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				) );
			} else {
				rencber_default_menu();
			}
			?>
			<div class="nav-platforms">
				<?php // Doldurulmuş tüm platformlar: Spotify, Apple Music, YouTube… + Instagram, Facebook (boş olanlar görünmez). ?>
				<?php foreach ( rencber_platforms( 'all' ) as $p ) : ?>
					<a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener me" aria-label="<?php echo esc_attr( $p['label'] ); ?>"><?php echo rencber_icon( $p['icon'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php endforeach; ?>
			</div>
		</nav>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Menü', 'rencber' ); ?></span>
			<span class="nav-toggle-bar"></span>
		</button>
	</div>
</header>

<main id="icerik">
