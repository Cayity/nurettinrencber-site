<?php
/**
 * Bulunamadı.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'parts/page-head', null, array(
	'eyebrow' => '404',
	'title'   => __( 'Bu sayfa bulunamadı', 'rencber' ),
	'text'    => __( 'Aradığınız sayfa taşınmış ya da kaldırılmış olabilir.', 'rencber' ),
) );
?>
<div class="section">
	<div class="wrap narrow">
		<a class="btn btn-solid" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana sayfaya dön', 'rencber' ); ?></a>
	</div>
</div>
<?php
get_footer();
