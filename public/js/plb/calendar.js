/**
Planning Biblio

@file public/js/calendar.js
@author Jérôme Combes <jerome@planningbiblio.fr>

@desc Javascript functions use to display calendars
*/

$(document).ready(function() {
  // Set the same width to all columns
  var width = 0;
  $('#tab_agenda th').each(function() {
    width = $(this).width() > width ? $(this).width() : width;
  });
  $('#tab_agenda th').each(function() {
    $(this).css('width', width);
  });

  // Formatting error messages
  errorHighlight($('.information'), 'error');
});
