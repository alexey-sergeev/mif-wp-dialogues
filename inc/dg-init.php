<?php

//
// Инит-файл плагина Dialogues
// 
//

defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/dg-core.php';
include_once dirname( __FILE__ ) . '/dg-rating-panel.php';
include_once dirname( __FILE__ ) . '/dg-control-panel.php';
include_once dirname( __FILE__ ) . '/dg-download.php';



class mif_dg_init extends mif_dg_control_panel { 

    function __construct()
    {
        parent::__construct();

        add_filter( 'comments_template', array( $this, 'add_control_panel' ) );
        add_filter( 'comment_text', array( $this, 'add_rating_panel' ), 2, 2 );

        add_action( 'wp_insert_post', array( $this, 'update_settings' ), 10, 3 );
        // add_action( 'save_post', array( $this, 'update_settings' ), 10, 3 );

        add_action( 'wp_ajax_mark', array( $this, 'ajax_submit' ) );
        add_action( 'wp_ajax_refresh_rl', array( $this, 'ajax_submit' ) );
        add_action( 'wp_ajax_settings_edit', array( $this, 'ajax_submit' ) );
        add_action( 'wp_ajax_settings_save', array( $this, 'ajax_submit' ) );
        
        // add_action( 'init', array( $this, 'force_download' ) );
        $mif_dg_download = new mif_dg_download();

    }


    // 
    // Точка входа для AJAX-запросов
    // 

    public function ajax_submit()
    {
        check_ajax_referer( 'mif-dg' );

        $post_id = (int) $_REQUEST['post_id'];

        if ( $_REQUEST['action'] == 'mark' ) {

            // Новая оценка

            $comment_id = (int) $_REQUEST['comment_id'];
            $rating = (int) $_REQUEST['rating'];
            $user_id = wp_get_current_user()->ID;

            $this->update_rating( $comment_id, $rating, $user_id );
            
            $panel = $this->get_rating_panel( $comment_id );

            echo $panel;
            // p(time());
            // p($user_id);
            // p($_REQUEST);


        } elseif ( $_REQUEST['action'] == 'refresh_rl' ) {

            echo $this->get_result_list( $post_id );

        } elseif ( $_REQUEST['action'] == 'settings_edit' ) {

            echo $this->get_settings_edit_tab( $post_id );
            
        } elseif ( $_REQUEST['action'] == 'settings_save' ) {
            
            $this->update_settings( $post_id );
            echo $this->get_settings_tab( $post_id );

        }

        // $this->add_quiz_content();
        wp_die();
    }


}




?>