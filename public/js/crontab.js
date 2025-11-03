/*
 * Description :
 * Script gérant la sélection d’un cron command
 * et le remplissage automatique des champs du formulaire.
 */
$(function() {

  var $commandSelect = $("#command_id");
  var $descText = $("#command_description");
  
  var $inputMin = $("input[name='min']");
  var $inputHour = $("input[name='hour']");
  var $inputDom = $("input[name='dom']");
  var $inputMon = $("input[name='mon']");
  var $inputDow = $("input[name='dow']");

  // when command selection changes
  $commandSelect.change(function() {
    var id = $(this).val();

    //Default case: clear fields
    if (id === "0" || id === undefined) {
      $descText.text("");
      $inputMin.val("");
      $inputHour.val("");
      $inputDom.val("");
      $inputMon.val("");
      $inputDow.val("");
      return;
    }

    $.ajax({
      url: "/crontab/info/" + id,
      type: "GET",
      dataType: "json",
      success: function(result) {
        if (result.error) {
          $descText.html("Erreur : " + result.error);
          return;
        }

        var desc = result.description ? result.description : "(Aucune description)";
        $descText.html(desc);

        $inputMin.val(result.m || "");
        $inputHour.val(result.h || "");
        $inputDom.val(result.dom || "");
        $inputMon.val(result.mon || "");
        $inputDow.val(result.dow || "");

      },
      error: function() {
        $descText.html("Erreur AJAX" );
      }
    });
  });

  $(document).on("click", ".cron_disabled", function() {
    var id=$(this).attr("id")
    var checked=$(this).prop("checked")?1:0;
    var CSRFToken=$('#CSRFSession').val();

    $.ajax({
      url: url('crontab/disabled'),
      method: 'POST',
      data: 'id=' + id + '&checked=' + checked + '&CSRFToken=' + CSRFToken,
      success: function(){
        information('Modification enregistrée', 'highlight');
      },
      error: function(){
        information('Attention, la modification n\'a pas pu être enregistrée', 'error');
      }
    });
  });
});
