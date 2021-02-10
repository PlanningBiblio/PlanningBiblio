function updateCycles() {
    console.log("change");
    $.ajax({
        url: "/ajax/agent-sites",
        data: {id: 2},
        dataType: "json",
        type: "get",
        async: false,
        success: function(result) {
            console.log(result);
        },
        error: function(result) {
        }
  });

}

$(function(){
  $("document").ready(function(){
//    updateCycles();
    $("#perso_id").change(function() {
        updateCycles();
    });
  });
});
