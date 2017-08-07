// Код, выполняемый на всех страницах приложения.
//
define([

  'jquery',
  'jquery/colorbox',
  'lib/datepicker'

], function ($) {

  $(function() {
    menu();
    gallery();
  });


  // Инициализация меню.
  //
  function menu() {
    var $menu    = $('.mainmenu');

    $menu.on('mouseenter', function () {
      $(this).find('.submenu').show();
    })
    .on('mouseleave', function () {
      $(this).find('.submenu').hide();
    });    
  }

  // Настройка галерей с colorbox.
  //
  function gallery() {
    var $gallery = $("#gallery a[rel=gal]");
    if ($gallery.length) {
      $gallery.colorbox({ rel: 'gal' });
    }
  }

});
