// Гуглокарта на странице контактов
//
define([
  'flight/component',
  'jquery',
  'require.json!site/map/styles.json',
  'require.goog!maps,3,sensor:false'
], function (
  component,
  $,
  styles
) {

  "use strict";

  return component(function ( $tmp ) {
    var self = false;

    // Атрибуты.
    //
    this.attributes({
      center: [ 55.8176555, 37.6560859 ],   // - центр карты по умолчанию
      zoom: 17,                           // - масштаб по умолчанию
      icon: '/image/marker.png'
    });

    this.showPoint = function (e, data) {
      var point = this.points[data.id];

      this.infoWindow.close();
      this.infoWindow.setContent(point.content);
      this.infoWindow.open(this.map, point.marker);
    }

    this.updateMarkers = function () {
      // Индексируем исходные записи по id:
      var id = 0;
      this.points[id] = {
        id: id,
        content: self.attr.tooltip,
        marker: new google.maps.Marker({
          position: new google.maps.LatLng( self.attr.center[0], self.attr.center[1] ),
          title: self.attr.tooltip,
          map: this.map,
          icon: this.attr.icon,
          animation: google.maps.Animation.DROP,
          pixelOffset: new google.maps.Size(-6, 0)
        })
      };
    };


    this.InitParameters = function(){
      self.attr.tooltip = self.$node.data( 'tooltip' );
      self.attr.center = [ self.$node.data( 'lat' ), self.$node.data( 'lng' ) ];
      self.attr.zoom = self.$node.data( 'zoom' );
    };
////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      self = this;
      self.InitParameters();

      this.map = new google.maps.Map(this.node, {
        center: new google.maps.LatLng(
          this.attr.center[0],
          this.attr.center[1]
        ),
        zoom: this.attr.zoom,
        panControl: false,
        rotateControl: false,
        scaleControl: true,
        streetViewControl: false,
        zoomControl: true,
        scrollwheel: false,
        styles: styles
      });

      // Набор отображаемых маркеров:
      this.points = {};

      // Информационное окно маркера:
      this.infoWindow = new google.maps.InfoWindow();
      this.updateMarkers();
    });
  });

});
