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
        url: url('ajax/workinghour-tables'),
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
        // Don't reload the form when
        // changing the agent on copy mode.
        if ($('input[name="copy"]').val()) {
            return false;
        }
        const queryString = window.location.search;
        document.location.href="/workinghour/add/" + this.value + queryString;
    });

    $("#select_number_of_weeks").change(function() {
        updateTables(this.value);
    });

});
