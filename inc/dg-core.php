<?php

//
// Ядро плагина Dialogues
// 
//

defined( 'ABSPATH' ) || exit;


class mif_dg_core { 

    public $default_method = 'better';
    public $acceptable_methods = array( 'better', 'summa' );
    public $max_rating = 5;


    function __construct()
    {

    }


    // 
    // Записать новую оценку
    // 

    protected function update_rating( $comment_id = 0, $rating = 0, $user_id = 0 )
    {
        if ( ! $comment_id ) return false;

        $comment = get_comment( $comment_id );
        $post_id = $comment->comment_post_ID;
        $author_id = $comment->user_id;

        if ( ! $author_id ) return false;
        if ( $this->access_level( $post_id ) < 3 ) return false;

        $data['rating'] = $rating;
        $data['user'] = $user_id;
        $data['timestamp'] = time();

        // Уточнение при повторном выборе 1 (ставить 0)

        $old_data = $this->get_rating_data( $comment_id );
        if ( isset( $old_data['rating'] ) && $old_data['rating'] == 1 && $data['rating'] == 1 ) $data['rating'] = 0;

        $res = update_comment_meta( $comment_id, 'dg-rating', $data );
        
        // Пересчитать результаты, чтобы они записались в глобальном масштабе

        $this->get_result( $post_id, $author_id );

        return $res;
    }

    

    // 
    // Получить все данные оценки
    // 

    protected function get_rating_data( $comment_id )
    {
        if ( ! $comment_id ) return false;

        $data = get_comment_meta( $comment_id, 'dg-rating', 'true' );

        $comment = get_comment( $comment_id );
        $s = $this->get_settings( $comment->comment_post_ID );

        if ( isset( $data['rating'] ) && (int) $data['rating'] > $s['max_rating'] ) $data['rating'] = $s['max_rating'];

        return $data;
    }

    

    // 
    // Получить только оценкку
    // 

    protected function get_rating( $comment_id )
    {
        $data = $this->get_rating_data( $comment_id );
        $rating = ( isset( $data['rating'] ) ) ? (int) $data['rating'] : 0;
        return $rating;
    }


    //
    // Получить оценки для всех пользователей
    //

    public function get_result( $post_id = NULL, $user_id = false )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $args = array(
            'post_id' => $post_id
        );

        if ( $user_id ) $args['user_id'] = $user_id;

        // Оценки по всем комментариям

        $arr = array();
        $comments = get_comments( $args );
        
        foreach ( $comments as $c ) {

            $user = $c->user_id;

            $access_level = $this->access_level( $post_id, $user );

            if ( $access_level !=2 ) continue;

            $data = $this->get_rating_data( $c->comment_ID );

            $rating = ( isset( $data['rating'] ) ) ? (int) $data['rating'] : 0;
            $marked = ( isset( $data['rating'] ) ) ? true: false;

            if ( $user ) $arr[] = array( 'user' => $user, 'rating' => $rating, 'comment' => $c->comment_ID, 'marked' => $marked );

        }

        // Оценки для пользователей

        $index = array();
        $s = $this->get_settings( $post_id );
        $method = $s['method'];

        foreach ( $arr as $item ) {

            $user = $item['user'];

            if ( ! isset( $index[$user] ) ) $index[$user] = array( 'rating' => 0, 'comments' => array(), 'unmarked' => array() );

            // Здесь проверка способа вычисления оценки.
            
            if ( $method == 'better' ) {
                
                if ( $item['rating'] > $index[$user]['rating'] ) $index[$user]['rating'] = $item['rating'];
                
            } elseif ( $method == 'summa' ) {
                
                $index[$user]['rating'] += $item['rating'];

            }

            $index[$user]['comments'][] = $item['comment'];
            if ( ! $item['marked'] ) $index[$user]['unmarked'][] = $item['comment'];

        }

        // Все данные пользователей

        $result = array();

