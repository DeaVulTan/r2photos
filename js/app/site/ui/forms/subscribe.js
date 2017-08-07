// Увеличение картинок по клику
//
define([
  'flight/component',
  'jquery',
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {
    this.attributes({
      messageSelector: '.alert',
      fadeOutDelay: 1000 * 5
    });

    this.Init = function() {
      this.on( 'submit', this.OnSubmit );
    };//Init

    this.OnSubmit = function() {
      var
        data = this.$node.serialize(),
        self = this;
      $.post( this.attr.submitUrl, data ).success(function( res ){
        if( typeof( res.redirect ) === 'string' && res.redirect.length ) {
          location.href = res.redirect;
        }
        self.select( 'messageSelector' ).html( $( '<p>' ).html( res.message ).delay( self.attr.fadeOutDelay ).toggle( 100 ) );
      });
      return false;
    }//OnSubmit

    //
    this.after('initialize', function () {
      this.attr.submitUrl = this.$node.attr( 'action' );

      this.Init();
    });
  });

});
