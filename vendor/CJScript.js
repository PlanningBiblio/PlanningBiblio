/* Divers scripts JS
Licence GNU/GPL (version 2 et au dela)

Fichier : CJScript.js
Création : mars 2015
Dernière modification : 23 juillet 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr
*/

function CJDataTableHideRow(selector){
  // (.hide mieux que .remove car si .remove, la ligne réapparait lors de l'utilisation des tris
  $(selector).hide();
  var table=$(selector).closest("table");
  
  // On refait le zebra
  table.find("tr").removeClass("odd");
  table.find("tr").removeClass("even");
  
  // Lignes paires et impaires : on ne traite que les lignes visibles
  var classe="odd";
  table.find("tr:visible").each(function(){
    classe=classe=="odd"?"even":"odd";
    $(this).addClass(classe);
  });
}

function CJDataTableStripe() {
  // On refait le zebra, à chaque fois que le tableau est redessiné.
  // Utile en cas de suppression de ligne et d'utilisation du filtre et des tris
  $(this).find("tr").removeClass("odd");
  $(this).find("tr").removeClass("even");
  
  // Lignes paires et impaires : on ne traite que les lignes visibles
  var classe="odd";
  (this).find("tr:visible").each(function(){
    classe=classe=="odd"?"even":"odd";
    $(this).addClass(classe);
  });
}

function CJErrorHighlight(e, type, icon) {
    if (!icon) {
        if (type === 'highlight') {
            icon = 'ui-icon-info';
        } else {
            icon = 'ui-icon-alert';
        }
    }
    return e.each(function() {
        $(this).addClass('ui-widget');
        var alertHtml = '<div class="ui-state-' + type + ' ui-corner-all" style="padding:0 .7em;">';
        alertHtml += '<p style="text-align:center;">';
        alertHtml += '<span class="ui-icon ' + icon + '" style="float:left;margin-right: .3em;"></span>';
        alertHtml += $(this).html();
        alertHtml += '</p>';
        alertHtml += '</div>';

        $(this).html(alertHtml);
    });
}

function CJFileExists(url){
  $.ajax({
    url: url,
    type:'HEAD',
    async: false,
    error: function(retour){
        return false;
    },
    success: function(retour){
        return true;
    }
  });
}

/**
 * @function CJInfo
 * Affiche des messages d'erreur ou d'information en haut de l'application
 * @param string message : message à afficher, utiliser #BR# pour les sauts de lignes
 * @param string type : type de message, valeurs = success ou error
 * @param int top : position haute du message en pixel, default=82
 * @param int time : temps d'affichage en milisecondes, default=8000
 * @param string myClass : permet d'attribuer une classe au div affichant le message pour agir dessus à postériori (ex : $(".myClass").remove(); )
 */