        foreach ( $index as $user_id => $data ) {

            // $u = get_userdata( $user_id );

            $rating_data = $this->update_user_rating( $user_id, $data['rating'], $post_id );

            $result[$user_id] = array(
                'user_id' => $user_id,
                'nicename' => $this->get_nicename( $user_id ),
                'display_name' => $this->get_username( $user_id ),
                'rating' => $data['rating'],
                'master_id' => $rating_data['user'],
                'master_nicename' => $this->get_nicename( $rating_data['user'] ),
                'master_display_name' => $this->get_username( $rating_data['user'] ),
                'timestamp' => $rating_data['timestamp'],
                'comments' => $data['comments'],
                'unmarked' => $data['unmarked'],
            );
        }

        // p($result);

        return $result;
    }


    
    // 
    // Сохраняет глобальный рейтинг пользователя
    // 

    public function update_user_rating( $user_id, $rating, $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $rating_data = $this->get_user_rating( $user_id, $post_id );

        if ( isset( $rating_data['rating'] ) && $rating_data['rating'] == $rating ) return $rating_data;

        // $site_id = get_current_blog_id();
        // $key = 'mif_rating_' . $site_id . '_' . $post_id;

        $key = $this->get_key( 'rating', $post_id );
        
        $rating_data = array(
            'rating' => $rating,
            'user' => wp_get_current_user()->ID,
            'timestamp' => time(),
            'url' => get_permalink( $post_id )
        );
        
        $ret = update_user_meta( $user_id, $key, $rating_data );

        if ( $ret ) return $rating_data;

        return false;
    }


    
    // 
    // ПОлучает глобальный рейтинг пользователя
    // 

    public function get_user_rating( $user_id, $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;
        
        // $site_id = get_current_blog_id();
        // $key = 'mif_rating_' . $site_id . '_' . $post_id;
        $key = $this->get_key( 'rating', $post_id );

        $rating_data = get_user_meta( $user_id, $key, true );

        // p($rating_data);

        return $rating_data;
    }



    // 
    // Сохраняет настройки старницы
    // 

    public function update_settings( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        if ( $this->access_level( $post_id ) < 3 ) return false;

        $max_rating = ( isset( $_REQUEST['max_rating'] ) ) ? (int) $_REQUEST['max_rating'] : $this->max_rating;
        if ( $max_rating < 1 ) $max_rating = 1;
        if ( $max_rating > 10 ) $max_rating = 10;

        $method = $this->default_method;
        $m = ( isset( $_REQUEST['method'] ) ) ? sanitize_text_field( $_REQUEST['method'] ) : '';
        if ( in_array( $m, $this->acceptable_methods ) ) $method = $m;

        $members = ( isset( $_REQUEST['members'] ) ) ? sanitize_textarea_field( $_REQUEST['members'] ) : '';
        $masters = ( isset( $_REQUEST['masters'] ) ) ? sanitize_textarea_field( $_REQUEST['masters'] ) : '';

        $arr = array( 
            'max_rating' => $max_rating,
            'method' => $method,
            'members' => $members,
            'masters' => $masters,
            'user' => wp_get_current_user()->ID,
            'timestamp' => time(),
        );

        $res = update_post_meta( $post_id, 'dg_settings', $arr );

        wp_cache_delete( 'dg_settings', $post_id );

        // Пересчитать результаты, чтобы они записались в глобальном масштабе

        $this->get_result( $post_id );

        // Обновить глобальные настройки

        $this->set_param( $post_id );

        return $res;
    }



    // 
    // Сохранить глобальные параметры
    // 

    public function set_param( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $p = get_post( $post_id );

        if ( $p->post_status != 'publish' ) return;

        $s = $this->get_settings( $post_id );

        $key = $this->get_key( 'param', $post_id );
        $ret = update_site_option( $key, array( 'max_rating' => $s['max_rating'], 'url' => get_permalink( $post_id ) ) );
        
        return $ret;
    }


    // 
    // Возвращает настройки старницы
    // 

    public function get_settings( $post_id = NULL )
    {

        if ( ! $arr = wp_cache_get( 'dg_settings', $post_id ) ) {

            $meta = get_post_meta( $post_id, 'dg_settings', true );

            // p($meta);

            $arr = array( 
                'max_rating' => $this->max_rating,
                'method' => $this->default_method,
                'members' => '',
                'masters' => '',
                'members_arr' => array(),
                'masters_arr' => array(),
            );

            if ( isset( $meta['max_rating'] ) ) $arr['max_rating'] = $meta['max_rating'];
            if ( isset( $meta['method'] ) && in_array( $meta['method'], $this->acceptable_methods ) ) $arr['method'] = $meta['method'];
            if ( isset( $meta['members'] ) ) $arr['members'] = $meta['members'];
            if ( isset( $meta['masters'] ) ) $arr['masters'] = $meta['masters'];

            $arr['members_arr'] = array_unique( array_merge( $this->get_user_arr( $arr['members'] ), $this->get_members( 'member', $post_id ) ) );

            $p = get_post( $post_id );
            $arr['masters_arr'] = array_unique( array_merge( array( $p->post_author ), $this->get_user_arr( $arr['masters'] ), $this->get_members( 'members', $post_id ) ) );

            wp_cache_set( 'dg_settings', $arr, $post_id );

        }

        return $arr;
    }


    // 
    // Получить глобальных пользователей
    // 

    public function get_members( $type = 'members', $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $arr = array();

        /// ???
        /// ???
        /// ???

        return $arr;
    }




    // 
    // Уровень доступа
    // 
    //  0 - никто
    //  1 - кто-то, но не подписчик
    //  2 - подписчик
    //  3 - преподаватель
    //

    public function access_level( $post_id = NULL, $user_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        if ( ! is_user_logged_in() ) return 0;

        if ( $user_id == NULL ) $user_id = wp_get_current_user()->ID;

        $s = $this->get_settings( $post_id );

        if ( in_array( $user_id, $s['masters_arr'] ) ) return 3;
        if ( in_array( $user_id, $s['members_arr'] ) ) return 2;

        if ( empty( $s['members_arr'] ) ) return 2;

        return 1;
    }



    // 
    // Получить массив ID пользователей по текстовому описанию
    // 

    private function get_user_arr( $text )
    {
        $data = preg_replace( '/[^0-9a-z_-]/', ' ', $text );
        $arr = explode( ' ', $data );
        $arr = array_diff( $arr, array( '' ));

        $arr2 = array();

        foreach ( $arr as $item ) {

            $user = get_user_by( 'slug', $item );
            if ( $user ) $arr2[] = $user->ID;

        }

        // p( $arr );
        // p( $arr2 );

        $arr2 = array_unique( $arr2 );

        return $arr2;
    }


    // 
    // Возвращает описание метода подсчета баллов
    // 

    public function method_caption( $method = NULL )
    {
        if ( $method == NULL ) $method = $this->default_method;

        $arr = array();

        $arr[$method] = $method;

        $arr['better'] = 'Лучший балл'; 
        $arr['summa'] = 'Сумма баллов'; 

        return $arr[$method];
    }


    // 
    // Получить строку времени
    // 

    public function get_time( $timestamp = false )
    {
        // $time = ( $timestamp ) ? date('d.m.Y г. в G:i', $timestamp + get_option( 'gmt_offset' ) * 3600 ) : '&mdash;';
        $time = ( $timestamp ) ? date('d.m.Y в G:i', $timestamp + get_option( 'gmt_offset' ) * 3600 ) : '&mdash;';
        return $time;
    }


    // 
    // Получить публичное имя пользователя
    // 

    public function get_username( $user_id )
    {
        $u = get_userdata( $user_id );
        return $u->data->display_name;
    }


    // 
    // Получить nicename пользователя
    // 

    public function get_nicename( $user_id )
    {
        $u = get_userdata( $user_id );
        return $u->data->user_nicename;
    }
    


    // 
    // Получить key для текущего сайта
    // 

    public function get_key( $token, $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $site_id = get_current_blog_id();
        $key = 'mif_' . $site_id . '_' . $post_id . '_' . $token;

        return $key;
    }


}




?>