<?php
/**
 * /albumler/ — diskografi.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'parts/page-head', null, array( 'eyebrow' => __( 'Diskografi', 'rencber' ), 'title' => rencber_mod( 'title_albums' ) ) );
get_template_part( 'parts/albums', null, array( 'heading' => false ) );
get_footer();
