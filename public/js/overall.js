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
  $('.hideSite').each(function() {
    site_id = $(this).data('site');
    site_tab = $('#planning_' + site_id);
    site_name = $(this).data('site-name');
    if (site_tab.is(':visible')) {
      $(this).prop('title', 'Masquer le site ' + site_name);
    } else {
      $(this).prop('title', 'Afficher le site ' + site_name);
    }
  });

  $("#pl-calendar").change(function(){
    var date = dateFr($(this).val());
    window.location.href="index.php?page=planning/poste/overall.php&date="+date;
  });

  $('.hideSite').on('click', function() {
    site_id = $(this).data('site');
    site_tab = $('#planning_' + site_id);
    site_name = $(this).data('site-name');
    if (site_tab.is(':visible')) {
      site_tab.hide();
      $(this).prop('title', 'Afficher le site ' + site_name);
      change_hidden(site_id, 'add');
    } else {
      site_tab.show();
      $(this).prop('title', 'Masquer le site ' + site_name);
      change_hidden(site_id, 'remove');
    }
  });
});
