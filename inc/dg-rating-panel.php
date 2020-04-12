<?php

//
// Методы выставления оценок в комментариях
// 
//

defined( 'ABSPATH' ) || exit;


class mif_dg_rating_panel extends mif_dg_core { 

    function __construct()
    {
        parent::__construct();
    }





    // 
    // Добавить панель выставления рейтинга для комментария
    // 

    public function add_rating_panel( $comment_text, $comment )
    {
        $comment_id = $comment->comment_ID;
        
        $panel = $this->get_rating_panel( $comment_id );

        return $panel . $comment_text;
    }


    // 
    // Сформировать панель выставления рейтинга
    // 

    protected function get_rating_panel( $comment_id )
    {
        $comment = get_comment( $comment_id );
        $post_id = $comment->comment_post_ID;
        $access_level = $this->access_level( $post_id );

        if ( $access_level < 2 ) return false;
        
        // if ( $access_level_author != 2 ) return false;

        $panel = '';
        
        $s = $this->get_settings( $post_id );
        $max = $s['max_rating'];
        
        $data = $this->get_rating_data( $comment_id );
        // p($data);
        $rating = ( isset( $data['rating'] ) ) ? (int) $data['rating'] : 0;
        $timestamp = ( isset( $data['timestamp'] ) ) ? $this->get_time( $data['timestamp'] ) : $this->get_time();
        
        $nonce = wp_create_nonce( 'mif-dg' );
        
        $panel .= '<div class="rating">';
        
        $access_level_author = $this->access_level( $post_id, $comment->user_id );

        if ( $access_level_author == 2 ) {

            $panel .= '<span class="rating" data-rating="' . $rating . '" data-select="0" data-comment="' . $comment_id . '">';
            
            for ( $i = 1; $i <= $max; $i++ ) {

                if ( $rating >= $i ) {

                    $type = 'on';
                    $star = '<i class="fa fa-star" aria-hidden="true"></i>';
                    
                } else {
                    
                    $type = 'off';
                    $star = '<i class="fa fa-star-o" aria-hidden="true"></i>';

                }

                if ( $access_level < 3 ) {

                    $panel .= '<span class="star ' . $type . '">';
                    $panel .= $star;
                    $panel .= '</span>';

                } else {

                    $panel .= '<a href="" class="star ' . $type . '" data-point="' . $i . '" data-nonce="' . $nonce . '">';
                    $panel .= $star;
                    $panel .= '</a>';

                }

            }
            
            $panel .= '</span>';

            if ( isset( $data['user'] ) ) {
                
                $panel .= '<span class="comment">';
                $panel .= '<span class="result">' . $rating . ' из ' . $max . '</span>';
                $panel .= $this->get_avatar( $data['user'] ); 
                $panel .= '<span class="timestamp">' . $timestamp . '</span>'; 
                $panel .= '</span>';
            
            } else {

                $panel .= '<span class="comment">';
                $panel .= '<span class="timestamp norating">Ожидает проверки</span>'; 
                $panel .= '</span>';

            }
        
        } else {

            $panel .= '<span class="comment">';
            $panel .= '<span class="timestamp free">Не требует оценки</span>'; 
            $panel .= '</span>';

        }

        $panel .= '</div>';

        return $panel;
    }



    // 
    // Вывести аватар
    // 
    
    protected function get_avatar( $user_id, $size = 25 )
    {
        $out = '';
        $user = get_user_by( 'id', $user_id );

        $out .= '<span class="user-avatar"><a href="' . bp_core_get_user_domain( $user->ID ) . '" title="' . $user->display_name . '">' . get_avatar( $user->ID, $size ) . '</a></span>';

        return $out;
    }



}




?>