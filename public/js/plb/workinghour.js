function updateTables(selected_weeks) {

    var weeks;
    if (selected_weeks) {
        weeks = selected_weeks;
    } else if ($("#this_number_of_weeks").val()) {
        weeks = $("#this_number_of_weeks").val();
    }  else {
        weeks = $("#number_of_weeks").val();
    }
    $('#select_number_of_weeks').val(weeks);

    $.ajax({
        url: "/ajax/workinghour-tables",
        data: {weeks: weeks, perso_id: $("#perso_id").val(), ph_id: $("#id").val()},
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

    $("#select_number_of_weeks").change(function() {
        updateTables(this.value);
    });

});
