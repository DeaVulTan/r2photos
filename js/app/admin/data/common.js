//
//
define([

  'flight/component',
  'jquery'

], function (component, $) {

  return component(function() {
    var self = false;

    this.BodyOnClick = function() {
      self.trigger( document, 'on-click' );
    };//BodyOnClick

    this.after('initialize', function() {
      self = this;
      $( 'body' ).on( 'click', this.BodyOnClick );
    });

  });

});
