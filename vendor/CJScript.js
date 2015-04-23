/* Divers scripts JS
Licence GNU/GPL (version 2 et au dela)

Fichier : CJScript.js
Création : mars 2015
Dernière modification : 23 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr
*/

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