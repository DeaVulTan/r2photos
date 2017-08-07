// Увеличение картинок по клику
//
define([
  'flight/component',
  'jquery',
  'lib/forms',
], function (
  component,
  $,
  forms
) {

  "use strict";

  return component(function ( $tmp ) {
    this.Init = function() {
      forms.setup();
    };//Init

    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
