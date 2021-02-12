function updateCycles() {
    $.ajax({
        url: "/ajax/agent-distinct-sites-cycles",
        data: {id: $("#perso_id").val()},
        dataType: "json",
        type: "get",
        async: false,
        success: function(result) {
                var cycle_select = '';
                result.forEach(function (cycle, index) {
                   cycle_select += '<option value="' + cycle + '">' + cycle + '</option>'; 
                });
                $("#number_of_weeks").html(cycle_select);

            updateTables();
        },
        error: function(result) {
        }
  });
}

function updateTables() {
    console.log("updateTables");
    console.log("ph_id " + $("#id").val());
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
