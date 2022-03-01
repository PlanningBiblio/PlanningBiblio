/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/conges/js/script.conges.js
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié <etienne.cavalie@unice.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des congés
*/

function afficheRefus(me){
  if(me.value=="-1" || me.value=="-2"){
    document.getElementById("tr_refus").style.display="";
  }
  else{
    document.getElementById("tr_refus").style.display="none";
  }
}

function adaptDisplay() {
  if (multipleAgentsSelected()) {
    $(".hideWhenMultipleAgents").hide();
  } else {
    $(".hideWhenMultipleAgents").show();
  }
}

function currentCredits() {
  if (multipleAgentsSelected()) {
    return;
  }

  perso_id = $('#selected_agent_id').val();

  $.ajax({
    url: url('ajax/current-credits'),
    data: { id: perso_id },
    dataType: 'json',
    type: 'get',
    async: false,
    success: function(credits){
      console.log(credits)
      $('#holiday_balance').text(credits.holiday_balance);
      $('#reliquat4').text(credits.holiday_balance);
      $('input[name="reliquat"]').val(credits.holiday_balance_decimal);

      $('#holiday_credit').text(credits.holiday_credit);
      $('#credit4').text(credits.holiday_credit);
      $('input[name="credit"]').val(credits.holiday_credit_decimal);

      $('#holiday_debit').text(credits.holiday_debit);
      $('#anticipation4').text(credits.holiday_debit);
      $('input[name="anticipation"]').val(credits.holiday_debit_decimal);
    },
    error: function(xhr, ajaxOptions, thrownError){
      information("Impossible de récupérer le compte de congés actuel.","error");
    },
  });
}

function calculCredit(){

  $("#erreurCalcul").val("false");
  $("#nbJours").text('');
  $("#nbHeures").text('');

  if( ! $('input[name=debut]').length) { return; }
  if (multipleAgentsSelected()) { return; }

  debut=document.form.elements["debut"].value;
  fin=document.form.elements["fin"].value;
  hre_debut=document.form.elements["hre_debut"].value;
  hre_fin=document.form.elements["hre_fin"].value;
  perso_id=$('#selected_agent_id').val();
  halfday = $('input[name="halfday"]').is(':checked') ? 1 : 0;
  conges_mode = $('#conges-mode').val();
  is_recover = $('#is-recover').val();
  conges_demi_journee = $('#conges-demi-journees')
  congesRecup = $('#conges-recup').val();

  if(!fin){
    fin=debut;
  }
  if(!debut){
    return;
  }

  hre_debut=hre_debut?hre_debut:"00:00:00";
  hre_fin=hre_fin?hre_fin:"23:59:59";

  data = {
    debut: debut,
    fin: fin,
    hre_debut: hre_debut,
    hre_fin: hre_fin,
    perso_id: perso_id,
    is_recover: is_recover
  };

  if (conges_mode == 'jours' && conges_demi_journee && halfday) {
    start_halfday = $('select[name="start_halfday"]').val();
    end_halfday = $('select[name="end_halfday"]').val();

    data['start_halfday'] = start_halfday;
    data['end_halfday'] = end_halfday;
  }

  $.ajax({
    url: url('ajax/holiday-credit'),
    data: data,
    dataType: "json",
    type: "get",
    async: false,
    success: function(result){
      var msg=result[0];
      if(result.error == true) {
        $("#erreurCalcul").val("true");
        document.form.elements["heures"].value=0;
        document.form.elements["minutes"].value=0;
        $("#nbHeures").text("0h00");
        $("#nbHeures").effect("highlight",null,3000);
        $("#nbJours").effect("highlight",null,3000);
        information("Aucun planning de présence enregistré pour cette période - calcul impossible.","error");
      } else {
        $("#JSInformation").remove();
        var balance_date = result.recover[0];  // Date de début, affichée pour le réajustement des crédits disponibles
        var balance = result.recover[1];       // Crédits de récupérations disponibles à la date choisie

        var balance_estimated = result.recover[4];       // Crédits de récupérations prévisionnels à la date choisie

        if(congesRecup == 0 && balance_estimated < 0 ){
          balance_estimated = 0;
        }

        if (congesRecup != 0) {
          $(".balance_tr").hide();
        }


        $('#recuperation').val(balance);
        $('.balance_date').text(dateFr(balance_date));
        $('#balance_before').text(heure4(balance));
        $('#balance2_before').text(heure4(balance_estimated));
        $("#recuperation_prev").val(balance_estimated);

        if (congesRecup == 0 || result.rest != 0) {
          $(".balance_tr").effect("highlight",null,4000);
        }

        if ($('#hours_per_day').val()) {
          var hours_per_day = $('#hours_per_day').val();

          if (balance != 0) {
            days= hours_to_days(balance, hours_per_day);
            $("#balance_before").append(days);
          }
          if (balance_estimated != 0) {
            days= hours_to_days(balance_estimated, hours_per_day);
            $("#balance2_before").append(days);
          }
        }

        document.form.elements["heures"].value = result.hours;
        document.form.elements["minutes"].value = result.minutes;

        $("#nbHeures").text(result.hr_hours);
        $("#nbJours").text(result.days);
        $("#nbHeures").effect("highlight",null,4000);
        $("#nbJours").effect("highlight",null,4000);

        $("#rest").val(0);
        $("#hr_rest").text('');
        $("#rest").parent().parent().hide();
        if (result.rest != 0) {
          if (result.rest > 0) {
            $("#hr_rest").text(result.hr_rest + ' créditée(s)');
          } else {
            $("#hr_rest").text(result.hr_rest + ' débitée(s)');
          }
          $("#rest").val(result.rest);
          $("#rest").parent().parent().show();
          $("#hr_rest").effect("highlight",null,4000);
        }
      }
    },
    error: function(xhr, ajaxOptions, thrownError){
      var congesMode = $('#conges-mode').val();

      if (congesMode == 'heures') {
        information("Impossible de calculer le nombre d'heures correspondant au congé demandé.","error");
      } else {
        information("Impossible de calculer le nombre de jours correspondant au congé demandé.","error");
      }
    },
  });
  calculRestes();
  calculRegul();
}

