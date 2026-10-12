<?php
/**
 * Tek albüm sayfası.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id     = get_the_ID();
	$year   = get_post_meta( $id, '_al_year', true );
	$label  = get_post_meta( $id, '_al_label', true );
	$tracks = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $id, '_al_tracks', true ) ) ) );
	$links  = array();
	foreach ( array( 'spotify' => 'Spotify', 'apple' => 'Apple Music', 'youtube' => 'YouTube' ) as $k => $name ) {
		$u = get_post_meta( $id, '_al_' . $k, true );
		if ( $u ) {
			$links[ $k ] = array( $name, $u );
		}
	}
	// Albüme özel bağlantı yoksa sanatçı profillerine yönlendir.
	if ( ! $links ) {
		foreach ( rencber_platforms( 'music' ) as $p ) {
			$links[ $p['icon'] ] = array( $p['label'], $p['url'] );
		}
	}
	$cover = rencber_album_cover( $id, 'large' );
	$types  = rencber_release_types();
	$type   = get_post_meta( $id, '_al_type', true );
	$type   = isset( $types[ $type ] ) ? $type : 'album';
	$credit = get_post_meta( $id, '_al_credit', true );
	$meta   = array_filter( array( $types[ $type ], $year, $label ) );
	?>
	<div class="section album-single-wrap">
		<div class="wrap album-single">
			<figure class="album-single-cover">
				<?php if ( $cover ) : ?>
					<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title() . ' albüm kapağı' ); ?>" width="600" height="600">
				<?php endif; ?>
			</figure>
			<div class="album-single-info">
				<p class="eyebrow"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<?php if ( $credit ) : ?>
					<p class="album-credit is-large"><?php echo esc_html( rencber_mod( 'artist_name' ) . ' & ' . $credit ); ?></p>
				<?php endif; ?>
				<ul class="platform-pills">
					<?php foreach ( $links as $icon => $l ) : ?>
						<li><a href="<?php echo esc_url( $l[1] ); ?>" target="_blank" rel="noopener"><?php echo rencber_icon( $icon, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $l[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $tracks ) : ?>
					<ol class="tracklist">
						<?php foreach ( $tracks as $t ) : ?>
							<li><?php echo esc_html( $t ); ?></li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
				<div class="prose"><?php the_content(); ?></div>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_template_part( 'parts/albums' );
get_footer();
