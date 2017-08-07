// Ссылки на расшаривание страницы в соцсетях
//
define([
  'flight/component',
  'jquery',
], function (component, $) {

  "use strict";

  return component(function () {

    this.attributes({
      linksSelector: 'a',
      links: {
        lj: 'lj',
        fb: 'facebook',
        tw: 'twitter',
        od: 'odnoklassniki',
        vk: 'vkontakte',
        gplus: 'gplus'
      }
    });

    this.Init = function() {
      var self = this;
      this.select( 'linksSelector' ).each(function(){
        var
          type = $( this ).data( 'type' ),
          url = 'http://share.yandex.ru/go.xml?service='+( self.attr.links[ type ] )+'&url='+( self.attr.baseUrl )+'&title='+( self.attr.title );
        $( this ).attr({ href: url });
      });
    };//Init

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      this.attr.baseUrl = this.$node.data( 'url' );
      this.attr.title = this.$node.data( 'title' );
      this.Init();
    });
  });
});