function calculRegul() {
  regul = $('#rest').val();
  if (regul != 0 && regul !== undefined) {
    recuperation = $('#recuperation').val();
    recuperation_prev = $('#recuperation_prev').val();

    if (Math.sign(regul) < 0) {
      regul = Math.abs(regul);
    } else {
      regul = -Math.abs(regul);
    }

    recuperation = recuperation - regul;
    recuperation_prev = recuperation_prev - regul;

    $('#recup4').text(heure4(recuperation));
    $('#balance2_after').text(heure4(recuperation_prev));
  }
}

function calculRestes(){
  heures=document.form.elements["heures"].value+"."+document.form.elements["minutes"].value;
  reliquat=document.form.elements["reliquat"].value;
  recuperation=document.form.elements["recuperation"].value;
  recuperation_prev = $('#recuperation_prev').val();
  credit=document.form.elements["credit"].value;
  anticipation=document.form.elements["anticipation"].value;
  debit=document.form.elements["debit"].value;

  heures = parseFloat(heures.replace(' ',''));
  reliquat = parseFloat(reliquat.replace(' ',''));
  recuperation = parseFloat(recuperation.replace(' ',''));
  credit = parseFloat(credit.replace(' ',''));
  anticipation = parseFloat(anticipation.replace(' ',''));

  var congesRecup = $('#conges-recup').val();
  var congesMode = $('#conges-mode').val();

  // Si les récupérations et les congés sont gérés de la même façon
  if(congesRecup == 0){
    
    // Calcul du reliquat après décompte
    reste=0;
    reliquat=reliquat-heures;
    if(reliquat<0){
      reste=-reliquat;
      reliquat=0;
    }

    reste2=0;
    // Calcul du crédit de récupération
    if(debit=="recuperation"){
      recuperation=recuperation-reste;
      recuperation_prev = recuperation_prev - reste;
      if(recuperation<0){
        reste2=-recuperation;
        recuperation=0;
      }

      if(recuperation_prev < 0){
        recuperation_prev = 0;
      }
    }
    
    // Calcul du crédit de congés
    else if(debit=="credit"){
      credit=credit-reste;
      if(credit<0){
        reste2=-credit;
        credit=0;
      }
    }
    
    // Si après tous les débits, il reste des heures, on débit le crédit restant
    reste3=0;
    if(reste2){
      if(debit=="recuperation"){
        credit=credit-reste2;
        if(credit<0){
          reste3=-credit;
          credit=0;
        }
      }
      else if(debit=="credit"){
        recuperation=recuperation-reste2;
        recuperation_prev = recuperation_prev - reste2;
        if(recuperation<0){
          reste3=-recuperation;
          recuperation=0;
        }
        if(recuperation_prev < 0){
          recuperation_prev = 0;
        }
      }
    }
    
    if(reste3){
      anticipation=parseFloat(anticipation)+reste3;
    }
  }

  // Si les récupérations et les congés ne sont pas gérés de la même façon
  else{
    // Calcul du crédit de récupération
    if(debit=="recuperation"){
      recuperation = recuperation - heures;
      recuperation_prev = recuperation_prev - heures;

      $('.recup-alert').remove();
      if(recuperation < 0){
        CJInfo("Le crédit de récupération ne peut pas être négatif.", "error", null, 5000, 'recup-alert');
        $(".balance_tr").effect("highlight",null,4000);
      }
    }

    // Calcul du crédit de congés
    else if(debit=="credit"){

      // Calcul du reliquat après décompte
      reste=0;
      reliquat=reliquat-heures;
      if(reliquat<0){
        reste=-reliquat;
        reliquat=0;
      }
        
      // Calcul du crédit de congés
      credit=credit-reste;
      if(credit<0){
        reste=-credit;
        credit=0;
      } else {
        reste = 0;
      }
      
      // Anticipation
      if(reste){
        anticipation=parseFloat(anticipation)+reste;
      }

    }
  }

  // Affichage
  $("#recup4").text(heure4(recuperation));
  $("#balance2_after").text(heure4(recuperation_prev));
  if (congesMode == 'jours') {
    day_reliquat = reliquat / 7;
    day_reliquat = Math.round(day_reliquat * 2) / 2;
    day_reliquat = day_reliquat > 1 ? day_reliquat + ' jours' : day_reliquat + ' jour';
    $("#reliquat4").text(day_reliquat);

    day_credit = credit / 7;
    day_credit = Math.round(day_credit * 2) / 2;
    day_credit = day_credit > 1 ? day_credit + ' jours' : day_credit + ' jour';
    $("#credit4").text(day_credit);

    day_anticipation = anticipation / 7;
    day_anticipation = Math.round(day_anticipation * 2) / 2;
    day_anticipation = day_anticipation > 1 ? day_anticipation + ' jours' : day_anticipation + ' jour';
    $("#anticipation4").text(day_anticipation);
  } else {
    $("#reliquat4").text(heure4(reliquat));
    $("#credit4").text(heure4(credit));
    $("#anticipation4").text(heure4(anticipation));
    if ($('#hours_per_day').val()) {
      var hours_per_day = $('#hours_per_day').val();
      if (reliquat != 0) {
        days= hours_to_days(reliquat, hours_per_day);
        $("#reliquat4").append(days);
      }
      if (credit != 0) {
        days= hours_to_days(credit, hours_per_day);
        $("#credit4").append(days);
      }
      if (anticipation != 0) {
        days= hours_to_days(anticipation, hours_per_day);
        $("#anticipation4").append(days);
      }
      if (recuperation != 0) {
        days= hours_to_days(recuperation, hours_per_day);
        $("#recup4").append(days);
      }
      if (recuperation_prev != 0) {
        days= hours_to_days(recuperation_prev, hours_per_day);
        $("#balance2_after").append(days);
      }
    }
  }
}

