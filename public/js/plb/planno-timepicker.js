plannoTimepickerParams = {
  timeFormat: 'HH:mm',
  defaultTime: '',
  dropdown: true,
  scrollbar: true,
  dynamic: true,
  change: function(date) {
    roundTimePiker(this);
    timePickerChange(date, this);
  }
};

(function( $ ) {
  $.fn.plannoTimepicker = function(params) {
    params = $.extend(plannoTimepickerParams, params);

    $(this).timepicker(params);

    $(this).attr('autocomplete', 'off');
  };
})( jQuery );

function timePickerChange(date, obj) {
  if ($(obj).hasClass('checkdate')) {
    dateChange(obj);
  }

  if ($(obj).hasClass('select')) {
    plHebdoCalculHeures($(obj),"");
    plHebdoChangeHiddenSelect();
  }

  if ($(obj).hasClass('select0')) {
    calculHeures($(obj),'','form','heures0',0);
  }

  if ($(obj).hasClass('select1')) {
    calculHeures($(obj),'','form','heures1',1);
  }

  if ($(obj).hasClass('select2')) {
    calculHeures($(obj),'','form','heures2',2);
  }

  if ($(obj).hasClass('select3')) {
    calculHeures($(obj),'','form','heures3',3);
  }

  if ($(obj).hasClass('framework-timepicker')) {
    change_horaires($(obj));
  }
}

function roundTimePiker(obj) {
  var element = $(obj), text;
  var tp = element.timepicker();

  time = $(obj).val();

  var times = time.split(':');
  var hours = parseInt(times[0]);
  var minutes = parseInt(times[1]);
  var rounded = Math.round(minutes / tp.options.granularity) * tp.options.granularity;

  if (rounded == 60) {
    rounded = 0;
    hours++;
  }

  if (hours > tp.options.maxHour) {
    hours = tp.options.maxHour;
  }

  if (hours < tp.options.minHour) {
    hours = tp.options.minHour;
  }

  $(obj).val(formatHHmm(hours, rounded));
}

function formatHHmm(hour, minute) {
  if (hour < 10) {
    hour = '0' + hour;
  }

  if (minute < 10) {
    minute = '0' + minute;
  }

  return hour + ':' + minute;
}

function setTimePickerStep(granularity) {
  if (granularity == 1 || granularity == 15 || granularity == 5) {
    return 30;
  }

  return granularity;
}
