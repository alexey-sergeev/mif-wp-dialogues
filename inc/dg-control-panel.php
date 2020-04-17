<?php

//
// Методы панели управления
// 
//

defined( 'ABSPATH' ) || exit;


class mif_dg_control_panel extends mif_dg_rating_panel { 

    function __construct()
    {
        parent::__construct();
    }



    //
    // Добавить панель управления на всей странице
    //

    public function add_control_panel( $comment_template )
    {

        if ( $this->access_level( $post_id ) < 2 ) return false;

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'control-panel.php' ) ) {
            
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/control-panel.php', false );

        }    

        return $comment_template;
    }



    //
    // Вывести страницу настроек
    //

    public function get_settings_tab( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $out = '';

        $s = $this->get_settings( $post_id );

        $out .= '<div class="container mt-5">';

        // if ( $this->access_level( $post_id ) > 2 ) {

        //     $out .= '<div class="row mb-3"><div class="col">Код для интеграции: ';
        //     $out .= '<span class="caption">g:' . get_current_blog_id() . ':' . $post_id . '</span>';
        //     $out .= '</div></div>';

        // }

        $out .= '<div class="row mb-3">';
        $out .= '<div class="col col-6">Максимальный балл: <span class="caption">' . $s['max_rating'] . '</span></div>';
        
        
        $out .= '</div>';
        
        
        
        
        $out .= '<div class="row mb-3"><div class="col">Подсчет баллов: <span class="caption">' . $this->method_caption( $s['method'] ) . '</span></div></div>';
        $out .= '<div class="row mb-3"><div class="col">Подписчики:</div></div>';
        
        $out .= '<div class="row mb-3"><div class="col">';
        
        if ( ! empty( $s['members_arr'] ) ) {
            
            foreach ( $s['members_arr'] as $m ) $out .= '<span class="mr-2">' . $this->get_avatar( $m, 30 ) . '</span>';
            
        } else {
            
            $out .= '<span class="caption">Все пользователи портала</span>';
        }
        
        $out .= '</div></div>';
        
        $out .= '<div class="row mb-3"><div class="col">Преподаватели: </div></div>';
        $out .= '<div class="row mb-3"><div class="col">';
        
        foreach ( $s['masters_arr'] as $m ) $out .= '<span class="mr-2">' . $this->get_avatar( $m, 30 ) . '</span>';
        
        $out .= '</div></div>';
        
        if ( $this->access_level( $post_id ) > 2 ) {
            
            $out .= '<div class="row mt-5"><div class="col col-6"><a href="" id="settings_edit" data-nonce="' . wp_create_nonce( 'mif-dg' ) . '" data-post="' . $post_id . '">Изменить</a>';
            $out .= '<span class="loading ml-3"><i class="fa fa fa-spinner fa-spin"></i></span>';
            $out .= '</div>';
            
            $out .= '<div class="col col-6 text-right"><span title="Код для интеграции">e:' . get_current_blog_id() . ':' . $post_id . '</span></div>';
            
            $out .= '</div>';
    

        }

        $out .= '</div>';

        return $out;
    }



    //
    // Вывести страницу редактирования настроек
    //

    public function get_settings_edit_tab( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        if ( $this->access_level( $post_id ) < 3 ) return false;
        
        $s = $this->get_settings( $post_id );
        $m = array( 'better' => '', 'summa' => '' );
        $m[$s['method']] = ' checked';

        $out = '';

        $out .= '<div class="container mt-5">';

        $out .= '<form id="settings_edit_form">';
        
        $out .= '<div class="form-group row"><label for="max_rating" class="col-4 col-form-label pt-0">Максимальная оценка:</label>
                <div class="col-2"><input type="text" class="form-control form-control-lg mt-2" name="max_rating" id="max_rating" value="' . $s['max_rating'] . '"></div>
                <div class="col-6 pt-2"><span class="caption"> от 1 до 10</span></div>
                </div>';

        $out .= '<div class="row"><legend class="col-form-label col-4 pt-0">Подсчет баллов:</legend>
                <div class="col-sm-8">
                <div class="form-check">
                <input class="form-check-input mt-2" type="radio" name="method" id="better" value="better"' . $m['better'] . '>
                <label class="form-check-label ml-3" for="better">Лучший балл</label>
                </div>
                <div class="form-check">
                <input class="form-check-input mt-2" type="radio" name="method" id="summa" value="summa"' . $m['summa'] . '>
                <label class="form-check-label ml-3" for="summa">Сумма баллов</label>
                </div>
                </div>
                </div>';
        
        $out .= '<div class="row mt-5">
                <legend class="col-form-label col-4 pt-0">Подписчики:</legend>
                <div class="col-8"><textarea class="mt-2" name="members">' . $s['members'] . '</textarea></div>
                </div>';
        
        $out .= '<div class="row mt-5">
                <legend class="col-form-label col-4 pt-0">Преподаватели:</legend>
                <div class="col-8"><textarea class="mt-2" name="masters">' . $s['masters'] . '</textarea></div>
                </div>';
        
        $out .= '<div class="row mt-5">
                <div class="col-8"><button type="submit" class="btn mb-2" id="settings_save">Сохранить</button></div>            
                </div>';
        
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-dg' ) . '">';
        $out .= '<input type="hidden" name="action" value="settings_save">';
        $out .= '<input type="hidden" name="post_id" value="' . $post_id . '">';

        $out .= '</form>';
        
        $out .= '</div>';

        return $out;
    }



    //
    // Вывести список результатов
    //

    public function get_result_list( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $out = '';

        $result = $this->get_result( $post_id );

        $out .= '<div class="container">';
        
        $out .= '<div class="row">';
        $out .= '<div class="col pt-5 pb-5">';

        if ( $this->access_level( $post_id ) > 2 ) $out .= '<a href="?id=' . $post_id . '&download=rating" class="big-button mr-3" id="download_rl" title="Скачать"><i class="fa fa-download" aria-hidden="true"></i></a>';
        $out .= '<a href="#" class="big-button mr-3" title="Обновить" id="refresh_rl" data-nonce="' . wp_create_nonce( 'mif-dg' ) . '" data-post="' . $post_id . '"><i class="fa fa-refresh" aria-hidden="true"></i></a>';
        $out .= '<span class="loading"><i class="fa fa fa-spinner fa-spin"></i></span>';
        
        $out .= '</div>';
        $out .= '</div>';

        foreach ( $result as $item ) {

            $out .= '<div class="row">';

            $out .= '<div class="col col-6">';
            $out .= $item['display_name'];
            $out .= '</div>';
            
            $out .= '<div class="col col-2">';
            $out .= '<span class="rating" title="' . $item['master_display_name'] . ', ' . $this->get_time( $item['timestamp'] ) . '">' . $item['rating'] . '</span>';
            $out .= '</div>';

            $out .= '<div class="col col-4">';

            foreach ( $item['comments'] as $c ) {

                $color = 'text-success';
                $link = get_comment_link( $c );
                $title = '';
                
                if ( in_array( $c, $item['unmarked'] ) ) {
                    
                    $color = 'text-warning';
                    $title = ' title="Ожидает оценки"';
                    
                }
                
                if ( in_array( $c, $item['unapproved'] ) ) {
                    
                    $color = 'text-danger';
                    $link = get_edit_post_link( $post_id );
                    if ( $link ) $link .= '#commentsdiv';
                    $title = ' title="Ожидает проверки и одобрения"';

                }

                if ( $link ) {

                    $out .= '<a href="' . $link . '" class="' . $color . ' mr-2"' . $title . '><i class="fa fa-comment" aria-hidden="true"></i></a>';
                    
                } else {
                    
                    $out .= '<span class="' . $color . ' mr-2"' . $title . '><i class="fa fa-comment" aria-hidden="true"></i></span>';

                }
                // $out .= $c;

            }



            $out .= '</div>';

            $out .= '</div>';
        }
        
        if ( empty( $result ) ) $out .= '<span class="caption">Пока нет результатов</span>';

        $out .= '</div>';
        
        // p($result);

        
        return $out;
    }



    //
    // Вывести количество результатов
    //

    public function get_result_count( $post_id = NULL )
    {
        global $post;
        if ( $post_id == NULL ) $post_id = $post->ID;

        $result = $this->get_result( $post_id );

        $arr = array();

        foreach ( $result as $r ) if ( count( $r['unmarked'] ) || count( $r['unapproved'] ) ) $arr[] = true;

        $count = count( $arr );
        $class = 'bg-warning';

        if ( $count == 0 ) {
            
            $count = count( $result );
            $class = 'bg-success';

        }

        $out = '<span class="count ' . $class . '">' . $count . '</span>';

        return $out;
    }




}




?>