function workingHourSettingDelete(id) {
  var message = 'Êtes vous sûr de vouloir supprimer ce cycle ?\n\n';
  message += 'Attention !\n\n';
  message += 'La suppression des cycles peut avoir des conséquences grâves sur la rotation des heures de présence, ';
  message += 'sur les plannings passés et à venir, ainsi que sur les statistiques, les calculs des quotas ';
  message += 'et les calculs des temps d\'absences.\n\n';
  message += 'La suppression des cycles passés est fortement déconseillée.\n\n';

  if (confirm(message)) {
    $('#delete-id').val(id);
    $('#delete-form').submit();
  }
}
