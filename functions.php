<?php

//
// Разные функции
// 
//

defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'get_users_by_metakey' ) ) {

    // 
    // Возвращает массив пользователей, для которых устанволен указанный meta_key
    // Значение meta_key тоже возвращает
    // 
    // Данные кэшируются. Если менять список таких пользовтелей, то надо чистить кэш
    // 

    function get_users_by_metakey( $meta_key )
    {
        global $wpdb;
    
        $meta_key = wp_unslash( $meta_key );        

        $data = wp_cache_get( 'get_user_by_metakey', $meta_key );

        if ( ! $data ) {
        
            $table = $wpdb->usermeta;
            $result = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, meta_value FROM $table WHERE meta_key = %s", $meta_key ), ARRAY_A );

            $data = array();
            foreach ( $result as $r ) $data[$r['user_id']] = $r['meta_value'];

            wp_cache_set( 'get_user_by_metakey', $data, $meta_key );
            
        }

        return $data;
    }


}


?>