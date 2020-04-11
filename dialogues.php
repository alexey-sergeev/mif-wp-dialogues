<?php
/*
Plugin Name: MIF WP Dialogues
Plugin URI: http://edu.vspu.ru
Description: Плагин для сайта учебных диалогов.
Author: Алексей Н. Сергеев
Version: 0.9
Author URI: https://vk.com/alexey_sergeev
*/

defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/inc/dg-init.php';
include_once dirname( __FILE__ ) . '/inc/dg-templates.php';


global $mif_dg;
$mif_dg = new mif_dg_init();



add_action( 'wp_enqueue_scripts', 'mif_dg_styles' );

function mif_dg_styles() 
{
    // Twitter bootstrap
    
    wp_register_style( 'bootstrap', plugins_url( 'lib/bootstrap/css/bootstrap.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap' );
    // wp_enqueue_script( 'bootstrap', plugins_url( 'lib/bootstrap/js/bootstrap.min.js', __FILE__ ) );


    // Локальные стили и скрипты

    wp_enqueue_style( 'mif-dg-styles', plugins_url( 'mif-dg-styles.css', __FILE__ ) );
    wp_enqueue_script( 'mif-dg-js-helper', plugins_url( 'js/dg-helper.js', __FILE__ ) );



}






?>