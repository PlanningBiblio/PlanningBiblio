/**
Divers scripts JS
Licence AGPL (version 3 et au dela)

Fichier : CJScript.js
@author Jérôme Combes <jerome@planningbiblio.fr>
*/

 DataTable.type('date', 'className', 'dt-left');

function CJDataTableLogLevelFilter() {
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
         var max = $('#maxLevel').val();
         if (!max) return true;
         return data[1] <= max;
       });

     $('#maxLevel').on('change', function() {
        CJDataTableDraw();
     });
}

function CJDataTableDraw() {
    $('.CJDataTable').each(function() {
        $(this).DataTable().draw();
    });
}

/**
 * Options of a CJDataTable column, from the classes of its th:
 * - dataTableNoSort: column not orderable
 * - dataTableHide: hidden column
 * - dataTableDate, dataTableDateFR, dataTableDateFR-fin, dataTableHeureFR, clear-string: sort type
 */
function CJDataTableColumn(th) {
  if (th.hasClass('dataTableDate')) {
    return { type: 'date' };
  }
  if (th.hasClass('dataTableDateFR')) {
    return { type: 'date-fr' };
  }
  if (th.hasClass('dataTableDateFR-fin')) {
    return { type: 'date-fr-fin' };
  }
  if (th.hasClass('dataTableHeureFR')) {
    return { type: 'heure-fr' };
  }
  if (th.hasClass('dataTableNoSort')) {
    return { orderable: false };
  }
  if (th.hasClass('dataTableHide')) {
    return { visible: false };
  }
  if (th.hasClass('clear-string')) {
    return { type: 'clear-string' };
  }
  return { orderable: true };
}

/**
 * Boolean value of a data- attribute: '0' and 'false' are false, a missing attribute gives the default value
 */
function CJDataTableBoolean(value, defaultValue) {
  if (value === undefined || value === null || value === '') {
    return defaultValue;
  }
  return !(value === '0' || String(value).toLowerCase() === 'false');
}

