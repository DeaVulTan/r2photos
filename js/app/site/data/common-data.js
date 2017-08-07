//
//
define([

  'flight/component',
  'jquery',
  'lib/jquery-migrate',
  'lib/forms',
  'lib/placeholder',
  'jquery/cookie',
  'jquery/crypt'

], function (component, $, _, forms) {

  return component(function() {
    var self = false;
    this.attributes({
      popupDialogShowPeriod: 1000 * 2,      //длительность показа всплывающих сообщений
      popupDialogFadeOutPeriod: 1000 * 0.2  //длительность исчезновения всплывающих сообщений
    });

    this.BodyOnClick = function() {
      self.trigger( document, 'on-click' );
    };//BodyOnClick

    this.ShowPopup = function( event, data ) {
      var
        $popup = $( '<div>' ).addClass( 'popup-dialog' ).html( data.content ),
        self = this;
      $popup.animate({ opacity: 1 }, this.attr.popupDialogShowPeriod, function(){ $( this ).unbind( 'mouseover mouseleave' ); }).animate({ opacity: 0 }, this.attr.popupDialogFadeOutPeriod, function(){
        $( this ).remove();
      }).on( 'mouseover', function(){
        $( this ).stop();
        $( this ).css({ opacity: 1 });
      } ).on( 'mouseleave', function(){
        $popup.animate({ opacity: 1 }, self.attr.popupDialogShowPeriod, function(){ $( this ).unbind( 'mouseover mouseleave' ); }).animate({ opacity: 0 }, self.attr.popupDialogFadeOutPeriod, function(){
          $( this ).remove();
        });
      });
      $( '.js-popup-container' ).prepend( $popup );
      return false;
    };

    this.after('initialize', function() {
      self = this;
      this.on( document, 'show-popup', this.ShowPopup );
      $( 'body' ).on( 'click', this.BodyOnClick );
      forms.setup();
      if( $.browser.msie ) {
        $( 'input[placeholder], textarea[placeholder]' ).placeholder();
        $( 'form' ).on( 'submit', function(){
          $( this ).find( '[placeholder]' ).focus().blur();
        } );
      }

      InitFormsCapcha( 'r2photos', 'uhtj405yu04imt3q08yshdlk' );
      if( location.hash.length ) {
        $( 'a[name='+( location.hash.replace( '#', '' ) )+']' ).remove(); //фикс для ios, иначе прокрутка залипает на якоре
      }
    });
  
    function InitFormsCapcha( cName, salt ) {
      $( 'body' ).on( 'mousemove touchmove', function(){
        SetCookie( cName, salt );
      });
    }//InitFormsCapcha

    function SetCookie( str, salt ) {
      var NowDate = new Date();
      var day = ( NowDate.getDate() < 10 ? '0'+NowDate.getDate() : NowDate.getDate() );
      var month = ( ( NowDate.getMonth() + 1 ) < 10 ? '0'+( NowDate.getMonth() + 1 ) : ( NowDate.getMonth() + 1 ) );
      var fullDate = day+'.'+month+'.'+NowDate.getFullYear();
      var cookieName = $().crypt({ method: "md5",source: str + fullDate });
      var cookieVal = $().crypt({ method: "md5",source: str + fullDate+salt });
      if ( !$.cookie( cookieName ) ) {
        var CookieOption = { path: '/' };
        $.cookie( cookieName, cookieVal, CookieOption );
      }
    }//SetCookie

  });

});
