// "Написать письмо"
//
define([
  'flight/component',
  'jquery',
  'jquery/colorbox',
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {

    // Атрибуты.
    //
    this.attributes({
      submitUrl: '/mail/send'
    });

////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      var mail = this;

      this.$node.on( 'click', function(){
        $.colorbox({
          href: '/mail',
          onComplete: function(){
            var $cbox = $( '#cboxLoadedContent' );
            $cbox.on( 'submit', 'form', function(){
              $.post( mail.attr.submitUrl, $( this ).serialize() ).success(function( res ){
                $cbox.html( res ).find( '.focused' ).find( 'input, textarea' );
                setTimeout( function(){ //фикс для IE8, иначе не фокусится
                  $cbox.find( '.focused' ).find( 'input, textarea' ).focus();
                }, 1 );
                $.colorbox.resize();
              });
              return false;
            });
          }
        });
        return false;
      });
    });
  });

});
