// Календарь
//
define([

  'flight/component',
  'jquery',
  'lodash'

], function (component, $, _) {

  return component(function() {
    var self = false;

    this.attributes({
      url: '/news/calendar.php'
    });

    this.sendCalendarInfo = function( event, data ) {
      $.ajax({
        url: self.attr.url,
        data: {
          date: data.date
        },
        success: function( res ) {
          self.trigger( document, 'got-calendar-data', res );
        }
      });
    }

    // Инициализация компонента.
    //
    this.after('initialize', function() {
      self = this;
      this.on( document, 'need-calendar-data', this.sendCalendarInfo );
    });

  });

});
