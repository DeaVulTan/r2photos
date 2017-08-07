// Добавление товара в корзину, всплывающее сообщение о результате
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

    this.AddToBasket = function() {
      var data = {
          items: {}
        },
        self = this;
      data.items[ this.attr.id ] = this.attr.count;
      $.post( '/orders/do', data ).success(function( res ){
        $( '.js-basket-content' ).html( res.basket );
        self.trigger( document, 'show-popup', { content: res.content } );
      });
      return false;
    };

    this.after('initialize', function() {
      this.attr.id = this.$node.data( 'id' );
      this.attr.count = ( this.$node.data( 'count' ) || 1 )|0;

      this.on( 'click', this.AddToBasket );
    });
  });
});
