// Замена плейсхолдеров инпутов формы
//
define([
  'flight/component',
  'jquery',
  'lib/placeholder'
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {
    this.attributes({
      items: 'input[type=text][placeholder], input[type=password][placeholder]',
    });
    this.Init = function() {
      this.select( 'items' ).placeholder();
    };//Init

    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
