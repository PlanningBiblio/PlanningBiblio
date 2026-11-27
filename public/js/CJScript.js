/**
Divers scripts JS
Licence AGPL (version 3 et au dela)

Fichier : CJScript.js
@author Jérôme Combes <jerome@planningbiblio.fr>
*/

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
  div.setAttribute('role', 'alert');
  div.insertAdjacentHTML('beforeend', `<i class="bi ${icons[type]} me-2" aria-hidden="true"></i>`);
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

   // Check all checkboxes 
   $(".CJCheckAll").click(function(){
        var checked = $(this).prop('checked');
        $(this).closest("table").find("td input[type=checkbox]").each(function(){
            $(this).prop('checked', checked);
        });
    });
  
});
