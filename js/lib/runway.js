// Простейший загрузчик компонент.
//
// Выполняет загрузку и привязку компонент на основании значений специальных
// дата-атрибутов.
//
define([
  'require',
  'flight/component',
  'jquery'
], function (require, component, $) {

  "use strict";

  // Определяет сервис.
  //
  return component(runway);

  // Сервис создания компонент.
  //
  function runway() {

    // Загружает и создает компоненты по data-атрибуту component.
    //
    // Загрузка выполняется для всего документа, или для отдельного фрагмент,
    // если он передан в качестве параметра события data.fragment.
    //
    this.alterFragment = function (event, data) {
      // Определяем обрабатываемый фрагмент, если не указан — используем весь
      // документ:
      var fragment = (data && data.fragment) ? data.fragment : document;

      // Находим все элементы, для которых установлен атрибут `data-component`
      // Для каждого такого элемента пробуем определить параметры создания
      // компонента (атрибут `data-component-attributes'):
      $(fragment).find('[data-component]').each( function () {

        var $this,      // - jquery-объект для элемента компонента
            module,     // - имя AMD-модуля компонента
            attrs;      // - параметры компонента.

        $this = $(this);

        // Используем $.data(), так как `data-component-attributes` использует
        // JSON.
        module = $this.data('component');
        attrs = $this.data('componentAttributes') || {};

        // Удаляем атрибуты у найденных элементов, чтобы предотвратить повторное
        // создание:
        this.removeAttribute('data-component');
        this.removeAttribute('data-component-attributes');

        // Загружаем необходимый модуль и привязываем компонент к элементу
        // документа после загрузки:
        require([module], function (component) {
          component.attachTo($this, attrs);
        });
      });
    };

    // Выполняет инициализацию компонента.
    //
    this.after('initialize', function () {

      // Устанавливаем сервисы, если они указаны в атрибутах:
      if (this.attr.services) {
        $.each(this.attr.services, function () {

          var service,              // - компонент сервиса;
              attrs;                // - атрибуты компонента сервиса.

          if ($.isArray(this)) {    // Сервис в виде массива:
            service = this[0];      // - первый элемент компонент сервиса;
            attrs = this[1] || {};  // - второй — атрибуты компонента.
          } else {
            service = this;         // Сервис в виде собственно объекта сервиса
            attrs = {};             // без атрибутов.
          }

          // Привязываем сервис к документу.
          service.attachTo(document, attrs);

        });
      }

      // Устанавливаем обработчик события `alter`...
      this.on('document:request-alter', this.alterFragment);

      // ... и генерируем это событие.
      this.trigger('document:request-alter');
    });
  }
});
