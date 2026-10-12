<?php
/**
 * Galeri bölümü + ışık kutusu.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$ids = rencber_gallery_ids();
if ( ! $ids ) {
	return;
}
$heading = isset( $args['heading'] ) ? $args['heading'] : true;
$limit   = isset( $args['limit'] ) ? (int) $args['limit'] : 12;
$shown   = $limit > 0 ? array_slice( $ids, 0, $limit ) : $ids;
?>
<section class="section section-gallery" id="galeri">
	<div class="wrap">
		<?php
		if ( $heading ) {
			$page = get_page_by_path( 'galeri' );
			rencber_section_head(
				__( 'Fotoğraflar', 'rencber' ),
				rencber_mod( 'title_gallery' ),
				( $page && count( $ids ) > count( $shown ) ) ? get_permalink( $page ) : '',
				__( 'Tümünü gör', 'rencber' )
			);
		}
		?>
		<ul class="gallery-grid" data-gallery>
			<?php foreach ( $shown as $id ) : ?>
				<?php
				$full = wp_get_attachment_image_url( $id, 'full' );
				if ( ! $full ) {
					continue;
				}
				?>
				<li>
					<a href="<?php echo esc_url( $full ); ?>" data-lightbox>
						<?php echo wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
