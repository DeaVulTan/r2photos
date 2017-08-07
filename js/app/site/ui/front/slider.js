// Слайдеры на главной странице
//
define([
  'flight/component',
  'jquery',
  'jquery/iosslider'
], function (
  component,
  $
) {

  "use strict";

  return component(function () {

    // Атрибуты.
    //
    this.attributes({
      autoSlideTimer: 3000,
      autoSlide: false,
      infiniteSlider: true,
      prevButton: false,
      nextButton: false,
      slideSelector: false,
      buttonSelector: false
    });
    var self = false;

    this.OnContentChange = function( args ) {
      var $buttons = $(args.sliderObject).parent().find( this.attr.buttonSelector );
      $buttons.removeClass('selected');
      var $next = $buttons.eq((args.currentSlideNumber - 1));
      $next.addClass('selected');
    };//OnContentChange

    this.InitSlider = function(){
      var self = this;
      var $attrs = this.$node.data( 'attrs' );
      $.extend( this.attr, $attrs );

      var $slider = this.$node;
      var $parameters = {
        navPrevSelector:  $( this.attr.prevButton ),
        navNextSelector:  $( this.attr.nextButton ),
        navSlideSelector: this.select( 'slideSelector' )
        //onSlideChange: function( args ){ self.OnContentChange.call( self, args ); }
      };
      $.extend( $parameters, this.attr );

      $slider.iosSlider( $parameters );
    }

////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      self = this;
      this.InitSlider();
    });
  });

});
