// Фильтр каталога
//
define([
  'flight/component',
  'jquery',
  'lib/cookie'
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {
    var self = false;

    this.attributes({
      priceMinSelector: '.js-price input[name=price_min]',
      priceMaxSelector: '.js-price input[name=price_max]',
      catalogIdSelector: 'input[name=catalog_id]',
      catalogIdLinkSelector: '.js-catalog-id',
      blockSelector: '.filter-box',
      blockTitleSelector: '.filter-box-title',
      contentBlockForScrollbar: '.filter-box-content'
    });

    // OnChange
    //
    this.OnChange = function() {
      var tmpData = this.$node.serializeArray();
      var data = {};
      for( var q in tmpData ) {
        data[ tmpData[ q ].name ] = tmpData[ q ].value;
      }
      data.price_min = data.price_min.replace( /[^0-9]/, '' )|0;
      data.price_max = data.price_max.replace( /[^0-9]/, '' )|0;
      if( data.price_min <= this.attr.priceMin ) {
        //data.PRICE_MIN = undefined;
      }
      if( data.price_max >= this.attr.priceMax ) {
        //data.PRICE_MAX = undefined;
      }
      $.get( this.attr.url, data ).success( this.OnReceiveCatalog );
      return false;
    };

    // OnReceiveCatalog
    //
    this.OnReceiveCatalog = function( res ) {
      if( typeof( res.content ) !== 'undefined' ) {
        var content = $( res.content ).find( '.js-catalog-content' ).html();
        $( '.js-catalog-content' ).html( content );
        self.trigger( document, 'document:request-alter' );
        //history.pushState( { content: content }, 'Каталог', res.url );
        location.href = res.url + '#content';
      }
    };

    // OnPopState
    //
    this.OnPopState = function( event ) {
      if( event.state ) {
        $( '.js-catalog-content' ).html( event.state.content );
        window.title = event.state.pageTitle;
      }
    };

    // InitEachBlock
    //
    this.InitEachBlock = function() {
      var
        type = $( this ).data( 'type' ),
        cookieName = 'r2photos-catalog-filter-' + type,
        isHidden = $.cookie( cookieName )|0,
        height = $( this ).height();
      if( isHidden ) {
        $( this ).addClass( 'hidden' );
      }
      if( height > 400 ) { //add scrollbar
        $( this ).find( self.attr.contentBlockForScrollbar ).addClass( 'scrollable' ).mCustomScrollbar({
          scrollInertia: 0
        });
      }
    };//InitEachBlock

    // OnClickBlock
    //
    this.OnClickBlock = function() {
      var
        $block = $( this ).closest( self.attr.blockSelector ),
        type = $block.data( 'type' ),
        cookieName = 'r2photos-catalog-filter-' + type;
      $block.toggleClass( 'hidden' );
      var isHidden = $block.hasClass( 'hidden' );
      $.cookie( cookieName, isHidden ? 1 : 0, { path: '/', expires: 1 } );
    };//OnClickBlock

    this.OnCatalogPartSelect = function() {
      var catalogId = $( this ).data( 'id' )|0;
      self.select( 'catalogIdSelector' ).val( catalogId );
      self.select( 'priceMinSelector' ).val( 0 );
      self.select( 'priceMaxSelector' ).val( 0 );
      self.trigger( document, 'filter-changed' );
      return false;
    };//OnCatalogPartSelect

    // initialize
    //
    this.after('initialize', function () {
      self = this;
      this.attr.url = this.$node.attr( 'action' );
      this.attr.priceMin = ( this.select( 'priceMinSelector' ).val().replace( /[^0-9]/, '' ) )|0;
      this.attr.priceMax = ( this.select( 'priceMaxSelector' ).val().replace( /[^0-9]/, '' ) )|0;
      this.attr.request = this.$node.data( 'request' );
      this.on( 'change', this.OnChange );
      this.$node.on( 'click', this.attr.catalogIdLinkSelector, this.OnCatalogPartSelect );
      this.on( document, 'filter-changed', this.OnChange );
      window.onpopstate = this.OnPopState;
      this.select( 'blockSelector' )
        .each( this.InitEachBlock )
        //.on( 'click', this.attr.blockTitleSelector, this.OnClickBlock )
      ;
    });//initialize
  });

});
