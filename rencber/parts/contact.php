<?php
/**
 * İletişim bölümü.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$phone   = rencber_mod( 'manager_phone' );
$email   = rencber_mod( 'manager_email' );
$form    = trim( (string) rencber_mod( 'contact_form' ) );
$heading = isset( $args['heading'] ) ? $args['heading'] : true;
?>
<section class="section section-contact" id="iletisim">
	<div class="wrap contact-grid<?php echo $form ? '' : ' no-form'; ?>">
		<div class="contact-copy">
			<?php
			if ( $heading ) {
				rencber_section_head( __( 'Konser ve organizasyon', 'rencber' ), rencber_mod( 'title_contact' ) );
			}
			?>
			<?php if ( rencber_mod( 'contact_text' ) ) : ?>
				<p class="lead"><?php echo wp_kses_post( rencber_mod( 'contact_text' ) ); ?></p>
			<?php endif; ?>

			<div class="contact-card">
				<?php if ( rencber_mod( 'manager_name' ) ) : ?>
					<p class="contact-role"><?php esc_html_e( 'Menajer', 'rencber' ); ?></p>
					<p class="contact-name"><?php echo esc_html( rencber_mod( 'manager_name' ) ); ?></p>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a class="contact-line" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo rencber_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a class="contact-line" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo rencber_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( antispambot( $email ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $form ) : ?>
			<div class="contact-form">
				<?php echo do_shortcode( $form ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
	</div>
</section>
