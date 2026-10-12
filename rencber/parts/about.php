<?php
/**
 * Hakkında bölümü.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$img     = rencber_mod( 'about_image' );
$img_url = rencber_image_url( $img, 'rencber-wide' );
$heading = isset( $args['heading'] ) ? $args['heading'] : true;
?>
<section class="section section-about" id="hakkinda">
	<div class="wrap about-grid<?php echo $img_url ? '' : ' no-image'; ?>">
		<?php if ( $img_url ) : ?>
			<figure class="about-media">
				<?php
				if ( is_numeric( $img ) ) {
					echo wp_get_attachment_image( (int) $img, 'rencber-wide', false, array( 'loading' => 'lazy', 'alt' => rencber_mod( 'artist_name' ) ) );
				} else {
					echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( rencber_mod( 'artist_name' ) ) . '" loading="lazy">';
				}
				?>
			</figure>
		<?php endif; ?>
		<div class="about-copy">
			<?php
			if ( $heading ) {
				rencber_section_head( __( 'Biyografi', 'rencber' ), rencber_mod( 'title_about' ) );
			}
			?>
			<?php if ( rencber_mod( 'about_quote' ) ) : ?>
				<blockquote class="about-quote"><?php echo wp_kses_post( rencber_mod( 'about_quote' ) ); ?></blockquote>
			<?php endif; ?>
			<div class="prose"><?php echo wp_kses_post( wpautop( rencber_mod( 'about_text' ) ) ); ?></div>
		</div>
	</div>
</section>
