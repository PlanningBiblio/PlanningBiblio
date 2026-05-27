/**
Description : Fichier JS des pages de configurations
*/

function ldaptest() {
  var filter = $('#LDAP-Filter').val();
  var host = $('#LDAP-Host').val();
  var idAttribute = $('#LDAP-ID-Attribute').val();
  var password = $('#LDAP-Password').val();
  var port = $('#LDAP-Port').val();
  var protocol = $('#LDAP-Protocol').val();
  var rdn = $('#LDAP-RDN').val();
  var suffix = $('#LDAP-Suffix').val();

  if(port == ''){
    port = '389';
  }

  if(filter == ''){
    filter = '(objectclass=inetorgperson)';
  }


  $('#alert-stack-top-center').remove();

  $.ajax({
    url: url('config/ldap-test'),
    type: 'post',
    dataType: 'json',
    data: {filter: filter, host: host, idAttribute: idAttribute, password : password, port: port, protocol: protocol, rdn: rdn, suffix: suffix},
    success: function(result) {
      if (result == 'ok') {
        stackAlert('Les paramètres LDAP sont corrects');
      } else if (result == 'bind') {
        stackAlert('Les paramètres Protocol, RDN et/ou Password sont incorrects', 'error');
      } else if (result == 'search') {
        stackAlert('Les paramètres Suffix, Filter et/ou ID-Attribute sont incorrects', 'error');
      } else {
        stackAlert('Les paramètres LDAP Host et/ou Port sont incorrects', 'error');
      }
    },
    error: function() {
      stackAlert('Impossible de vérifier les paramètres LDAP', 'error');
    }
  });
}

function mail_config() {
  if ($('#Mail-IsMail-IsSMTP').length == 0) {
    return;
  }

  if ($('#Mail-IsMail-IsSMTP').val() == 'IsMail') {
    $('#Mail-Hostname_tr').hide();
    $('#Mail-Host_tr').hide();
    $('#Mail-Port_tr').hide();
    $('#Mail-SMTPSecure_tr').hide();
    $('#Mail-SMTPAuth_tr').hide();
    $('#Mail-SMTPAutoTLS_tr').hide();
    $('#Mail-Username_tr').hide();
    $('#Mail-Password_tr').hide();
  } else {
    $('#Mail-Hostname_tr').show();
    $('#Mail-Host_tr').show();
    $('#Mail-Port_tr').show();
    $('#Mail-SMTPSecure_tr').show();
    $('#Mail-SMTPAutoTLS_tr').show();
    $('#Mail-SMTPAuth_tr').show();
    $('#Mail-Username_tr').show();
    $('#Mail-Password_tr').show();
  }
}

function mailtest() {
  var enabled = $('#Mail-IsEnabled').prop('checked');
  var mailSmtp = $('#Mail-IsMail-IsSMTP').val();
  var hostname = $('#Mail-Hostname').val();
  var host = $('#Mail-Host').val();
  var port = $('#Mail-Port').val();
  var secure = $('#Mail-SMTPSecure').val();
  var autoTLS = $('#Mail-SMTPAutoTLS').prop('checked') ? 1 : 0;
  var auth = $('#Mail-SMTPAuth').prop('checked') ? 1 : 0;
  var user = $('#Mail-Username').val();
  var password = $('#Mail-Password').val();
  var fromMail = $('#Mail-From').val();
  var fromName = $('#Mail-FromName').val();
  var signature = $('#Mail-Signature').val();
  var planning = $('#Mail-Planning').val().trim();


  $('#alert-stack-top-center').remove();

  if(enabled == 0) {
    stackAlert('Le paramètre "Mail-IsEnabled" est désactivé', 'error');
    return false;
  }

  if( !planning) {
    stackAlert('Veuillez entrer une (ou plusieurs) adresse(s) valide(s) dans le champ "Mail-Planning"', 'error');
    return false;
  }

  var data = {
    mailSmtp: mailSmtp,
    hostanme: hostname,
    host: host,
    port: port,
    secure: secure,
    autoTLS: autoTLS,
    auth: auth,
    user: user,
    password: password,
    fromMail: fromMail,
    fromName: fromName,
    signature: signature,
    planning: planning,
  }

  $.ajax({
    url: url('ajax/mail-test'),
    type: 'post',
    dataType: 'json',
    data: data,
    success: function(result) {
      if (result == 'ok') {
        stackAlert('Le mail de test a été envoyé avec succès. Vérifiez votre messagerie.');
      } else if (result == 'socket') {
        stackAlert('Impossible de joindre le serveur de messagerie.', 'error');
      } else {
        stackAlert('Une erreur est survenue lors de l\'envoi du mail.\n' + result, 'error');
      }
    },
    error: function(result){
        stackAlert('Une erreur est survenue lors de l\'envoi du mail.\n' + result.responseText, 'error');
    }
  });
}

function nb_week_reset() {
  var previous_nb_semaine = $('#nb-week-modal').data('previous_nb_semaine');
  $('#nb_semaine option[value="' + previous_nb_semaine + '"]').prop('selected', true);
}

$( document ).ready(function() {
  previous_nb_semaine = $('#nb_semaine option:selected').val();
  mail_config();

  $('#Conges-Mode, #Conges-Recuperations').on('change', function() {
    conges_mode = $('#Conges-Mode').val();
    conges_recuperations = $('#Conges-Recuperations').val();
    if (conges_mode == 'jours' && conges_recuperations == 0) {
      $('#holiday-policy-modal').modal('show');
      if ($(this)[0] == $('#Conges-Recuperations')[0]) {
        $('#cancel-mode').hide();
      }
      else {
        $('#cancel-mode').show();
      }
    }
  });

  $('#nb_semaine, #PlanningHebdo').on('change', function() {
    nb_semaine = $('#nb_semaine').val();
    planningHebdo = $('#PlanningHebdo').is(':checked');
    if (nb_semaine > 3 && !planningHebdo) {
      $('#nb-week-modal').data('previous_nb_semaine', previous_nb_semaine).modal('show');
      if ($(this)[0] == $('#PlanningHebdo')[0]) {
        $('#cancel-week').hide();
      }
      else {
        $('#cancel-week').show();
      }
      return;
    }
    previous_nb_semaine = nb_semaine;
  });

  $('#Auth-PasswordLength').on('change', function(e) {
    password_length = $('#Auth-PasswordLength').val();
    if (!int_validation(password_length) || password_length < 8) {
      $('#password-length-modal').modal('show');
    }
  });

  $('#Mail-IsMail-IsSMTP').on('change', function() {
    mail_config();
  });

});
