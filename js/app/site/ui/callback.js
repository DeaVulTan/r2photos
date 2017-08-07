// Формы "Перезвоните мне", "Напишите нам"
//
define([
  'flight/component',
  'jquery',
  'jquery/colorbox'
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {

    // Атрибуты.
    //
    this.attributes({
      submitUrl: false
    });

////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      var callback = this;
      this.attr.submitUrl = this.$node.data( 'submit-url' );

      this.$node.on( 'click', function(){
        $.colorbox({
          href: callback.attr.submitUrl,
          transition: 'fade',
          onComplete: function(){
            var $cbox = $( '#cboxLoadedContent' );
            $cbox.on( 'submit', 'form', function(){
              $cbox.find( 'input[type=submit]' ).attr({ disabled: true });
              $.post( callback.attr.submitUrl, $( this ).serialize() ).success(function( res ){
                $cbox.find( 'input[type=submit]' ).attr({ disabled: false });
                if( typeof( res.redirect ) === 'string' ) {
                  location.href = res.redirect;
                  return false;
                }
                if( typeof( res.message ) === 'string' ) {
                  $cbox.find( '.popup-content' ).html( res.message );
                } else {
                  $cbox.html( res );
                }
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
