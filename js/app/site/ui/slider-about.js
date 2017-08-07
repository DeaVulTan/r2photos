// Слайдер "О салоне"
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
      autoSlideTimer: 4000,
      autoSlide: true,
      infiniteSlider: true,
      prevButton: '.btn-slider-about-prev',
      nextButton: '.btn-slider-about-next',
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
        navPrevSelector:  this.attr.prevButton,
        navNextSelector:  this.attr.nextButton,
      };
      $.extend( $parameters, this.attr );

      $.get( 'slider-about' ).success(function( res ){
        $slider.html( res.content );
        $slider.find( '.slider-about-container' ).iosSlider( $parameters );
      });
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