function googleCalendarIcon(){
  var debut=$("#debut").val();
  var debut_hre=$("#hre_debut_select").val();
  var fin=$("#fin").val();
  var fin_hre=$("#hre_fin_select").val();
  var agent=$("#agent").val();
  var location="";

  $("#google-calendar-div").html("");

  if(!debut){
    return false;
  }

  if($("select#perso_id").length>0){
    agent=$("select#perso_id").find(":selected").text();
    agent=sanitize_string(agent);
  }

  debut=debut.replace(/([0-9]*)\/([0-9]*)\/([0-9]*)/g,"$3$2$1");
  fin=fin?fin.replace(/([0-9]*)\/([0-9]*)\/([0-9]*)/g,"$3$2$1"):debut;
  
  debut_hre=debut_hre?debut_hre.replace(/:/g,""):"000000";
  fin_hre=fin_hre?fin_hre.replace(/:/g,""):"235959";

  debut=debut+"T"+debut_hre;
  fin=fin+"T"+fin_hre;

  var link="<a style='margin-left: 30px;' target='_blank' id='googleCalendarLink' title='Ajouter dans mon agenda Google' ";
  link+="href='https://www.google.com/calendar/event?action=TEMPLATE&hl=fr&text=Congés "+agent+"&dates="+debut+"/"+fin+"&location="+location+"&ctz=Europe%2FParis&amp;details='>";
  link+="<span class='pl-icon pl-icon-google-calendar'></span></a>";
  
  $("#google-calendar-div").append(link);
}

