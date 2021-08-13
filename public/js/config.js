/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

@file public/js/config.js
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier JS de la page administration / Configuration
*/

function ldaptest(){
 
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
  
  var pos = $('#LDAP-Test').position();
  top1 = pos.top - 10;
  
  $(".CJInfo").remove();

  $.ajax({
    url: 'ldap/ajax.ldaptest.php',
    type: 'post',
    dataType: 'json',
    data: {filter: filter, host: host, idAttribute: idAttribute, password : password, port: port, protocol: protocol, rdn: rdn, suffix: suffix},
    success: function(result){
      if(result == 'ok'){
        CJInfo('Les paramètres LDAP sont corrects','success',top1);
      }else if(result == 'bind'){
        CJInfo('Les paramètres Protocol, RDN et/ou Password sont incorrects','error',top1);
      }else if(result == 'search'){
        CJInfo('Les paramètres Suffix, Filter et/ou ID-Attribute sont incorrects','error',top1);
      }else{
        CJInfo('Les paramètres LDAP Host et/ou Port sont incorrects','error',top1);
      }
    },
    error: function(){
      CJInfo('Impossible de vérifier les paramètres LDAP','error',top1);
    }
  });
}

function mail_config() {
  if (document.getElementById('Mail-IsMail-IsSMTP').value == 'IsMail') {
    document.getElementById('Mail-Hostname_tr').style.display = 'none';
    document.getElementById('Mail-Host_tr').style.display = 'none';
    document.getElementById('Mail-Port_tr').style.display = 'none';
    document.getElementById('Mail-SMTPSecure_tr').style.display = 'none';
    document.getElementById('Mail-SMTPAuth_tr').style.display = 'none';
    document.getElementById('Mail-Username_tr').style.display = 'none';
    document.getElementById('Mail-Password_tr').style.display = 'none';
  } else {
    document.getElementById('Mail-Hostname_tr').style.display = '';
    document.getElementById('Mail-Host_tr').style.display = '';
    document.getElementById('Mail-Port_tr').style.display = '';
    document.getElementById('Mail-SMTPSecure_tr').style.display = '';
    document.getElementById('Mail-SMTPAuth_tr').style.display = '';
    document.getElementById('Mail-Username_tr').style.display = '';
    document.getElementById('Mail-Password_tr').style.display = '';
  }
}

function mailtest(){
 
  var enabled = $('#Mail-IsEnabled').prop('checked');
  var mailSmtp = $('#Mail-IsMail-IsSMTP').val();
  var hostname = $('#Mail-Hostname').val();
  var host = $('#Mail-Host').val();
  var port = $('#Mail-Port').val();
  var secure = $('#Mail-SMTPSecure').val();
  var auth = $('#Mail-SMTPAuth').prop('checked') ? 1 : 0;
  var user = $('#Mail-Username').val();
  var password = $('#Mail-Password').val();
  var fromMail = $('#Mail-From').val();
  var fromName = $('#Mail-FromName').val();
  var signature = $('#Mail-Signature').val();
  var planning = $('#Mail-Planning').val().trim();
  
  var pos = $('#Mail-Test').position();
  top1 = pos.top - 10;
  
  $(".CJInfo").remove();
  
  if(enabled == 0){
    CJInfo("Le paramètre \"Mail-IsEnabled\" est d&eacute;sactiv&eacute;","error",top1,8000);
    return false;
  }

  if( !planning){
    CJInfo("Veuillez entrer une (ou plusieurs) adresse(s) valide(s) dans le champ \"Mail-Planning\"","error",top1,8000);
    return false;
  }

  $.ajax({
    url: 'ajax/mail-test',
    type: 'post',
    dataType: 'json',
    data: {mailSmtp: mailSmtp, hostanme: hostname, host: host, port: port, secure: secure, auth: auth, user: user, password: password, fromMail: fromMail, fromName: fromName, signature: signature, planning: planning},
    success: function(result){
      if(result == 'ok'){
        CJInfo('Le mail de test a été envoyé avec succès. Vérifiez votre messagerie.','success',top1,8000);
      }else if(result == 'socket'){
        CJInfo('Impossible de joindre le serveur de messagerie.','error',top1,8000);
      }else{
        CJInfo("Une erreur est survenue lors de l'envoi du mail.#BR#"+result,'error',top1,8000);
      }
    },
    error: function(result){
        CJInfo("Une erreur est survenue lors de l'envoi du mail.#BR#"+result.responseText,'error',top1,8000);
    }
  });

}