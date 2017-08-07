// Изменение корзины
//
define([
  'flight/component',
  'jquery',
  'jquery/ikSelect',
  'jquery-ui/i18n/datepicker-ru',
  'jquery-ui/i18n/timepicker-ru',
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {
    this.attributes({
      basketSelector: 'form[name=updateOrder]',
      inputSelector: 'input[name^=countOrd_]',
      deleteSelector: '.js-del',
      basketRowSelector: '.basket-tr',
      submitButtonSelector: '.js-submit',
      formUrl: '/orders/form',
      orderCloseUrl: '/orders/close'
    });

    this.Init = function() {
      var self = this;
      this.attr.url = this.$node.attr( 'action' );
      this.$node.on( 'change', function(){ return self.OnChange.call( self, this ); } );
      this.$node.on( 'click', self.attr.deleteSelector, function(){ return self.OnDelete.call( self, this ); } );
      this.$node.on( 'click', self.attr.submitButtonSelector, function(){ return self.OnSubmit.call( self, this ); } );
    };

    //отображение диалога ввода контактной информации
    this.OnSubmit = function( element ) {
      var self = this;
      $.get( self.attr.formUrl ).success(function( res ){
        if( !( typeof( res.success ) === 'boolean' && res.success ) ) {
          return false;
        }
        $.colorbox({
          html: res.content,
          onComplete: function(){
            $( '#cboxLoadedContent .btn-select' ).ikSelect({
              ddFullWidth: false,
              syntax: '<div class="ik_select_link"><span class="ik_select_link_text"></span><span class="corner"></span></div><div class="ik_select_block"><div class="ik_select_list"></div></div>'
            });
            $( '#cboxLoadedContent .js-date' ).datetimepicker({
              autoSizeType: true,
              controlType: 'select',
              stepMinute: 5,
              hourMin: 8,
              hourMax: 20,
              minDate: 1
            });
            $.colorbox.resize();
            $( '#cboxLoadedContent input[name=delivery_info]' ).on( 'click', function(){
              $( '#cboxLoadedContent .js-delivery-info' ).toggleClass( 'active', $( this ).is( ':checked' ) );
              $.colorbox.resize();
            });
            $( '#cboxLoadedContent form' ).on( 'submit', function(){ return self.OnCloseOrder.call( self, this ); });
            $( '#cboxLoadedContent form input, #cboxLoadedContent form select, #cboxLoadedContent form textarea' ).on( 'change', function(){
              $.cookie( 'orders-form-value-'+( $( this ).attr( 'name' ) ), $( this ).val() );
            });
          }
        });
      });
      return false;
    };//OnSubmit

    //завершение оформления заказа
    this.OnCloseOrder = function( element ) {
      var
        self = this,
        data = $( element ).serializeArray();
      $.post( self.attr.orderCloseUrl, data ).success(function( res ){
        if( typeof( res.success ) !== 'boolean' ) {
          return false;
        }
        if( typeof( res.error ) === 'string' ) {
          $( '#cboxLoadedContent .alert' ).html( res.error );
        }
        if( typeof( res.basket ) === 'string' ) {
          $( '.js-basket-content' ).html( res.basket );
        }
        if( typeof( res.content ) === 'string' ) {
          $( '#cboxLoadedContent, .js-page-content' ).html( res.content );
        }
        $.colorbox.resize();
      });
      return false;
    }//OnCloseOrder

    //изменилось число товаров в корзине
    this.OnChange = function OnChange( element ) {
      this.select( 'inputSelector' ).each(function(){
        var fixedValue = Math.max( 1, $( this ).val()|0 );
        $( this ).val( fixedValue );
      });

      this.SubmitForm();
      return false;
    }//OnChange

    //удалить товар
    this.OnDelete = function( element ) {
      var self = this;
      $( element ).closest( self.attr.basketRowSelector ).find( self.attr.inputSelector ).val( 0 );

      this.SubmitForm();
      return false;
    };//OnDelete

    this.SubmitForm = function() {
      var
        self = this,
        data = self.$node.serializeArray();

      $.post( self.attr.url, data ).success(function( res ){
        if( !( typeof( res.success ) === 'boolean' && res.success ) ) {
          return false;
        }
        var content = $( '<div>' ).html( res.content ).find( self.attr.basketSelector ).html();
        self.$node.html( typeof( content ) === 'string' && content.length ? content : res.content );
        $( '.js-basket-content' ).html( res.basket );
      });
      return true;
    };//SubmitForm

    this.after('initialize', function() {
      this.Init();
    });
  });
});
