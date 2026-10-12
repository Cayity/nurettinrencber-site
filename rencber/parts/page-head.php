<?php
/**
 * İç sayfa başlık bandı. $args: title, eyebrow, text.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args, array( 'title' => get_the_title(), 'eyebrow' => '', 'text' => '' ) );
?>
<header class="page-head">
	<div class="wrap">
		<?php if ( $args['eyebrow'] ) : ?>
			<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
		<?php endif; ?>
		<h1 class="page-title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['text'] ) : ?>
			<p class="page-lead"><?php echo esc_html( $args['text'] ); ?></p>
		<?php endif; ?>
	</div>
</header>
