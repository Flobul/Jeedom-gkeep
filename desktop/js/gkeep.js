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

const commandTableBody = document.querySelector('#table_cmd tbody');
if (commandTableBody && typeof Sortable !== 'undefined') {
  new Sortable(commandTableBody, {draggable: '.cmd', handle: '.cmd', animation: 150});
}

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

  commandTableBody.insertAdjacentHTML('beforeend', tr);
  var newRow = commandTableBody.lastElementChild;
  jeedom.eqLogic.buildSelectCmd({
    id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
    filter: {
      type: 'info'
    },
    error: function(error) {
      jeedomUtils.showAlert({
        message: error.message,
        level: 'danger'
      });
    },
    success: function(result) {
      newRow.querySelector('.cmdAttr[data-l1key="value"]')?.insertAdjacentHTML('beforeend', result);
      newRow.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="updateCmdId"]')?.insertAdjacentHTML('beforeend', result);
      newRow.setJeeValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(newRow, init(_cmd.subType));
    }
  });
}

jeedom.config.load({
    plugin : 'gkeep',
    configuration: 'selected_account',
    error: function (error) {
      jeedomUtils.showAlert({message: error.message, level: 'danger'});
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
      jeedomUtils.showAlert({message: error.message, level: 'warning'});
    },
    success: function(_eqLogics) {
      for (var i in _eqLogics) {
        updateDisplayCard(document.querySelector('.eqLogicDisplayCard[data-eqlogic_id="' + _eqLogics[i].id + '"]'), _eqLogics[i]);
      }
    }
  });

function hideDisplayCard(_account, _state) {
  document.querySelectorAll('.eqLogicDisplayCard[data-account="' + CSS.escape(String(_account)) + '"]').forEach(card => {
    card.style.display = _state != '0' && _state != 0 ? '' : 'none';
  });
}

