// ajax-календарь новостей
//
define([
  'flight/component',
  'jquery'
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {
    this.attributes({
      prevButton: 'table.calendar .n-prev',
      nextButton: 'table.calendar .n-next'
    });

    this.Init = function() {
      var self = this;
      this.$node.on( 'click', this.attr.prevButton, function(){ self.OnChangeMonth.call( self, this ); } );
      this.$node.on( 'click', this.attr.nextButton, function(){ self.OnChangeMonth.call( self, this ); } );
    };//Init

    this.OnChangeMonth = function( element ) {
      var newDate = $( element ).attr( 'data-new-date' );
      var self = this;
      $.get( '/news/archiv/' + newDate ).success(function( res ){
        self.$node.html( $( '<div>' ).html( res.content ).find( '.calendar-content' ) );
      });
      return false;
    };//OnChangeMonth

    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
