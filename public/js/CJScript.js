/**
Divers scripts JS
Licence GNU/GPL (version 2 et au dela)

Fichier : CJScript.js
@author Jérôme Combes <jerome@planningbiblio.fr>
*/

 DataTable.type('date', 'className', 'dt-left');

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

/**
 * @function stackAlert
 * Generates a stacked alert. Uses BS alerts classes and comportment.
 * @param string message : message to display
 * @param string type :  message type; possible values: info, success, error or warning
 * @param int timeout : display duration in milliseconds; if timeout = 0, the alert is permanent
 * @param int position : position on the screen; possible values: combination of top/bottom and left/center/right
 * @param array translationOptions : message additional translation information (variables, domains ...)
 */

function stackAlert(message, type='success', timeout=7000, position='top-center', translationOptions = null)
{
  type = (type === 'error') ? 'danger' : type;

  // Translate the message
  message = Translator.trans(message, translationOptions);
  message = message.replace(/#BR#/g,"<br/>&emsp;&emsp;");
  message = message.replace(/\n/g,"<br/>&emsp;&emsp;");

  const close_msg = Translator.trans('Close');

  // Map of types with associated icons (Bootstrap Icons).
  const icons = {
    info: 'bi-info-circle-fill bi-information',
    success: 'bi-check-circle-fill bi-success',
    warning: 'bi-exclamation-triangle-fill bi-warning',
    danger: 'bi-exclamation-triangle-fill bi-danger'
  };

  // Map of positions with associated CSS coordinates
  const positions = {
    'top-right': { top: '70px', right: '20px' },
    'bottom-right': { bottom: '20px', right: '20px' },
    'top-left':	{ top: '70px', left: '20px' },
    'bottom-left': { bottom: '20px', left: '20px' },
    'top-center': { top: '70px', left: '50%', transform: 'translateX(-50%)' },
    'bottom-center': { bottom: '20px', left: '50%', transform: 'translateX(-50%)' }
  };

  // Unique ID for the alert (used for DOM manipulation)
  const alertId = 'alert-' + Date.now();

  // ID of the container for the current position
  const containerId = 'alert-stack-' + position;
  let $container = $('#' + containerId);

  // If the container doesn't exist, it initializes it
  if (!$container.length) {
    $container = $('<div>', {
      id: containerId,
      css: $.extend({
        position: 'fixed',
        zIndex: 1050,
        minWidth: '25%'
      }, positions[position])
    }).appendTo('body');

    // Center the container if necessary
    if (positions[position].transform) {
      $container.css('transform', positions[position].transform);
    }
  }

  // Builds the HTML for the alert and inserts it into the container
  const alertHtml = `
  <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show mb-2" role="alert"">
    <i class="bi ${icons[type]} me-2"></i> 
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="${close_msg}"></button>
    </div>`;

  $container.append(alertHtml);

  // Dismiss automatically the alert after the timeout if timeout > 0
  if (timeout > 0) {
    setTimeout(() => {$('#' + alertId).alert('close');}, timeout);
  }

  return this;
};

// Fonction JQuery
$(function(){

    /** jsFileLocation
     * Used to include other JS files. e.g. DataTable language and buttons files
     */
    jsFileLocation = $('script[src*=CJScript]').attr('src');  // the js file path
    jsFileLocation = jsFileLocation.replace(/CJScript\.js.*/, '');   // the js folder path

  // DataTables
  /*
  Les tableaux ayant la classe CJDataTable seront transformés en DataTable
  Les paramètres suivant peuvent leur être transmis via les classes et les attributs data-
  
  Sur le tableau (balise <table>), les attributs suivants :
  - data-sort : tri par défaut, doit être une chaine JSON du type [[0,"asc"],[1,"asc"]]. Valeur par défaut [[0,"asc"]]
  - data-stateSave : garde en mémoire l'état du tableau (tris, recherches). Valeurs : 0, false, 1 ou true. Valeur par défaut = true
  - data-length : nombre d'éléments affichés. Par défaut : 25
  - data-responsive : Tableau responsive (les colonnes qui n'entrent pas dans le cadre sont masquées, le signe + permet d'afficher leur contenu). Par défaut : true
  
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
    // For accentuated string
    else if(th[i].hasClass('clear-string')){
      aoCol.push({'sType': 'clear-string'});
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

    // DataTable responsive or not
    var responsive = true;
    if($(this).attr("data-responsive")) {
      responsive = $(this).attr("data-responsive")
    }

    // save state ?
    var saveState=true;
    if($(this).attr("data-stateSave") && ($(this).attr("data-stateSave")=="false" || $(this).attr("data-stateSave")=="0")){
      var saveState=false;
    }

    // Colonnes fixes
    var scollX=$(this).attr("data-fixedColumns")?"100%":"";
    
    // Liens pour exporter les informations
    var sDom='<"H"lfr>t<"F"ip>T';
    if($(this).attr("data-noExport")){
      sDom='<"H"lfr>t<"F"ip>';
    }
    
    // On applique le DataTable
    var CJDataTable=$(this).DataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": saveState,
      "aLengthMenu" : [[10,25,50,75,100,-1],[10,25,50,75,100,"All"]],
      "iDisplayLength" : tableLength,
      "aaSorting" : sort,
      "aoColumns" : aoCol,
      "sScrollX": scollX,
      "autoWidth": false,
      "buttons": true,
      "language" : { "url" : jsFileLocation+"/dataTables.french.lang.json" },
      "initComplete": function () {
        $('.dt-layout-row:last').after(CJDataTable.buttons().container());
      },

      // On refait le zebra, à chaque fois que le tableau est redessiné.
      // Utile en cas de suppression de ligne et d'utilisation du filtre et des tris
      "fnDrawCallback": CJDataTableStripe,
      responsive: responsive,
    });

    // Colonnes fixes
    if($(this).attr("data-fixedColumns")){
      var nb=$(this).attr("data-fixedColumns");
      new $.fn.dataTable.FixedColumns(CJDataTable, init={"iLeftColumns" : nb});
    }

  });

  $.fn.dataTableExt.oSort['clear-string-asc'] = function (x, y) {
    return removeAccents(x) > removeAccents(y) ? 1 : -1;
  };

  $.fn.dataTableExt.oSort['clear-string-desc'] = function (x, y) {
    return removeAccents(x) < removeAccents(y) ? 1 : -1;
  };

   // Check all checkboxes 
   $(".CJCheckAll").click(function(){
        var checked = $(this).prop('checked');
        $(this).closest("table").find("td input[type=checkbox]").each(function(){
            $(this).prop('checked', checked);
        });
    });
  
});