function hours_to_days(hours, hours_per_day) {
  days = hours / hours_per_day;
  days = Math.round(days * 100) / 100;
  days = days > 1 || days < -1 ? ' / ' + days + ' jours' : ' / ' + days + ' jour';
  return days;
}

function supprimeConges(retour){
  if(retour == undefined){
    retour = '/holiday/index';
  }

  conf=confirm("Etes-vous sûr(e) de vouloir supprimer ce congé ?");
  if(conf){
    $.ajax({
      url: url('ajax/holiday-delete'),
      type: "get",
      data: "id="+$("#id").val()+"&CSRFToken="+$("#CSRFSession").val(),
      success: function(){
        location.href = retour;
      },
      error: function(){
        information("Une erreur est survenue lors de la suppresion du congé.","error");
      }
    });
  }
}

function valideConges(){
  document.form.elements["valide"].value="1";
  document.form.submit();
}

function verifConges(){
  if($("#erreurCalcul").val()=="true"){
    information("Aucun planning de présence enregistré pour cette période - calcul impossible.","error");
    return false;
  }

  // ID des agents
  perso_ids=[];

  // Only one agent pre-selected
  if ($("#perso_id").length > 0) {
      perso_ids.push($("#perso_id").val());

  // Multiple agents
  } else if ($("select.agents_multiples").length > 0) {
      $(".perso_ids_hidden").each(function(){
        perso_ids.push($(this).val());
      });

  // Only one agent in a select list
  } else {
      perso_ids.push($("#perso_ids").val());
  }

  // Si aucun agent n'est sélectionné, on quitte en affichant "Veuillez sélectionner ..."
  if(perso_ids.length<1){
    CJInfo("Veuillez sélectionner un ou plusieurs agents","error");
    return false;
  }

  // Variable, convertion des dates au format YYYY-MM-DD
  var debut=dateFr($("#debut").val());
  var fin=$("#fin").val()?dateFr($("#fin").val()):debut;
  var hre_debut=$("#hre_debut_select").val();
  var hre_fin=$("#hre_fin_select").val();
  var id=$("#id").val();
  hre_debut=hre_debut?hre_debut:"00:00:00";
  hre_fin=hre_fin?hre_fin:"23:59:59";
  debut=debut+" "+hre_debut;
  fin=fin+" "+hre_fin;

  // Vérifions si les dates sont correctement saisies
  if($("#debut").val()==""){
    information("Veuillez choisir la date de début","error");
    return false;
  }

  // Vérifions si les dates sont cohérentes
  if (debut >= fin) {
    information("La date de fin doit être supérieure à la date de début","error");
    return false;
  }
  
  // Vérifions si le solde des récupérations n'est pas négatif
  var recuperation = parseFloat( $('#recup4').text().replace('h', '.') );
  var isRegularization = false;
  if ($("#rest").val()) {
    isRegularization = true;
  }
  if(recuperation < 0 && isRegularization == false) {
    $('.recup-alert').remove();
    $(".balance_tr").effect("highlight",null,4000);
    if ($('#validation').val() > 0) {
      CJInfo("Le crédit de récupération ne peut pas être négatif.", "error", null, 5000, 'recup-alert');
      return false;
    } else {
      if (!confirm("Attention!\nLe crédit de récupération ne peut pas être négatif.\nCette demande ne pourra pas être validée tant que le crédit restera insufisant.\nVoulez-vous continuer ?")) {
        return false;
      }
    }
  }

  // Vérifions si un autre congé a été demandé ou validé

  var result=$.ajax({
    url: url('ajax/holiday-absence-control'),
    type: "get",
    dataType: "json",
    data: {perso_ids: JSON.stringify(perso_ids), debut: debut, fin: fin, id: id, type:'holiday'},
    async: false,
    success: function(result){
      var valid = true;
      var admin = result['admin'];

      for (i in result['users']) {
        if (result['users'][i]['holiday'] != undefined) {
          CJInfo("Un congé a déjà été demandé par " + result['users'][i]['nom'] + " " + result['users'][i]['holiday'], "error");
          valid = false;
        }
      }

      if (result['planning_started'] && valid == true) {
        if (admin == true) {
          if (!confirm("Vous essayer d'enregistrer un congé sur des plannings en cours d'élaboration : "+result["planning_started"]+"\nVoulez-vous continuer ?")) {
            valid = false;
          }
        } else {
          CJInfo("Vous ne pouvez pas enregistrer d'absences pour les dates suivantes car les plannings sont en cours d'élaboration :#BR#"+result["planning_started"], "error");
          valid = false;
        }
      }

      // Contrôle si les agents apparaissent dans des plannings validés
      // Pour chaque agent
      if (valid == true) {
        var planning_validated = [];
        for (i in result['users']) {
          if(result['users'][i]["planning_validated"]){
            planning_validated.push("\n- " + result['users'][i]['nom'] + "\n-- " + result['users'][i]['planning_validated'].replace(';', "\n-- "));
          }
        }

        if (planning_validated.length) {
          if (planning_validated.length == 1) {
            var message = "L'agent suivant apparaît dans des plannings validés :";
            message += planning_validated[0];
          } else if (planning_validated.length > 1) {
            var message = "Les agents suivants apparaissent dans des plannings validés :";
            for (i in planning_validated) {
              message += planning_validated[i];
            }
          }

        if(admin == true){
          if(!confirm(message +"\nVoulez-vous continuer ?"))
            valid = false;
          } else {
            CJInfo("Vous ne pouvez pas enregsitrer de congés car " + message.replace("\n", "#BR#"), "error");
            valid = false;
          }
        }
      }

      if (valid == true) {
          // Vérifions les plannings de présence pour le calcul des crédits
          if (multipleAgentsSelected()) {
            var result=$.ajax({
                url: url('ajax/check-planning'),
                type: "post",
                data: "perso_ids="+JSON.stringify(perso_ids)+"&start="+debut+"&end="+fin,
                async: false,
                success: function(data){
                  if(data){
                    CJInfo(data, "error");
                  }else{
                    $("#form").submit();
                  }
                },
                error: function(){
                  CJInfo("Une erreur est survenue lors de l'enregistrement du congé","error");
                },
            });
          } else {
            $("#form").submit(); 
          }
      }
    },
    error: function(){
      information("Une erreur est survenue lors de l'enregistrement du congé","error");
    },
  });
}

