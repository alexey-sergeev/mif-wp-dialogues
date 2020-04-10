<div class="comments-area control-panel">

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link result_list_tab active" href="#"><span class="count"><?php mif_dg_the_result_count() ?></span> Результаты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link settings_tab" href="#">Настройки</a>
        </li>
    </ul>

    <div class="tab result_list">
    
        <?php mif_dg_the_result_list(); ?>

    </div>

    <div class="tab settings">

        <?php mif_dg_the_settings_tab(); ?>

    </div>

</div>
