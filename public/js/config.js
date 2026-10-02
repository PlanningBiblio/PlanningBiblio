/**
Description : Fichier JS des pages de configurations
*/

function ldaptest() {
  $('#alert-stack-top-center').remove();

  $.ajax({
    url: url('config/ldap-test'),
    type: 'post',
    dataType: 'json',
    data: {
      _token: $('input[name=_token]').val(),
      filter: $('#LDAP-Filter').val() || '(objectclass=inetorgperson)',
      host: $('#LDAP-Host').val(),
      idAttribute: $('#LDAP-ID-Attribute').val(),
      password : $('#LDAP-Password').val(),
      port: $('#LDAP-Port').val() || 389,
      protocol: $('#LDAP-Protocol').val(),
      rdn: $('#LDAP-RDN').val(),
      suffix: $('#LDAP-Suffix').val(),
    },
    success: function(result) {
      if (result == 'CSRF') {
        stackAlert(Translator.trans('The CSRF token is invalid. Please try to resubmit the form', {}, 'validators'), 'error');
      } else if (result == 'ok') {
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
  $('#alert-stack-top-center').remove();

  if(!$('#Mail-IsEnabled').prop('checked')) {
    stackAlert('Le paramètre "Mail-IsEnabled" est désactivé', 'error');
    return false;
  }

  if(!$('#Mail-Planning').val().trim()) {
    stackAlert('Veuillez entrer une (ou plusieurs) adresse(s) valide(s) dans le champ "Mail-Planning"', 'error');
    return false;
  }

  $.ajax({
    url: url('config/mail-test'),
    type: 'post',
    dataType: 'json',
    data: {
      _token: $('input[name=_token]').val(),
      mailSmtp: $('#Mail-IsMail-IsSMTP').val(),
      hostanme: $('#Mail-Hostname').val(),
      host: $('#Mail-Host').val(),
      port: $('#Mail-Port').val(),
      secure: $('#Mail-SMTPSecure').val(),
      autoTLS: $('#Mail-SMTPAutoTLS').prop('checked') ? 1 : 0,
      auth: $('#Mail-SMTPAuth').prop('checked') ? 1 : 0,
      user: $('#Mail-Username').val(),
      password: $('#Mail-Password').val(),
      fromMail: $('#Mail-From').val(),
      fromName: $('#Mail-FromName').val(),
      signature: $('#Mail-Signature').val(),
      planning: $('#Mail-Planning').val().trim(),
    },
    success: function(result) {
      if (result == 'CSRF') {
        stackAlert(Translator.trans('The CSRF token is invalid. Please try to resubmit the form', {}, 'validators'), 'error');
      } else if (result == 'ok') {
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
