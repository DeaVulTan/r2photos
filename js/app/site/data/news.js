// Сервис списка новостей.
//
define([

  'flight/component',
  'jquery'

], function (component, $) {

  return component(function() {
    var self = false;

    this.attributes({
      newsUrl: '/news/main.php'
    });

    this.sendNewsList = function( event, data ) {
      $.ajax({
        url: self.attr.newsUrl,
        data: {
          page: data.page,
          per_page: data.per_page
        },
        success: function( res ) {
          self.trigger( document, 'got-news-list', res );
        }
      });
    }

    // Инициализация компонента.
    //
    this.after('initialize', function() {
      self = this;
      this.on( document, 'need-news-list', this.sendNewsList );
    });

  });

});
