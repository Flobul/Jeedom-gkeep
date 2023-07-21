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

$(document).ready(function() {
  jeedom.config.load({
    plugin : 'gkeep',
    configuration: 'selected_account',
    error: function (error) {
      $.fn.showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
      for (var i = 0; i < data.length; i++) {
        updateLogoSelectGkeep(data[i].value, data[i].state);
        hideDisplayCard(data[i].value, data[i].state);
      }
    }
  });
  jeedom.eqLogic.byType({
    type: 'gkeep',
    noCache: true,
    error: function (error) {
      $.fn.showAlert({message: error.message, level: 'warning'});
    },
    success: function(_eqLogics) {
      for (var i in _eqLogics) {
        updateDisplayCard($('.eqLogicDisplayCard[data-eqlogic_id=' + _eqLogics[i].id + ']'), _eqLogics[i]);
      }
    }
  });

});

hideDisplayCard = function (_account, _state) {
  var cards = $('.eqLogicDisplayCard[data-account="'+ _account +'"]');
  cards.toggle(_state != "0" && _state != 0); 
}

updateLogoSelectGkeep = function (_val, _state) {
    console.log(_val, _state )
    var div = $('.eqLogicThumbnailContainer .logoSelectGkeep[data-value="'+ _val +'"]');
    div.attr('data-state', _state);
    div.find('i').addClass(parseInt(_state) != 1 ? 'fa-user-alt-slash' : 'fa-user').removeClass(parseInt(_state) != 1 ? 'fa-user' : 'fa-user-alt-slash');
    div.css('opacity', parseInt(_state) != 1 ? 0.5 : 1);
};
updateDisplayCard = function (_card, _eq) {
	// Set visibility
    _card.toggleClass('disableCard', _eq.isEnable !== '1');
    var asTable = '';
  	asTable += asTableHelper(_eq, 'visible', 'fas fa-eye', 'fas fa-eye-slash');
	asTable += asTableHelper(_eq, 'pinned', 'fas fa-map-pin', 'fas fa-map-pin');
	asTable += asTableHelper(_eq, 'archived', 'fas fa-archive', 'fas fa-archive');
	asTable += asTableHelper(_eq, 'trashed', 'fas fa-trash', 'fas fa-trash');
	_card.find('span.hiddenAsTable').empty().html(asTable);

	var asCard = '';
	asCard += asCardHelper(_eq, 'enable', '{{Activé}}', '{{Désactivé}}', 'roundedLeft fas fa-check', 'roundedLeft fas fa-times');
	asCard += asCardHelper(_eq, 'visible', '{{Visible}}', '{{Masqué}}', 'fas fa-eye', 'fas fa-eye-slash');
	asCard += asCardHelper(_eq, 'pinned', '{{Épinglé}}', '{{Autre}}', 'fas fa-map-pin', 'fas fa-map-pin');
	asCard += asCardHelper(_eq, 'archived', '{{Archivé}}', '{{Non archivé}}', 'fas fa-archive',  'fas fa-archive');
	asCard += asCardHelper(_eq, 'trashed', '{{Supprimé}}', '{{En cours}}', 'fas fa-trash', 'fas fa-trash');
	asCard += '<a class="btn btn-xs cursor roundedRight"><i class="fas fa-cogs eqLogicAction tooltips" title="{{Configuration avancée}}" data-action="confEq"></i></a>';
	_card.find('span.hiddenAsCard').empty().html(asCard);

	_card.find('.eqLogicAction[data-action=confEq]').off('click').on('click', function() {
		$('#md_modal').dialog().load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + _eq.id).dialog('open');
	});
}

asTableHelper = function(_eq, _item, aClass, unaClass) {
    var colored = '';
    var iClass = '';
    if (_item == 'enable') {
      if (_eq.isEnable == '1') {
        colored = ' colored';
        iClass = aClass;
      } else {
        colored = '';
        iClass = unaClass;
      }
    } else if (_item == 'visible') {
      if (_eq.isVisible == '1') {
        colored = ' colored';
        iClass = aClass;
      } else {
        colored = '';
        iClass = unaClass;
      }
    } else {
      if(["true", true, 1, "1"].includes(_eq.configuration[_item])) {
        colored = ' colored';
        iClass = aClass;
      } else {
        colored = '';
        iClass = unaClass;
      }
    }
	return '<i class="' + iClass + colored + '"></i>';
}

asCardHelper = function(_eq, _item, _name, _unname, aClass, unaClass) {
    var name = '';
    var colored = '';
    var iClass = '';
    if (_item == 'enable') {
      if (_eq.isEnable == '1') {
        name = _name;
        colored = ' colored';
        iClass = aClass;
      } else {
        name = _unname;
        colored = '';
        iClass = unaClass;
      }
    } else if (_item == 'visible') {
      if (_eq.isVisible == '1') {
        name = _name;
        colored = ' colored';
        iClass = aClass;
      } else {
        name = _unname;
        colored = '';
        iClass = unaClass;
      }
    } else {
      if(["true", true, 1, "1"].includes(_eq.configuration[_item])) {
        colored = ' colored';
        name = _name;
        iClass = aClass;
      } else {
        colored = '';
        name = _unname;
        iClass = unaClass;
      }
    }
    return '<a class="btn btn-xs cursor w30"><i class="' + iClass + colored + ' tooltips" title="' + name + '"></i></a>';
}

$('#bt_healthgkeep').on('click', function() {
  $('#md_modal').dialog({
    title: "{{Santé Google Keep}}"
  });
  $('#md_modal').load('index.php?v=d&plugin=gkeep&modal=health').dialog('open');
});

$('#bt_documentationgkeep').off('click').on('click', function() {
  window.open($(this).attr("data-location"), "_blank", null);
});

printEqLogic = function(_eqLogic) {
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
        $('#img_device').attr("src", data.result.img);
        $('#img_device').addClass("imgColorFilter_" + data.result.color);
      }
    }
  })
  $('#table_infoseqlogic .eqLogicAttr').removeClass('eqLogicAttr');
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

synchronize = function() {
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
      console.log(request, status, error)
      handleAjaxError(request, status, error);
    },
    success: function(data) {
      console.log(data)
      if (data.state != 'ok') {
        $.fn.showAlert({
          message: data.result,
          level: 'danger'
        });
        //return;
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

$('.logoSelectGkeep').on('click', function () {
    var el = $(this);
    var value = el.attr('data-value');
    var state = el.attr('data-state');
    var newState = 1 - state;
    this.setAttribute('data-state', newState);

    var configuration = [];
    $('.logoSelectGkeep').each(function (index) {
        var value = $(this).attr('data-value');
        var state = $(this).attr('data-state');
        configuration.push({ value: value, state: state });
    });
    jeedom.config.save({
        plugin: 'gkeep',
        configuration: {selected_account: configuration},
        error: function (error) {
            $.fn.showAlert({ message: error.message, level: 'danger' });
        },
        success: function (data) {
            updateLogoSelectGkeep(value, newState);
            hideDisplayCard(value, newState);
		}
	});
});