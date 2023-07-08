/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
});

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {
      configuration: {}
    };
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }

  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'

  tr += '<td>';
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += '</td>';

  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'

  tr += '<td>';
  if (init(_cmd.type) == 'info') {
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  }
  if (init(_cmd.subType) == 'select') {
    tr += '    <input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste de valeur|texte séparé par ;}}" title="{{Liste}}">';
  }
  if (['select', 'slider', 'color'].includes(init(_cmd.subType)) || init(_cmd.configuration.updateCmdId) != '') {
    tr += '    <select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdId" title="{{Commande d\'information à mettre à jour}}">';
    tr += '        <option value="">{{Aucune}}</option>';
    tr += '    </select>';
    tr += '    <input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdToValue" placeholder="{{Valeur de l\'information}}">';
  }
  tr += '</td>';

  tr += '<td style="min-width:80px;width:200px;">';
  tr += '<div class="input-group">';
  if (is_numeric(_cmd.id) && _cmd.id != '') {
    tr += '<a class="btn btn-default btn-xs cmdAction roundedLeft" data-action="configure" title="{{Configuration de la commande}} ' + _cmd.type + '"><i class="fa fa-cogs"></i></a>';
    tr += '<a class="btn btn-success btn-xs cmdAction" data-action="test" title="{{Tester}}"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<a class="btn btn-danger btn-xs cmdAction roundedRight" data-action="remove" title="{{Suppression de la commande}} ' + _cmd.type + '"><i class="fas fa-minus-circle"></i></a>';
  tr += '</tr>';

  $('#table_cmd tbody').append(tr);
  var tr = $('#table_cmd tbody tr').last();
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {
      type: 'info'
    },
    error: function(error) {
      $.fn.showAlert({
        message: error.message,
        level: 'danger'
      });
    },
    success: function(result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });
}

$('.changeIncludeState').on('click', function() {
  var el = $(this);
  var state = $(this).attr('data-state');
  jeedom.config.save({
    plugin: 'gkeep',
    configuration: {
      include_mode: el.attr('data-state')
    },
    error: function(error) {
      $.fn.showAlert({
        message: error.message,
        level: 'danger'
      });
    },
    success: function() {
      if (el.attr('data-state') == 1) {
        $.hideAlert();
        $('.changeIncludeState:not(.card)').removeClass('btn-default').addClass('btn-success');
        $('.changeIncludeState').attr('data-state', 0);
        $('.changeIncludeState.card').css('background-color', '#8000FF');
        $('.changeIncludeState.card span center').text('{{Arrêter l\'inclusion}}');
        $('.changeIncludeState:not(.card)').html('<i class="fa fa-sign-in fa-rotate-90"></i> {{Arrêter l\'inclusion}}');
        $.fn.showAlert({
          message: '{{Vous êtes en mode inclusion. Cliquez à nouveau sur le bouton d\'inclusion pour sortir de ce mode}}',
          level: 'warning'
        });
      } else {
        $.hideAlert();
        $('.changeIncludeState:not(.card)').addClass('btn-default').removeClass('btn-success btn-danger');
        $('.changeIncludeState').attr('data-state', 1);
        $('.changeIncludeState:not(.card)').html('<i class="fa fa-sign-in fa-rotate-90"></i> {{Mode inclusion}}');
        $('.changeIncludeState.card span center').text('{{Mode inclusion}}');
        $('.changeIncludeState.card').css('background-color', '#ffffff');
        $.fn.hideAlert();
      }
    }
  });
});

$('body').on('gkeep::includeDevice', function(_event, _options) {
  if (modifyWithoutSave) {
    $.fn.showAlert({
      message: '{{Un périphérique vient d\'être inclu/exclu. Veuillez réactualiser la page}}',
      level: 'warning'
    });
  } else {
    if (_options == '') {
      window.location.reload();
    } else {
      window.location.href = 'index.php?v=d&p=gkeep&m=gkeep&id=' + _options;
    }
  }
});

$('#bt_healthgkeep').on('click', function() {
  $('#md_modal').dialog({
    title: "{{Santé gkeep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=health').dialog('open');
});

$('#bt_pushmessagesgkeep').on('click', function() {
  $('#md_modal').dialog({
    title: "{{Messages Push gkeep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=pushmessages').dialog('open');
});

$('#bt_firmwaregkeep').on('click', function() {
  $('#md_modal').dialog({
    title: "{{Firmware gkeep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=firmware').dialog('open');
});

$('#bt_documentationgkeep').off('click').on('click', function() {
  window.open($(this).attr("data-location"), "_blank", null);
});



function printEqLogic(_eqLogic) {

  $.ajax({
    type: "POST",
    url: "plugins/gkeep/core/ajax/gkeep.ajax.php",
    data: {
      action: "getImage",
      id: _eqLogic.id
    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function(data) {
      if (data.state != 'ok') {
        $.fn.showAlert({
          message: data.result,
          level: 'danger'
        });
        return;
      }
      if (data.result != '') {
        $('#img_device').attr("src", data.result);
      }
    }
  })
}

$('#bt_addgkeep').on('click', function() {
  $('#md_modal').dialog({
    title: "{{Ajouter une note Google Keep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=addNote').dialog('open');
});

$('#bt_synchronizegkeep').on('click', function() {
  synchronize();
});

function synchronize() {
  $.fn.showAlert({
    message: '{{Synchronisation en cours}}',
    level: 'warning'
  });
  $('#bt_synchronizegkeep > i.fas').addClass('fa-spin');
  $.ajax({
    type: "POST",
    url: "plugins/gkeep/core/ajax/gkeep.ajax.php",
    data: {
      action: "synchronize"
    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function(data) {
      if (data.state != 'ok') {
        $.fn.showAlert({
          message: data.result,
          level: 'danger'
        });
        return;
      } else {
        $.fn.showAlert({
          message: '{{Synchronisation terminée}}',
          level: 'success'
        });
        $('#bt_synchronizeSmartthings > i.fas').removeClass('fa-spin');
        window.location.reload();
      }
    }
  });
}

$('.eqLogicAction[data-action=delete]').on('click', function(e) {

  var what = e.currentTarget.dataset.action2;
  var text = '{{Cette action supprimera tous les appareils.}}';
  bootbox.confirm(text, function(result) {
    if (result) {
      $.ajax({
        type: "POST",
        url: "plugins/gkeep/core/ajax/gkeep.ajax.php",
        data: {
          action: "deleteEquipments",
          what: what
        },
        dataType: 'json',
        error: function(request, status, error) {
          handleAjaxError(request, status, error);
        },
        success: function(data) {
          if (data.state != 'ok') {
            $.fn.showAlert({
              message: data.result,
              level: 'danger'
            });
            return;
          }
          $.fn.showAlert({
            message: '{{Suppression réussie}} : ' + what,
            level: 'success'
          });
          location.reload();
        }
      });
    }
  });
});

$('#bt_showPrograms').on('click', function(e) {
  $('#md_modal').dialog({
    title: "{{Programmes gkeep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=schedule&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});