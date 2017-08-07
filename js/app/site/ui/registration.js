// Регистрация
//
define([
  'flight/component',
  'jquery',
  'jquery/colorbox',
  'jquery-ui/i18n/datepicker-ru',
  'jquery-ui/i18n/timepicker-ru',
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {

    // Атрибуты.
    //
    this.attributes({
      formUrl: '/registration',
      submitUrl: '/registration/add'
    });

////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      var
        self = this,
        submitUrl = this.$node.data( 'submit-url' ),
        formUrl = this.$node.data( 'form-url' );
      if( typeof( submitUrl ) === 'string' ) {
        this.attr.submitUrl = submitUrl;
      }
      if( typeof( formUrl ) === 'string' ) {
        this.attr.formUrl = formUrl;
      }

      this.InitDateFields = function( $form ) {
        var options = {
          autoSizeType: true,
          controlType: 'select',
          stepMinute: 5,
          hourMin: 8,
          hourMax: 20,
          minDate: 1
        };
        $form.find( '.js-date' ).each(function(){
          var
            customOptions = $.extend( {}, options ),
            attrs = $( this ).data( 'attrs' ),
            dateTimeFunction = $( this ).datetimepicker;
          if( typeof( attrs ) === 'object' ) {
            if( typeof( attrs.no_min_date ) === 'boolean' && attrs.no_min_date === true ) {
              customOptions.minDate = undefined;
            }
            if( typeof( attrs.custom ) === 'object' ) {
              customOptions = $.extend( customOptions, attrs.custom )
            }
            if( typeof( attrs.no_time ) === 'boolean' && attrs.no_time === true ) {
              dateTimeFunction = $( this ).datepicker;
            }
          }
          dateTimeFunction.call( $( this ), customOptions );
        });
      };//InitDateFields

      this.$node.on( 'click', function(){
        $.colorbox({
          href: self.attr.formUrl,
          onComplete: function(){
            self.trigger( document, 'document:request-alter' );
            var $cbox = $( '#cboxLoadedContent' );
            $cbox.on( 'change', 'form input[name=password]', function(){
              $cbox.find( 'input[name=re_password]' ).val( $( this ).val() );
            });
            self.InitDateFields( $cbox );
            $cbox.on( 'submit', 'form', function(){
              $.post( self.attr.submitUrl, $( this ).serialize() ).success(function( res ){
                if( typeof( res.success ) === 'boolean' && res.success ) {
                  if( typeof( res.redirect ) === 'string' ) {
                    document.location.href = res.redirect;
                  }
                } else {
                  $cbox.html( res ).find( '.focused' ).find( 'input, textarea' );
                  self.trigger( document, 'document:request-alter' );
                  setTimeout( function(){ //фикс для IE8, иначе не фокусится
                    $cbox.find( '.focused' ).find( 'input, textarea' ).focus();
                  }, 1 );
                }
                self.InitDateFields( $cbox );
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
