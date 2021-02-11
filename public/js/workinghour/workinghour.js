function updateCycles() {
    $.ajax({
        url: "/ajax/agent-distinct-sites-cycles",
        data: {id: $("#perso_id").val()},
        dataType: "json",
        type: "get",
        async: false,
        success: function(result) {
            if (result.length > 1) {
                var cycle_select = '';
                result.forEach(function (cycle, index) {
                   cycle_select += '<option value="' + cycle + '">' + cycle + '</option>'; 
                });
                $("#number_of_weeks").html(cycle_select);
            } else {

            }
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
  });
});