function CJDataTableStripe() {
  // On refait le zebra, à chaque fois que le tableau est redessiné.
  // Utile en cas de suppression de ligne et d'utilisation du filtre et des tris
  $(this).find("tr").removeClass("odd");
  $(this).find("tr").removeClass("even");
  
  // Lignes paires et impaires : on ne traite que les lignes visibles
  var classe="odd";
  $(this).find("tr:visible").each(function(){
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

  // Replace the string '#BR#' and the newline character by a <br> element
  // followed by two EM SPACE (U+2003) characters for alignment
  const messageParts = message.split(/#BR#|\n/).flatMap(function (element, index) {
    return index > 0 ? [document.createElement('br'), "\u2003\u2003", element] : element
  });

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
        zIndex: 10,
        minWidth: '25%'
      }, positions[position])
    }).appendTo('body');

    // Center the container if necessary
    if (positions[position].transform) {
      $container.css('transform', positions[position].transform);
    }
  }

  // Builds the HTML for the alert and inserts it into the container
  const div = document.createElement('div');
  div.id = alertId;
  div.className = `alert alert-${type} alert-dismissible fade show mb-2`;
  div.role = 'alert';
  div.insertAdjacentHTML('beforeend', `<i class="bi ${icons[type]} me-2"></i>`);
  div.append(...messageParts);
  div.insertAdjacentHTML('beforeend', `<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="${close_msg}"></button>`);

  $container.append(div);

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
  - data-responsive: responsive table (the columns that do not fit are hidden, the + sign displays their content). Values: 0, false, 1 or true. Default: true
  - data-fixedColumns: number of fixed columns on the left (horizontal scrolling)
  - data-noExport: hides the export buttons
  
  Sur les balises th de l'entête, les classes suivantes permettent de définir le type de données contenues dans les cellules 
  pour trier correctement les colonnes :
  - dataTableNoSort : La colonne ne sera pas triable
  - dataTableHide: la colonne sera masquée
  - dataTableDateFR : La colonne contient des dates au format JJ-MM-AAAA [HH:mm:ss]. 
      Si seule l'heure est affichée, le tri considère que la date est celle du jour
  - dataTableDateFR-fin : La colonne des dates de fin
  - dataTableHeureFR : La colonne contient des heures au format HH:mm[:ss]
  */
  
  // Sort strings without taking accents into account (declared before the tables are initialized)
  Object.assign(DataTable.ext.type.order, {
    'clear-string-asc': function (x, y) {
      return removeAccents(x) > removeAccents(y) ? 1 : -1;
    },
    'clear-string-desc': function (x, y) {
      return removeAccents(x) < removeAccents(y) ? 1 : -1;
    },
  });

  $(".CJDataTable").each(function(){
    var table = $(this);

    // Columns: sort type, orderable, visibility, from the classes of the th
    var columns = [];

    // With 2 header rows, merged columns (colspan) are described by the th of the 2nd row
    var tr2 = null;
    var tr2Index = 1;
    if (table.find('thead tr').length == 2) {
      tr2 = table.find('thead tr:nth-child(2)');
    }

    table.find('thead tr:first th').each(function(){
      var th = [$(this)];

      if ($(this).attr('colspan') && $(this).attr('colspan') > 1 && tr2) {
        th = [];
        for (var i = 0; i < $(this).attr('colspan'); i++) {
          th.push(tr2.find('th:nth-child(' + tr2Index + ')'));
          tr2Index++;
        }
      }

      for (var j in th) {
        columns.push(CJDataTableColumn(th[j]));
      }
    });

    // Initial order. Default: 1st column
    var order = [[0, 'asc']];
    if (table.attr('data-sort')) {
      order = JSON.parse(table.attr('data-sort'));
    }

    // Number of rows displayed. Default: 25
    var pageLength = 25;
    if (table.attr('data-length')) {
      pageLength = parseInt(table.attr('data-length'), 10) || pageLength;
    }

    // Responsive table. Default: true. data-responsive='false' or '0' disables it
    var responsive = CJDataTableBoolean(table.attr('data-responsive'), true);

    // Save the state (order, search, paging). Default: true
    var stateSave = CJDataTableBoolean(table.attr('data-stateSave'), true);

    // Fixed columns (number of fixed columns on the left)
    var fixedColumns = parseInt(table.attr('data-fixedColumns'), 10) || 0;

    // Export buttons, hidden by data-noExport
    var exportButtons = !table.attr('data-noExport');

    var options = {
      pagingType: 'full_numbers',
      stateSave: stateSave,
      lengthMenu: [10, 25, 50, 75, 100, { label: 'All', value: -1 }],
      pageLength: pageLength,
      order: order,
      columns: columns,
      autoWidth: false,
      buttons: exportButtons,
      language: { url: jsFileLocation + '/dataTables.french.lang.json' },
      responsive: responsive,

      // On refait le zebra, à chaque fois que le tableau est redessiné.
      // Utile en cas de suppression de ligne et d'utilisation du filtre et des tris
      drawCallback: function (settings) {
        CJDataTableStripe.call(new DataTable.Api(settings).table().node());
      },
    };

    if (fixedColumns) {
      options.scrollX = true;
      options.fixedColumns = { start: fixedColumns };
      // With scrollX, the header is drawn in a separate table: the widths of the columns must be
      // calculated, otherwise the header and the body are misaligned and the offsets of the fixed
      // columns are wrong (the fixed header cells overlap)
      options.autoWidth = true;
    }

    options.initComplete = function (settings) {
      var api = new DataTable.Api(settings);

      // Buttons placed under their own table (not under the last table of the page)
      if (exportButtons) {
        $(api.table().container()).find('.dt-layout-row').last().after(api.buttons().container());
      }

      // Recalculate the widths once the language file and the web fonts are loaded:
      // both change the width of the texts after the first draw
      if (fixedColumns) {
        api.columns.adjust();
        if (document.fonts && document.fonts.ready) {
          document.fonts.ready.then(function () {
            api.columns.adjust();
          });
        }
      }
    };

    new DataTable(this, options);
  });

   // Check all checkboxes 
   $(".CJCheckAll").click(function(){
        var checked = $(this).prop('checked');
        $(this).closest("table").find("td input[type=checkbox]").each(function(){
            $(this).prop('checked', checked);
        });
    });
  
});
