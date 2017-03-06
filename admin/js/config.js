/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : admin/js/config.js
Création : 6 mars 2017
Dernière modification : 6 mars 2017
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