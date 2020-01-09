function change_hidden(site, op) {
  login_id = $('#login_id').val();
  $.ajax({
    url: "planning/poste/ajax.hiddenSites.php",
    type: "post",
    dataType: "json",
    data: {site: site, login_id: login_id, op: op},
    success: function(result){
    },
    error: function(result){
    }
  });
}

$( document ).ready(function() {
  $('.hideSite').on('click', function() {
    site_id = $(this).data('site');
    site_tab = $('#planning_' + site_id);
    if (site_tab.is(':visible')) {
      site_tab.hide();
      change_hidden(site_id, 'add');
    } else {
      site_tab.show();
      change_hidden(site_id, 'remove');
    }
  });
});
