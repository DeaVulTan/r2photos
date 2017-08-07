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
      messageSelector: '.alert'
    });

    this.Init = function() {
      var self = this;
      this.$node.on( 'submit', 'form', function(){ return self.OnSubmit.call( self ); } );
    };//Init

    this.OnSubmit = function() {
      var
        data = this.$node.find( 'form' ).serializeArray(),
        self = this;
      $.post( this.attr.submitUrl, data ).success(function( res ){
        self.$node.html( res );
      });
      return false;
    }//OnSubmit

    //
    this.after('initialize', function () {
      this.attr.submitUrl = this.$node.find( 'form' ).attr( 'action' );

      this.Init();
    });
  });

});
