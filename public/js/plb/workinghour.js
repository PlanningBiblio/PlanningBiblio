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
            updateTables();
            return false;
        }
        const queryString = window.location.search;
        document.location.href="/workinghour/add/" + this.value + queryString;
    });

    $("#select_number_of_weeks").change(function() {
        updateTables(this.value);
    });

    $('.workingHoursRotationBtn').click(function() {
        const count = $('table[id^="tableau"]').length;
        const offset = (count - 1) * 7;
        const rotation = $(this).attr('data-rotation');

        var times = [];
        $('[name^="temps"]').each(function() {
            const name = $(this).attr('name');
            const indexes = name.match(/\d+/g);
            const value = $(this).val();
            const elem = [indexes[0], indexes[1], value];
            times.push(elem);
        });

        for(elem in times) {
            var index1 = parseInt(times[elem][0]);
            const index2 = parseInt(times[elem][1]);
            const value = times[elem][2];

            if (rotation == 'clockwise') {
                index1 = index1 >= offset ? (index1 - offset) : (index1 + 7);
            } else {
                index1 = index1 < 7 ? (index1 + offset) : (index1 - 7);
            }

            $('[name="temps[' + index1 + '][' + index2 + ']"]').val(value);
        }

        //$('[name^="temps"]').effect('highlight', null, 3000);
    });

//    $('.workingHoursRotationBtn').one('click', function() {
//        $('[name^="temps"]').effect('highlight', null, 3000);
//    });

});
