// Сервис добавления товара в корзину.
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
    var
      timerId = -1,
      replacePopupTimerId = -1,
      y = 0;

    this.attributes({
      url: '/orders/do',
      basketPopupShowDuration: 10 * 1000,   //длительность отображения всплывающего диалога
      basketPopupDelayBeforeHide: 1 * 1000, //период, после которого скроется сообщение после потери фокуса курсором
      replaceTimerDuration: 100
    });

    this.RemoveReplaceTimer = function() {
      replacePopupTimerId = -1;
      y = 0;
    };

    this.AddToBasket = function( event, data ) {
      var self = this;
      if( replacePopupTimerId === -1 ) {
        self.RemoveBasketPopup();
      }
      var data = {
        ID: data.data.id,
        COUNT: data.data.count
      };
      replacePopupTimerId = setTimeout( this.RemoveReplaceTimer, this.attr.replaceTimerDuration );
      $.get( this.attr.url, data ).success(function( res ){
        self.trigger( document, 'show-popup-dialog', { data: { content: res.popup } } );
        self.trigger( document, 'added-to-basket', { data: data } );
      });
      return false;
    };

    this.RemoveBasketPopup = function() {
      $( '.js-basket-popup' ).removeClass( 'js-basket-popup' ).animate({ opacity: 0 }, 1000, function(){ $( this ).remove(); });
      if( timerId >= 0 ) {
        clearTimeout( timerId );
        timerId = -1;
      }
    };//RemoveBasketPopup

    this.after('initialize', function () {
      this.attr.id = this.$node.data( 'id' );
      this.attr.count = ( this.$node.data( 'count' ) || 1 )|0;

      this.on( document, 'buy-item', this.AddToBasket );
      this.on( document, 'on-click', this.RemoveBasketPopup );
    });
  });
});
