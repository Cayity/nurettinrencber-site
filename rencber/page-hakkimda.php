<?php
/**
 * /hakkimda/ sayfası: içerik Özelleştir panelinden gelir, adres eski siteyle aynı kalır.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'parts/page-head', null, array( 'title' => get_the_title() ? get_the_title() : 'Hakkımda' ) );
get_template_part( 'parts/about', null, array( 'heading' => false, 'limit' => 0 ) );
get_footer();
