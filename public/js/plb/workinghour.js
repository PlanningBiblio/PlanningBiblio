function updateTables() {
    $.ajax({
        url: "/ajax/workinghour-tables",
        data: {weeks: $("#number_of_weeks").val(), perso_id: $("#perso_id").val(), ph_id: $("#id").val()},
        dataType: "html",
        type: "get",
        async: false,
        success: function(result) {
            $("#workinghour_tables").html(result);
        },
        error: function(result) {
        }
  });
  plHebdoCalculHeures2();
  plHebdoMemePlanning();
}

$(function(){
    $("document").ready(function(){
        updateTables();
    });

    $("#perso_id").change(function() {
        updateTables();
    });
});
