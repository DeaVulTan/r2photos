// Карта
//
// Стили карты определяются в отдельном json-файле.
define([

  'flight/component',
  'jquery',
  'lodash',
  'require.json!site/map/styles.json',
  'require.goog!maps,3,sensor:false'

], function (component, $, _, styles) {

  "use strict";

  // Определение компонента.
  //
  return component(function() {

    // Атрибуты.
    //
    this.attributes({
      center: [ 55.8176555, 37.6560859 ],   // - центр карты по умолчанию
      zoom: 10,                           // - масштаб по умолчанию
      icon: '/image/map.png'
    });

    // Показывает описание маркера.
    //
    this.showPoint = function (e, data) {
      var point = this.points[data.id];

      this.infoWindow.close();
      this.infoWindow.setContent(point.content);
      this.infoWindow.open(this.map, point.marker);
    }

    // Обновляет набор маркеров, отображаемых на карте.
    //
    this.updateMarkers = function (e, data) {
      var self = this;

      // Индексируем исходные записи по id:
      var items = _.indexBy(data.points, 'id');

      // Добавляем на карту еще не добавленные точки:
      _.forEach(items, function (item, id) {
        if( !_.has( this.points, id ) ) {
          this.points[id] = {
            id: id,
            content: item.content,
            marker: new google.maps.Marker({
              position: new google.maps.LatLng(item.lat, item.lng),
              title: item.title,
              map: this.map,
              icon: this.attr.icon,
              animation: google.maps.Animation.DROP,
              pixelOffset: new google.maps.Size(-6, 0)
            })
          };

          google.maps.event.addListener(
            this.points[id].marker,
            'click',
            function () { self.trigger('uiShopSelected', { id: id }); }
          );
        }
      }, this);

      // Если уже добавленной точки нет в списке, удаляем ее:
      /*
      _.forEach(this.points, function (point, id) {
        if( typeof( id ) !== 'undefined' && !_.has( items, id ) ) {
          this.points[id].marker.setMap(null);
          delete this.points[id];
        }
      }, this);
      */
    };

    // Инициализация компонента.
    //
    this.after('initialize', function() {
      $.extend( this.attr, this.$node.data( 'attrs' ) );

      // Создаем объект карты:
      this.map = new google.maps.Map(this.node, {
        center: new google.maps.LatLng(
          this.attr.center[0],
          this.attr.center[1]
        ),
        zoom: this.attr.zoom,
        panControl: false,
        rotateControl: false,
        scaleControl: false,
        streetViewControl: false,
        zoomControl: false,
        scrollwheel: false,
        mapTypeControl: false,
        styles: styles
      });

      // Набор отображаемых маркеров:
      this.points = {};
      this.points[0] = {
        lat: this.attr.center[ 0 ],
        lng: this.attr.center[ 1 ],
        title: this.attr.tooltip,
        content: this.attr.content,
        id: 1
      };

      // Информационное окно маркера:
      this.infoWindow = new google.maps.InfoWindow();

      // Обновляем маркеры при получении обновленного списка магазинов:
      this.on(document, 'dataGotShopList', this.updateMarkers);

      // Выводим информацию о магазине на карте при выборе магазина:
      this.on(document, 'uiShopSelected', this.showPoint);

      this.trigger(document, 'dataGotShopList', {points: this.points});
    });

  });
});