function verifRecup(o){
  var perso_id=$("#agent").val();
  var retour=false;
  $.ajax({
    url: "conges/ajax.verifRecup.php",
    data: "date="+o.val()+"&perso_id="+perso_id,
    type: "get",
    async: false,
    success: function(result){
      if(result=="Demande"){
	o.addClass( "ui-state-error" );
	updateTips( "Une demande a déjà été enregistrée pour le "+o.val()+".", "error" );
      }else{
	retour=true;
      }
    },
    error: function(result){
      updateTips( "Une erreur s'est produite lors de la vérification des récupérations enregistrées", "error");
    }
  });
  return retour;
}


// Dialog, récupérations

function checkLength( o, n, min, max ) {
  if ( o.val().length > max || o.val().length < min ) {
    o.addClass( "ui-state-error" );
    updateTips( "Veuillez sélectionner le nombre d'heures.", "error");
  return false;
  } else {
    return true;
  }
}

function checkInt( o, n, min, max, tips ) {
  if ( o.val() > max || o.val() < min ) {
    o.addClass("ui-state-error");
    updateTips(tips, "error");
  return false;
  } else {
    return true;
  }
}

function checkDateAge( o, limit, n, tip ) {
  // Calcul de la différence entre aujourd'hui et la date demandée
  if(tip==undefined){
    tip=true;
  }
  var today=new Date();
  tmp=o.val().split("/");
  var d=new Date(tmp[2],tmp[1]-1,tmp[0]);
  diff=dateDiff(d,today);
  if(diff.day>limit){
    if(tip){
      o.addClass( "ui-state-error" );
      updateTips( n , "error");
    }
    return false;
  } else {
    return true;
  }
}

