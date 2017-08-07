//Wrapped in an outer function to preserve global this
(function (root) { var amdExports; define(['jquery','jquery-ui','jquery-ui/timepicker'], function () { (function () {

jQuery(function($){
  $.timepicker.regional['ru'] = {
    timeOnlyTitle: 'Выберите время',
    timeText: 'Время',
    hourText: 'Часы',
    minuteText: 'Минуты',
    secondText: 'Секунды',
    millisecText: 'Миллисекунды',
    timezoneText: 'Часовой пояс',
    currentText: 'Сейчас',
    closeText: 'Закрыть',
    timeFormat: 'HH:mm',
    amNames: ['AM', 'A'],
    pmNames: ['PM', 'P'],
    isRTL: false
  };
  $.timepicker.setDefaults($.timepicker.regional['ru']);
});



}.call(root));
    return amdExports;
}); }(this));
