function updateCycles() {
    $.ajax({
        url: "/ajax/agent-distinct-sites-cycles",
        data: {id: $("#perso_id").val()},
        dataType: "json",
        type: "get",
        async: false,
        success: function(result) {
            var cycle_select = '';
            var ph_number_of_weeks = $("#ph_number_of_weeks").val();

            if (result.length == 1) {
                cycle_select += "<input type='hidden' name='number_of_weeks' id='number_of_weeks' value='" + result[0] + "' />";
            } else {
                cycle_select += "<p><label for='number_of_weeks'>Cycle (en nombre de semaines)</label>";
                cycle_select += "<select name='number_of_weeks' id='number_of_weeks'>";
                result.forEach(function (cycle, index) {
                   cycle_select += '<option value="' + cycle + '"';
                   if (ph_number_of_weeks == cycle) {
                        cycle_select += ' selected="selected"';
                   }
                   cycle_select += '>' + cycle + '</option>'; 
                });
                cycle_select += "</select></p>";
            }
            $("#cycle").html(cycle_select);
            updateTables();
        },
        error: function(result) {
        }
  });
}

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

}

$(function(){
  $("document").ready(function(){
    updateCycles();
    $("#perso_id").change(function() {
        updateCycles();
    });
    $("#number_of_weeks").change(function() {
        updateTables();
    });

  });
});