function checkSamedi( o, n ) {
  tmp=o.val().split("/");
  var d=new Date(tmp[2],tmp[1]-1,tmp[0]);
  if(d.getDay()!=6){
    o.addClass( "ui-state-error" );
    updateTips( n , "error");
    return false;
  } else {
    return true;
  }
}

function change_select_perso_ids(id){
  // Ajout des champs hidden permettant la validation des agents
  $('#perso_ids').before("<input type='hidden' name='perso_ids[]' value='"+id+"' id='hidden"+id+"' class='perso_ids_hidden'/>\n");

  $("#option"+id).hide();

  // Affichage des agents sélectionnés avec tri alphabétique
  affiche_perso_ul();
}

/**
 * Affichage des agents sélectionnés avec tri alphabétique
 */
function affiche_perso_ul(){
  var tab=[];
  $(".perso_ids_hidden").each(function(){
    var id=$(this).val();
    var name=$("#perso_ids option[value='"+id+"']").text();
    tab.push([name,id]);
  });

  // Only one agent selected. So set the
  // selected_agent_id to retrieve its
  // own data (workinghours, holiday credits...)
  $('#selected_agent_id').val('');
  if (tab.length == 1) {
    $('#selected_agent_id').val(tab[0][1]);
  }

  tab.sort(function (a, b) {
    return a[0].toLowerCase().localeCompare(b[0].toLowerCase());
  });

  $(".perso_ids_li").remove();

  // Réparti l'affichage des agents sélectionnés sur 5 colonnes de 10 (ou plus)
  var nb = Math.ceil(tab.length / 5);
  if(nb<10){
    nb=10;
  }

  for(i in tab){
    var style = tab[i][1] == $("#agent_id").val() ? ' style="font-weight:bolder;"' : '';
    var li="<li" + style + " id='li"+tab[i][1]+"' class='perso_ids_li' data-id='"+tab[i][1]+"'>"+tab[i][0];

    if( $('#admin').val() == 1 || tab[i][1] != $('#perso_id').val() ){
      li+="<span class='perso-drop' onclick='supprimeAgent("+tab[i][1]+");' ><span class='pl-icon pl-icon-drop'></span></span>";
    }

    li+="</li>\n";

    if(i < nb){
      $("#perso_ul1").append(li);
    } else if(i < (2*nb)){
      $("#perso_ul2").append(li);
    } else if(i < (3*nb)){
      $("#perso_ul3").append(li);
    } else if(i < (4*nb)){
      $("#perso_ul4").append(li);
    } else{
      $("#perso_ul5").append(li);
    }
  }

  update_validation_statuses();
}

function update_validation_statuses() {

  perso_ids = [];
  $('.perso_ids_li').each(function() {
    perso_ids.push($(this).data('id'));
  });

  absence_id = $('input[name="id"]').val();

  $('select[name="valide"] option[value="2"]').remove();
  $('select[name="valide"] option[value="-2"]').remove();
  $('select[name="valide"] option[value="1"]').remove();
  $('select[name="valide"] option[value="-1"]').remove();


  $.ajax({
    url: url('absence-statuses'),
    data: { ids: perso_ids, module: 'holiday', id: absence_id },
    dataType: "json",
    success: function(result){
      if (result.adminN1) {
        $('select[name="valide"]').append('<option value="2">Acceptée (En attente de validation hiérarchique)</option>');
        $('select[name="valide"]').append('<option value="-2">Refusé (En attente de validation hiérarchique)</option>');
      }

      if (result.adminN2) {
        $('select[name="valide"]').append('<option value="1">Accepté</option>');
        $('select[name="valide"]').append('<option value="-1">Refusé</option>');
      }

      state = $('input[name="absence_status"]').val()
      selected = 0;
      if (state == 'ACCEPTED_N1') {
        $('select[name="valide"] option[value="2"]').prop('selected', true);
      }
      if (state == 'REJECTED_N1') {
        $('select[name="valide"] option[value="-2"]').prop('selected', true);
      }
      if (state == 'ACCEPTED_N2') {
        $('select[name="valide"] option[value="1"]').prop('selected', true);
      }
      if (state == 'REJECTED_N2') {
        $('select[name="valide"] option[value="-1"]').prop('selected', true);
      }

      $('tr#validation-line').effect("highlight",null,2000);
    },
    error: function(xhr, ajaxOptions, thrownError) {
      information("Une erreur s'est produite lors de la mise à jour de la liste des statuts");
    }
  });
}

