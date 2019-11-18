function resize_td() {
  body_width = $(window).width() - 80;
  td_width = Math.round(body_width / 5);

  $('.statedweek-table td').width(Math.round(body_width / 5) + 'px');
}

$( document ).ready(function() {
  resize_td();

  $( window ).resize(function() {
    resize_td();
  });

  $("#pl-calendar").change(function(){
    var date = dateFr($(this).val());
    window.location.href="?date="+date;
  });

  $('.statedweek-table td').bind("contextmenu", function (event) {
    event.preventDefault();
    $(".custom-menu").finish().toggle(100).

    css({
        top: event.pageY + "px",
        left: event.pageX + "px"
    });

    $(document).bind("mousedown", function (e) {
      if (!$(e.target).parents(".custom-menu").length > 0) {
        $(".custom-menu").hide(100);
      }
    });
  });
});
