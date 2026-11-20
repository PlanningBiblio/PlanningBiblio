/**
Planno
Licence AGPL (version 3 et au dela)

File: datatables-init.js
Automatic initialization of the tables having the class CJDataTable (DataTables).

The tables are configured with attributes on the <table> tag and classes on the <th> tags,
so that the templates only need classes and data- attributes.

Attributes of the <table> tag:
- data-sort: initial order, JSON string such as [[0,"asc"],[1,"asc"]]. Default: [[0,"asc"]]
- data-length: number of rows displayed. Default: 25
- data-state-save: save the state of the table (order, search, paging) in the local storage
  of the browser. Values: 0, false, 1 or true. Default: true
- data-responsive: responsive table (the columns that do not fit are hidden, the + sign displays
  their content). Values: 0, false, 1 or true. Default: true
- data-fixed-columns: number of fixed columns on the left (horizontal scrolling)
- data-no-export: hides the export buttons (attribute without value)

Classes of the <th> tags:
- dataTableNoSort: the column is not orderable
- dataTableHide: the column is hidden
- dataTableDate: dates (YYYY-MM-DD)
- dataTableDateFR: dates DD/MM/YYYY [HHhmm]
- dataTableDateFR-fin: end dates DD/MM/YYYY [HHhmm] (a date without time is after a date with time)
- dataTableHeureFR: hours (HHhmm, HH:mm, decimal hours)
- clear-string: strings sorted without taking accents into account

Order data: a cell can carry a data-order attribute (e.g. data-order="2026-11-12"); DataTables then
sorts the column on this value instead of the displayed text. Recommended for the dates when the
template has the raw value: the column then needs no dataTableDateFR class.

Sort functions for the types date-fr, date-fr-fin and heure-fr: see dataTables.sort.js.
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
 * URL of the French language file of DataTables (same folder as this script)
 */
function CJDataTableLanguageUrl() {
  var src = $('script[src*="datatables-init"]').attr('src') || '';
  return src.replace(/datatables-init\.js.*/, '') + 'dataTables.french.lang.json';
}

$(function () {

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
    var stateSave = CJDataTableBoolean(table.attr('data-state-save'), true);

    // Fixed columns (number of fixed columns on the left)
    var fixedColumns = parseInt(table.attr('data-fixed-columns'), 10) || 0;

    // Export buttons, hidden by data-no-export
    var exportButtons = table.attr('data-no-export') === undefined;

    var options = {
      pagingType: 'full_numbers',
      stateSave: stateSave,
      // -1: all the rows, label from the language file ('Tout')
      lengthMenu: [10, 25, 50, 75, 100, -1],
      pageLength: pageLength,
      order: order,
      columns: columns,
      autoWidth: false,
      buttons: exportButtons,
      language: { url: CJDataTableLanguageUrl() },
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
});
