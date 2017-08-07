// Увеличение картинок по клику
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
    this.Init = function() {
      this.$node.find( 'img[data-picture]' ).colorbox({
        html: function(){
          return $( '<img>' ).attr({ src: $( this ).data( 'picture' ) });
        },
        rel: 'pictures-list',
        maxWidth : '1280px',
        maxHeight: '100%',
        scalePhotos: true
      });
      //this.$node.find( 'img[data-picture]' ).on( 'click', this.OnClick );
    };//Init

    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
