$(function() {
  $(document).on("click", ".cron_disabled", function() {
    var id=$(this).attr("id")
    var checked=$(this).prop("checked")?1:0;
    var CSRFToken=$('#CSRFSession').val();
    var _token=$('input[name="_token"]').val();

    $.ajax({
      url: url('crontab/disabled'),
      method: 'POST',
      data: 'id=' + id + '&checked=' + checked + '&CSRFToken=' + CSRFToken + '&_token=' + _token,
      success: function(){
        information('Modification enregistrée', 'highlight');
      },
      error: function(){
        information('Attention, la modification n\'a pas pu être enregistrée', 'error');
      }
    });
  });
});
