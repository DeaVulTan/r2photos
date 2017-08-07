// Выбор города
//
define([
  'flight/component',
  'jquery',
], function (
  component,
  $
) {

  "use strict";

  return component(function ( $tmp ) {

    // Атрибуты.
    //
    this.attributes({
      selectMenuSelector: '.js-city-select',
      classMenu: 'city-select-menu',
      minLength: 2,
      listClass: 'js-list',
      defaultText: 'Укажите название:',
      cityNameSelector: '.name'
    });

////////////////////////////////////////////////////////////////////////////////

    this.Init = function() {
      var
        self = this,
        $wrapper = self.select( 'selectMenuSelector' );
      var $input = $( '<input type="text">' ).on( 'click', function(){
        return false;
      }).on( 'keyup', function(){ return self.OnChangeFilter.call( self, this ); } );
      var $list = $( '<div>' ).addClass( self.attr.listClass );
      var $defaultText = $( '<div>' ).html( self.attr.defaultText ).addClass( 'city-name' );
      var $menu = $( '<div>' ).addClass( self.attr.classMenu ).append( $defaultText ).append( $input ).append( $list );
      $wrapper.append( $menu );
      self.$node.on( 'click', function(){ return self.OnToggle.call( self, this ); } );
    };//Init

    this.OnToggle = function( element ) {
      var $wrapper = this.select( 'selectMenuSelector' );
      $wrapper.toggleClass( 'active' );
      if( $wrapper.hasClass( 'active' ) ) {
        this.$node.find( 'input' ).focus();
      }
      return false;
    };//OnToggle

    this.OnChangeFilter = function( element ) {
      var
        name = $( element ).val(),
        self = this;
      if( name.length < this.attr.minLength ) {
        return false;
      }
      $.get( '/city/find', { name: name } ).success(function( res ){
        if( !( typeof( res.success ) === 'boolean' && res.success === true ) ) {
          return false;
        }
        var $list = self.$node.find( '.' + self.attr.listClass );
        $list.html( '' );
        for( var id in res.data ) {
          var $item = $( '<div>' ).html( res.data[ id ].name ).on( 'click', function(){ return self.OnCitySelect.call( self, this ); } );
          $list.append( $item );
        }
      });
      return false;
    };//OnChangeFilter

    this.OnCitySelect = function( element ) {
      var
        name = $( element ).text(),
        self = this;
      $.get( '/city/change', { name: name } ).success(function( res ){
        if( typeof( res.success ) === 'boolean' && res.success ) {
          self.select( 'cityNameSelector' ).html( name );
          self.OnToggle.call( self, element );
        } else {
          alert( 'Ошибка' );
        }
      });
      return false;
    };//OnCitySelect

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      this.Init();
    });
  });

});
