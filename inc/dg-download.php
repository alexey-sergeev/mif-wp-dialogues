<?php

//
// Методы загрузки
// 
//

defined( 'ABSPATH' ) || exit;


class mif_dg_download extends mif_dg_core { 

    function __construct()
    {
        parent::__construct();

        add_action( 'init', array( $this, 'force_download' ) );

    }



    
    // 
    // Сформировать txt с рейтингом
    // 

    public function get_rating_txt( $post_id )
    {
        $p = get_post( $post_id );

        $txt = '';

        $txt .= "Страница:\t" . $p->post_title . "\r\n";
        $txt .= "Дата:\t" . $this->get_time( time() ) . "\r\n";
        $txt .= "\r\n";
        $txt .= "№\tФ.И.О.\tОценка\tПользователь\tПреподаватель\tДата оценки\r\n";
        
        $result = $this->get_result( $post_id );
        
        $n = 1;

        foreach ( $result as $item ) {

            $txt .= $n++ . "\t";
            $txt .= $item['display_name'] . "\t";
            $txt .= $item['rating'] . "\t";
            $txt .= $item['nicename'] . "\t";
            $txt .= $item['master_display_name'] . "\t";
            $txt .= $this->get_time( $item['timestamp'] ) . "\r\n";

        }

        $upload_dir = (object) wp_upload_dir();
        $file = trailingslashit( $upload_dir->path ) . md5( $post_id ) . '.txt';

        $fp = fopen( $file, 'w' );
        fwrite( $fp, $txt );
        fclose( $fp );

        return $file;
    }




    // 
    // Инициализация скачивания файла
    // 


    public function force_download()
    {
        if ( ! $_REQUEST['download'] == 'rating' ) return;
        if ( empty( $_REQUEST['id'] ) ) return;

        $post_id = (int) $_REQUEST['id'];
        
        if ( $this->access_level( $post_id ) < 2 ) return;
       
        
        $file = $this->get_rating_txt( $post_id );
   
        $this->download( $file, 'Рейтинг.txt' ) ;
    }


    // 
    // Скачивание файла
    // 

    public function download( $file, $name = '' ) 
    {
        if ( empty( $file ) ) return;
    
        if ( file_exists( $file ) ) {
    
            if ( ob_get_level() ) ob_end_clean();
    
        } else {
    
            return;
    
        }
    
        $content_types = array(
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip, application/x-compressed-zip',
        );
        
        $content_type = 'application/octet-stream';
        $extension_arr = explode( ".", $file );
        $extension = array_pop( $extension_arr );
        if ( isset( $content_types[$extension] ) ) $content_type = $content_types[$extension];
    
        if ( $name == '' ) $name = basename( $file );
    
        header('Content-Description: File Transfer');
        header('Content-Type: ') . $content_type;
        header('Content-Disposition: attachment; filename="' . $name ) . '"';
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize( $file ) );
    
        if ( $fd = fopen( $file, 'rb' ) ) {
    
            while ( !feof($fd) ) print fread( $fd, 1024 );
            fclose($fd);
    
        }

        unlink( $file );

        exit;
    }
    



}




?>