// Фильтр каталога: выбор цены
//
define([
  'flight/component',
  'jquery',
  'jquery-ui/slider'
], function (
  component,
  $,
  forms
) {

  "use strict";

  return component(function ( $tmp ) {
    var
      self = false,
      needUpdateFilter = false,
      filterUpdateTimer = -1;

    this.attributes({
      sliderSelector: '.js-price-slider',
      minInputSelector: 'input[name=price_min]',
      maxInputSelector: 'input[name=price_max]',
      formUpdateTimeout: 1 * 1000 //время, через которое применится фильтр после изменения диапазона цен
    });

    this.OnSlide = function( event, ui ) {
      var
        minValue = self.number_format( ui.values[ 0 ], 0, '.', ' ' ),
        maxValue = self.number_format( ui.values[ 1 ], 0, '.', ' ' );
      self.select( 'minInputSelector' ).val( minValue );
      self.select( 'maxInputSelector' ).val( maxValue );
    };

    this.OnChange = function() {
      setTimeout(function(){
        if( self.select( 'minInputSelector' ).is( ':focus' ) || self.select( 'maxInputSelector' ).is( ':focus' ) ) {
          return false;
        }
        needUpdateFilter = true;
        setTimeout( self.RefreshChangeTimer, 100 );
      }, 1);
    };

    this.RefreshChangeTimer = function() {
      self.ClearChangeTimer();
      if( needUpdateFilter ) {
        filterUpdateTimer = setTimeout( self.DoUpdateFilter, self.attr.formUpdateTimeout );
      }
    };

    this.ClearChangeTimer = function() {
      if( filterUpdateTimer !== -1 ) {
        clearTimeout( filterUpdateTimer );
        filterUpdateTimer = -1;
      }
    };//ClearChangeTimer

    this.DoUpdateFilter = function() {
      needUpdateFilter = false;
      self.trigger( 'filter-changed' );
    };//DoUpdateFilter

    this.number_format = function( number, decimals, dec_point, thousands_sep ) {
      number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
      var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
          var k = Math.pow(10, prec);
          return '' + (Math.round(n * k) / k)
            .toFixed(prec);
        };
      // Fix for IE parseFloat(0.55).toFixed(0) = 0;
      s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
      if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
      }
      if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    };

    this.OnChangeInput = function(){
      var
        minValue = Math.min( Math.max( self.GetPriceMinValue(), self.attr.priceMin ), self.attr.priceMax ),
        maxValue = Math.max( Math.max( Math.min( self.GetPriceMaxValue(), self.attr.priceMax ), self.attr.priceMin ), minValue );
      self.select( 'sliderSelector' ).slider( 'values', [ minValue, maxValue ] );
      self.OnSlide( null, { values: [ minValue, maxValue ] } );
    };

    this.GetPriceMinValue = function() {
      return self.select( 'minInputSelector' ).val().replace( /[^0-9]/, '' )|0;
    };

    this.GetPriceMaxValue = function() {
      return self.select( 'maxInputSelector' ).val().replace( /[^0-9]/, '' )|0;
    };

    this.after('initialize', function () {
      self = this;

      self.attr.priceMin = self.$node.data( 'price-min' );
      self.attr.priceMax = self.$node.data( 'price-max' );
      self.select( 'sliderSelector' ).slider({
        range: true,
        min: self.attr.priceMin,
        max: self.attr.priceMax,
        values: [ self.GetPriceMinValue(), self.GetPriceMaxValue() ],
        slide: this.OnSlide,
        change: this.OnChange
      });
      self.select( 'minInputSelector' ).on( 'change', this.OnChangeInput );
      self.select( 'maxInputSelector' ).on( 'change', this.OnChangeInput );
      self.$node
        .on( 'mousemove', this.RefreshChangeTimer )
        .on( 'mouseleave', this.RefreshChangeTimer );
    });
  });

});
