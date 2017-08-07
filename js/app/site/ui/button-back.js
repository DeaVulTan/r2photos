// расположение блоков вплотную в 2 столбца (новости)
//
define([
  'flight/component',
  'jquery'
], function (
  component,
  $
) {

  "use strict";

  return component(function () {

    // Атрибуты.
    //
    this.attributes({
    });

////////////////////////////////////////////////////////////////////////////////

    // Инициализация компонента.
    //
    this.after('initialize', function () {
      this.$node.on({
        click: function(){
          window.history.back();
          return false;
        }
      });
    });

  });

});
