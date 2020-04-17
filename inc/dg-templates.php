<?php

//
// Функции шаблонов 
// 
//


defined( 'ABSPATH' ) || exit;



//
// Таблица результатов
//

function mif_dg_the_result_list()
{
    global $mif_dg;
    echo $mif_dg->get_result_list();
}



//
// Страница настроек
//

function mif_dg_the_settings_tab()
{
    global $mif_dg;
    echo $mif_dg->get_settings_tab();
}


//
// КОличество результатов
//

function mif_dg_the_result_count()
{
    global $mif_dg;
    echo '<span>' . $mif_dg->get_result_count() . '</span>';
}






?>