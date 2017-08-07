// Различные скрипты для текстовой страницы
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
    this.attributes({
      colorbox: {
        transition: 'fade',
        rel: 'pictures-list',
        maxWidth : '1280px',
        maxHeight: '100%',
        scalePhotos: true,
        onComplete: $.colorbox.resize
      }
    });

    this.Init = function() {
      var self = this;
      this.$node.find( 'img' ).each(function(){
        var $parent = $( this ).parent( 'a' );
        if( $parent.length && $parent.attr( 'href' ).match( /png|jpeg|jpg|gif|bmp|tga$/i ) ) {
          $parent.colorbox( self.attr.colorbox );
        } else {
          $( this ).colorbox( self.attr.colorbox );
        }
      });
    };//Init

    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