function CJInfo(message,type,top,time,myClass){
  if(type==undefined || type=="success"){
  	type="highlight";
  }

  if(top==undefined){
    top=82;
  }
  
  if(time==undefined){
    time=8000;
  }

  if(myClass==undefined){
    myClass=null;
  }

  if(typeof(timeoutJSInfo)!== "undefined"){
    window.clearTimeout(timeoutCJInfo);
  }

  var id=1;
  $(".CJInfo").each(function(){
    id=$(this).attr("data-id")>=id?($(this).attr("data-id")+1):id;
    top=$(this).position().top+$(this).height();
  });
  
  message=message.replace(/#BR#/g,"<br/>");

  $("body").append("<div class='CJInfo "+myClass+"' id='CJInfo"+id+"' data-id='"+id+"'>"+message+"</div>");
  CJErrorHighlight($("#CJInfo"+id),type);
  CJPosition($("#CJInfo"+id),top,"center");
  timeoutCJInfo=window.setTimeout(function(){
  		var height=$("#CJInfo"+id).height();
  		$("#CJInfo"+id).remove();
  		$(".CJInfo").each(function(){
  			var top=$(this).position().top-height;
  			$(this).css("top",top);
  		});
  	},time);
}

function CJPosition(object,top,left){
  object.css("position","absolute");
  object.css("z-index",10);
  object.css("top",top);
  if(left=="center"){
    left=($(window).width()-object.width())/2;
    object.css("left",left);
  }
}

// Fonction JQuery
$(function(){
  // DataTables
  /*
  Les tableaux ayant la classe CJDataTable seront transformés en DataTable
  Les paramètres suivant peuvent leur être transmis via les classes et les attributs data-
  
  Sur le tableau (balise <table>), les attributs suivants :
  - data-sort : tri par défaut, doit être une chaine JSON du type [[0,"asc"],[1,"asc"]]. Valeur par défaut [[0,"asc"]]
  - data-stateSave : garde en mémoire l'état du tableau (tris, recherches). Valeurs : 0, false, 1 ou true. Valeur par défaut = true
  - data-length : nombre d'éléments affichés. Par défaut : 25
  
  Sur les balises th de l'entête, les classes suivantes permettent de définir le type de données contenues dans les cellules 
  pour trier correctement les colonnes :
  - dataTableNoSort : La colonne ne sera pas triable
  - dataTableDateFR : La colonne contient des dates au format JJ-MM-AAAA [HH:mm:ss]. 
      Si seule l'heure est affichée, le tri considère que la date est celle du jour
  - dataTableDateFR-fin : La colonne des dates de fin
  - dataTableHeureFR : La colonne contient des heures au format HH:mm[:ss]
  */
  
  $(".CJDataTable").each(function(){

    // Tri des colonnes en fonction des classes des th
    var aoCol=[];
    
    // Variables tr2 utilisées si 2 lignes en entête. tr2 = 2eme ligne
    var tr2=null;
    if($(this).find("thead tr").length==2){
      tr2=$(this).find("thead tr:nth-child(2)");
      tr2th=tr2.find("th");
      tr2thNb=tr2th.length;
      tr2Index=1;
    }

    $(this).find("thead tr:first th").each(function(){
      
      var th=[$(this)];
      
      // Si colspan et 2 lignes en entête, on se base sur la 2ème ligne
      if($(this).attr("colspan") && $(this).attr("colspan")>1 && tr2){
	th=new Array();
	for(i=0;i<$(this).attr("colspan");i++){
	  th.push(tr2.find("th:nth-child("+tr2Index+")"));
	  tr2Index++;
	}
      }

      for(i in th){
	// Par défault, tri basic
	if(th[i].attr("class")==undefined){
	  aoCol.push({"bSortable":true});
	}
	// si date
	else if(th[i].hasClass("dataTableDate")){
	  aoCol.push({"sType": "date"});
	}
	// si date FR
	else if(th[i].hasClass("dataTableDateFR")){
	  aoCol.push({"sType": "date-fr"});
	}
	// si date FR Fin
	else if(th[i].hasClass("dataTableDateFR-fin")){
	  aoCol.push({"sType": "date-fr-fin"});
	}
	// si heures fr (00h00)
	else if(th[i].hasClass("dataTableHeureFR")){
	  aoCol.push({"sType": "heure-fr"});
	}
	// si pas de tri
	else if(th[i].hasClass("dataTableNoSort")){
	  aoCol.push({"bSortable":false});
	}
	// Par défaut (encore) : tri basic
	else{
	  aoCol.push({"bSortable":true});
	}
      }
    });

    // Tri au chargement du tableau
    // Par défaut : 1ère colonne
    var sort=[[0,"asc"]];
    
    // Si le tableau à l'attribut data-sort, on récupère sa valeur
    if($(this).attr("data-sort")){
      var sort=JSON.parse($(this).attr("data-sort"));
    }
    
    // Taille du tableau par défaut
    var tableLength=25;
    if($(this).attr("data-length")){
      tableLength=$(this).attr("data-length")
    }

    // save state ?
    var saveState=true;
    if($(this).attr("data-stateSave") && ($(this).attr("data-stateSave")=="false" || $(this).attr("data-stateSave")=="0")){
      var saveState=false;
    }

    // Colonnes fixes
    var scollX=$(this).attr("data-fixedColumns")?"100%":"";
    
    // On applique le DataTable
    var CJDataTable=$(this).DataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": saveState,
      "aLengthMenu" : [[10,25,50,75,100,-1],[10,25,50,75,100,"All"]],
      "iDisplayLength" : tableLength,
      "aaSorting" : sort,
      "aoColumns" : aoCol,
      "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"},
      "sScrollX": scollX,
      "sDom": '<"H"lfr>t<"F"ip>T',
      "oTableTools": {
	"sSwfPath" : "vendor/DataTables-1.10.4/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
	"aButtons": [
	  {
	    "sExtends": "xls",
	    "sButtonText": "Excel",
	  },
	  {
	    "sExtends": "csv",
	    "sButtonText": "CSV",
	  },
	  {
	    "sExtends": "pdf",
	    "sButtonText": "PDF",
	  },
	  {
	    "sExtends": "print",
	    "sButtonText": "Imprimer",
	  },
	]
      },
      // On refait le zebra, à chaque fois que le tableau est redessiné.
      // Utile en cas de suppression de ligne et d'utilisation du filtre et des tris
      "fnDrawCallback": CJDataTableStripe,
    });
    
    // Colonnes fixes
    if($(this).attr("data-fixedColumns")){
      var nb=$(this).attr("data-fixedColumns");
      new $.fn.dataTable.FixedColumns(CJDataTable, init={"iLeftColumns" : nb});
    }
  });

   // Check all checkboxes 
   $(".CJCheckAll").click(function(){
    $(this).closest("table").find("td input[type=checkbox]:visible").each(function(){
      $(this).click();
    });
  });
  
});
