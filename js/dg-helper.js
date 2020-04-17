// Отправка данных

jQuery( document ).ready( function( jq ) {


    // Кнопка сохранения
    
    jq( 'body' ).on( 'submit', '#settings_edit_form', function() {

        // console.log('12');

        var form = jq( this ).closest( 'form' );
        var container = jq( this ).closest( '.container' ).parent();
        // var data = new FormData( form.get(0) );
        var data = new FormData( this );

        jq.ajax( {
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: data,
            success: function( response ) {

                if ( response ) {

                    container.html( response );
                    // console.log(response);
                    
                } else {
                    
                    console.log( 'error 1' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 2' );

            },
        } );
    
        return false;

    } );


    // Кнопка изменения настроек
    
    jq( 'body' ).on( 'click', '#settings_edit', function() {

        // console.log('12');

        var nonce = jq( this ).attr( 'data-nonce' );
        var post = jq( this ).attr( 'data-post' );
        var container = jq( this ).closest( '.container' ).parent();
        
        // // jq( this ).find( 'i' ).addClass( 'fa-spin' );

        jq.post( ajaxurl, {
            action: 'settings_edit',
            _wpnonce: nonce,
            post_id: post,
            // comment: comment,
            // rating: rating,
        },
        function( response ) { 

            if ( response ) {

                container.html( response );
                // console.log(response);

            }

        });
     
        return false;

    } );
    


    // Вкладки результатов и настроек

    jq( 'body' ).on( 'click', '.control-panel .settings_tab', function() {
        
        jq( ".control-panel .tab.result_list" ).fadeOut( "fast", function() {

            jq( ".control-panel .tab.settings" ).fadeIn( "fast" );

            jq(  ".control-panel .result_list_tab" ).removeClass( 'active' );
            jq(  ".control-panel .settings_tab" ).addClass( 'active' );

        });
        
        return false;
    } );

    jq( 'body' ).on( 'click', '.control-panel .result_list_tab', function() {
        
        jq( ".control-panel .tab.settings" ).fadeOut( "fast", function() {

            jq( ".control-panel .tab.result_list" ).fadeIn( "fast" );

            jq(  ".control-panel .settings_tab" ).removeClass( 'active' );
            jq(  ".control-panel .result_list_tab" ).addClass( 'active' );

            jq( '#refresh_rl' ).trigger( 'click' );

        });
        
        return false;
    } );



    // Обновить результататы
    
    jq( 'body' ).on( 'click', '#refresh_rl', function() {

        // console.log('12');

        var nonce = jq( this ).attr( 'data-nonce' );
        var post = jq( this ).attr( 'data-post' );
        // var container = jq( this ).parent().parent().parent().parent();
        var container_result = jq( this ).closest( '.container' ).parent();
        var container_count = jq( this ).closest( '.control-panel' ).find( '.count' ).parent();
        
        // jq( this ).find( 'i' ).addClass( 'fa-spin' );
        show_loading( this );

        jq.post( ajaxurl, {
            action: 'refresh_rl',
            _wpnonce: nonce,
            post_id: post,
        },
        function( response ) { 

            if ( response ) {

                var data = jQuery.parseJSON( response );

                container_result.html( data['result'] );
                container_count.html( data['count'] );
                // console.log(data);

            }

        });
     
        return false;

    } );


    // Нажатие на звездочку
    
    jq( 'body' ).on( 'click', '.rating a', function() {

        var nonce = jq( this ).attr( 'data-nonce' );
        var rating = jq( this ).attr( 'data-point' );
        var comment = jq( this ).parent().attr( 'data-comment' );
        var panel = jq( this ).parent().parent();
        
        // console.log(comment);


        // show_loading( this );

        
        jq.post( ajaxurl, {
            action: 'mark',
            _wpnonce: nonce,
            comment_id: comment,
            rating: rating,
        },
        function( response ) { 

            if ( response ) {

                panel.html( response );
                // console.log(response);

            }

        });
     
        return false;

    } );



} )



// Анимация звездочек

window.onload = function() {

    cmt = document.getElementById( 'comments' );

    cmt.addEventListener( 'mouseover', function () {

        elem = event.target.parentNode;

        if ( elem.tagName != 'A' ) return;
        if ( ! elem.classList.contains( 'star' )) return;
           
        elem.parentNode.dataset.select = elem.dataset.point;
        
    });

    cmt.addEventListener( 'mouseout', function () {
        
        elem = event.target.parentNode;

        if ( elem.tagName != 'A' ) return;
        if ( ! elem.classList.contains( 'star' )) return;
            
        elem.parentNode.dataset.select = 0;
        
    });


        // cmt.addEventListener( 'click', function () {
           
        //     elem = event.target.parentNode;

        //     if ( elem.tagName != 'A' ) return;
        //     if ( ! elem.classList.contains( 'star' )) return;
                
        //     console.log('Ок!');

        //     return false;
          
        // });

}

function show_loading( elem )
{
    var div = jq( elem ).closest( 'div' );
    var loading = jq( '.loading', div );
    loading.fadeIn();
}