function multipleAgentsSelected() {
    if (!$('#agents-multiples').val()) {
      return false;
    }

    return $(".perso_ids_hidden").length == 1 ? false : true;
}

/**
 * supprimeAgent
 * supprime les agents de la sélection lors de l'ajout ou modification d'une absence
 */
function supprimeAgent(id){
  $("#option"+id).show();
  $("#li"+id).remove();
  $("#hidden"+id).remove();
  adaptDisplay();
  affiche_perso_ul();
  currentCredits();
  calculCredit();
}

function getAgentsBySites(sites) {
  var agents = null;
  $.ajax({
    url: url('ajax/agents-by-sites'),
    data: {sites: sites},
    dataType: "json",
    type: "get",
    async: false,
    success: function(result){
      agents = result;
    }
  });
  return agents;
}

function updateAgentsListBySites() {

    if ($("input#multisites").val()) {
        managed_sites = JSON.stringify($("input[name='selected_sites']").map(function(){
          return $(this).val();
        }).get());

        selected_sites = JSON.stringify($("input[name='selected_sites']:checked").map(function(){
          return $(this).val();
        }).get());
    } else {
        managed_sites = "[1]";
        selected_sites = "[1]";
    }

    managed_sites_agents = getAgentsBySites(managed_sites);
    selected_sites_agents = getAgentsBySites(selected_sites);
    selected_sites_agents = selected_sites_agents.map(x => x.id);

    options = '';

    // Check if multiple agents is allowed
    if ($('#perso_ids option[value="0"]').length > 0) {
        options += '<option value="0" selected="selected">-- Ajoutez un agent --</option>';
    }

    // Check if all agents is allowed
    if ($('#perso_ids option[value="tous"]').length > 0) {
        options += '<option value="tous">Tous les agents</option>';
    }

    selected_agent_id = $('#selected_agent_id').val();
    $.each(managed_sites_agents, function(index, value) {
        style = value.id == $("#agent_id").val() ? ' style="font-weight:bolder;"' : '';
        selected = value.id == selected_agent_id ? ' selected="selected"' : '';
        options += '<option' + style + selected + ' value="' + value.id + '" id="option' + value.id;
        options += '">' + value.nom + ' ' + value.prenom + '</option>';
    });

    $("#perso_ids").html(options);

    selected_agents = $(".perso_ids_hidden").map(function(){
        return $(this).val();
    }).get();

    $.each(managed_sites_agents, function(index, value) {
        // Check if not selected or not already added
        if ($.inArray(value.id, selected_sites_agents) == -1 || $.inArray(value.id, selected_agents) !== -1) {
            $("#option" + value.id).hide();
        }
    });

    calculCredit();
}

$(function(){
  $('.checkdate').on('change', function() {
    calculCredit();
  });

  $("input[name='selected_sites']").change(function() {
    updateAgentsListBySites();
  });

  $(".googleCalendarTrigger").change(function(){
    googleCalendarIcon();
  });
  $(".googleCalendarForm").ready(function(){
    googleCalendarIcon();
  });

  $("#perso_ids").change(function(){

    if ($('#perso_ids').hasClass('agents_multiples')) {
      // Variables
      var id=$(this).val();

      // Si sélection de "tous" dans le menu déroulant des agents, ajoute tous les id non-sélectionnés
      if(id == 'tous'){
	$("#perso_ids > option").each(function(){
	  var id = $(this).val();
	  if(id != 'tous' && id != 0 && $('#hidden'+id).length == 0 && $(this).css('display') != 'none'){
	    change_select_perso_ids(id);
	  }
	});

      } else {
	// Ajoute l'agent choisi dans la liste
	change_select_perso_ids(id);
      }

      adaptDisplay();

      // Réinitialise le menu déroulant
      $("#perso_ids").val(0);
    }

    currentCredits();
    calculCredit();

  });
});

$(document).ready(function() {
    updateAgentsListBySites();
    affiche_perso_ul();
});