function updateLogoSelectGkeep(_val, _state) {
    const selector = '.eqLogicThumbnailContainer .logoSelectGkeep[data-value="' + CSS.escape(String(_val)) + '"]';
    document.querySelectorAll(selector).forEach(element => {
      element.dataset.state = _state;
      const icon = element.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-user', parseInt(_state) === 1);
        icon.classList.toggle('fa-user-alt-slash', parseInt(_state) !== 1);
      }
      element.style.opacity = parseInt(_state) === 1 ? '1' : '0.5';
    });
}
function updateDisplayCard(_card, _eq) {
	if (!_card) return;
	// Set visibility
    _card.classList.toggle('disableCard', _eq.isEnable !== '1');
    var asTable = '';
  	asTable += asTableHelper(_eq, 'visible', 'fas fa-eye', 'fas fa-eye-slash');
	asTable += asTableHelper(_eq, 'pinned', 'fas fa-map-pin', 'fas fa-map-pin');
	asTable += asTableHelper(_eq, 'archived', 'fas fa-archive', 'fas fa-archive');
	asTable += asTableHelper(_eq, 'trashed', 'fas fa-trash', 'fas fa-trash');
	const tableStatus = _card.querySelector('span.hiddenAsTable');
	if (tableStatus) tableStatus.innerHTML = asTable;

	var asCard = '';
	asCard += asCardHelper(_eq, 'enable', '{{Activé}}', '{{Désactivé}}', 'roundedLeft fas fa-check', 'roundedLeft fas fa-times');
	asCard += asCardHelper(_eq, 'visible', '{{Visible}}', '{{Masqué}}', 'fas fa-eye', 'fas fa-eye-slash');
	asCard += asCardHelper(_eq, 'pinned', '{{Épinglé}}', '{{Autre}}', 'fas fa-map-pin', 'fas fa-map-pin');
	asCard += asCardHelper(_eq, 'archived', '{{Archivé}}', '{{Non archivé}}', 'fas fa-archive',  'fas fa-archive');
	asCard += asCardHelper(_eq, 'trashed', '{{Supprimé}}', '{{En cours}}', 'fas fa-trash', 'fas fa-trash');
	asCard += '<a class="btn btn-xs cursor roundedRight"><i class="fas fa-cogs eqLogicAction tooltips" title="{{Configuration avancée}}" data-action="confEq"></i></a>';
	const cardStatus = _card.querySelector('span.hiddenAsCard');
	if (cardStatus) cardStatus.innerHTML = asCard;

	_card.querySelector('.eqLogicAction[data-action="confEq"]')?.addEventListener('click', function() {
		jeeDialog.dialog({id: 'md_gkeepEqLogicConfigure', title: '{{Configuration avancée}}', contentUrl: 'index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + encodeURIComponent(_eq.id)});
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

document.getElementById('bt_healthgkeep')?.addEventListener('click', function() {
  jeeDialog.dialog({id: 'md_gkeepHealth', title: '{{Santé Google Keep}}', contentUrl: 'index.php?v=d&plugin=gkeep&modal=health'});
});

document.getElementById('bt_documentationgkeep')?.addEventListener('click', function() {
  window.open(this.dataset.location, '_blank', 'noopener');
});

function printEqLogic(_eqLogic) {
  domUtils.ajax({
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
        jeedomUtils.showAlert({
          message: data.result,
          level: 'danger'
        });
        return;
      }
      if (data.result != '') {
        const image = document.getElementById('img_device');
        if (image) {
          image.src = data.result.img;
          image.classList.add('imgColorFilter_' + data.result.color);
        }
      }
    }
  })
  document.querySelectorAll('#table_infoseqlogic .eqLogicAttr').forEach(element => element.classList.remove('eqLogicAttr'));
}

document.getElementById('bt_addgkeep')?.addEventListener('click', function() {
  jeeDialog.dialog({id: 'md_gkeepAddNote', title: '{{Ajouter une note Google Keep}}', contentUrl: 'index.php?v=d&plugin=gkeep&modal=addNote'});
});

document.getElementById('bt_synchronizegkeep')?.addEventListener('click', function() {
  synchronize();
});

function synchronize() {
  jeedomUtils.showAlert({
    message: '{{Synchronisation en cours}}',
    level: 'warning'
  });
  document.querySelector('#bt_synchronizegkeep > i.fas')?.classList.add('fa-spin');
  domUtils.ajax({
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
        jeedomUtils.showAlert({
          message: data.result,
          level: 'danger'
        });
        //return;
      } else {
        jeedomUtils.showAlert({
          message: '{{Synchronisation terminée}}',
          level: 'success'
        });
        document.querySelector('#bt_synchronizegkeep > i.fas')?.classList.remove('fa-spin');
        window.location.reload();
      }
    }
  });
}

document.querySelectorAll('.eqLogicAction[data-action="delete"]').forEach(button => button.addEventListener('click', function(e) {

  var what = e.currentTarget.dataset.action2;
  var text = '{{Cette action supprimera tous les appareils.}}';
  jeeDialog.confirm(text, function(result) {
    if (result) {
      domUtils.ajax({
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
            jeedomUtils.showAlert({
              message: data.result,
              level: 'danger'
            });
            return;
          }
          jeedomUtils.showAlert({
            message: '{{Suppression réussie}} : ' + what,
            level: 'success'
          });
          location.reload();
        }
      });
    }
  });
}));

document.querySelectorAll('.logoSelectGkeep').forEach(element => element.addEventListener('click', function () {
    var value = this.dataset.value;
    var state = this.dataset.state;
    var newState = 1 - state;
    this.setAttribute('data-state', newState);

    var configuration = [];
    document.querySelectorAll('.logoSelectGkeep').forEach(item => {
        configuration.push({value: item.dataset.value, state: item.dataset.state});
    });
    jeedom.config.save({
        plugin: 'gkeep',
        configuration: {selected_account: configuration},
        error: function (error) {
            jeedomUtils.showAlert({ message: error.message, level: 'danger' });
        },
        success: function (data) {
            updateLogoSelectGkeep(value, newState);
            hideDisplayCard(value, newState);
		}
	});
}));
