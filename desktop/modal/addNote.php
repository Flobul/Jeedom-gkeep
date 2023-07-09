<?php
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

if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
$eqLogics = gkeep::byType('gkeep');
?>
<style>
    #table_createNoteGkeep .tableNote,
    #table_createNoteGkeep .tableList {
        display: none;
    }
    #table_createNoteGkeep .titleNote {
        width: auto;
        min-width: 400px;
    }
    .btnNoteOrList [data-action="1"] {
        color: var(--sc-lightTxt-color) !important;
        background-color: var(--al-success-color) !important;
    }
    .btnNoteOrList [data-action="0"] {
        color: var(--linkHoverLight-color) !important;
        background-color: var(--btn-default-color) !important;
    }

    #table_createNoteGkeep .itemsOnly {
        display: none;
    }

    #table_createNoteGkeep  #sendIt {
        float: right;
    }
    #table_createNoteGkeep .addNoteAttr[data-l1key="text"] {
        border: 1px solid #000;
        padding: 8px;
        font-family: courier new;
    }
</style>
<div class="table table-condensed tablesorter" id="table_createNoteGkeep">
  <div class="btnNoteOrList center">
    <a class="btn btn-sm" id="createNote" data-action="1"><i class="fas fa-sticky-note "></i> {{Une note}}</a>
    <a class="btn btn-sm" id="createList" data-action="0"><i class="fas fa-list "></i> {{Une liste}}</a>
  </div>
    <a class="btn btn-sm btn-success" id="sendIt"><i class="fas fa-save"></i> {{Ajouter}}</a>
  <div class="col-sm-12">
    <form class="form-horizontal">
      <fieldset>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Titre}} *
            <sup><i class="fas fa-question-circle tippied" data-title="{{Titre de la note}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <input type="text" class="titleNote form-control addNoteAttr" data-l1key="title" placeholder="{{Titre de la note}}" />
          </div>
        </div>
        <div class="form-group noteOnly">
          <label class="col-sm-3 control-label">{{Texte}} *
            <sup><i class="fas fa-question-circle tippied" data-title="{{Texte de la note}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <textarea class="addNoteAttr autogrow" data-l1key="text" rows="2" cols="70" placeholder="{{Texte de la note}}"></textarea>
          </div>
        </div>
        <div class="form-group itemsOnly">
          <label class="col-sm-3 control-label">{{Éléments}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Éléments de la note}}"></i></sup>
          </label>
          <a class="btn btn-sm" id="addItem" data-number="0">{{Élément}}</a>
          <div class="col-sm-9">
              <span id="itemField"></span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Couleur}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Couleur de la note}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <select class="input-sm addNoteAttr" data-l1key="color" >
              <option value="BLUE">{{Bleu}}</option>
              <option value="BROWN">{{Marron}}</option>
              <option value="CERULEAN">{{Bleu céruléen}}</option>
              <option value="GRAY">{{Gris}}</option>
              <option value="GREEN">{{Vert}}</option>
              <option value="ORANGE">{{Orange}}</option>
              <option value="PINK">{{Rose}}</option>
              <option value="PURPLE">{{Violet}}</option>
              <option value="RED">{{Rouge}}</option>
              <option value="TEAL">{{Bleu sarcelle}}</option>
              <option value="DEFAULT">{{Défaut}}</option>
              <option value="YELLOW">{{Jaune}}</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Options}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Note à archiver ou épingler.}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <label class="checkbox-inline"><input type="checkbox" class="addNoteAttr" data-l1key="archived" >{{Archivée}}</label>
            <label class="checkbox-inline"><input type="checkbox" class="addNoteAttr" data-l1key="pinned" >{{Épinglée}}</label>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Étiquettes}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Étiquettes.}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <input type="text" class="labelsNote form-control addNoteAttr" data-l1key="labels" placeholder="{{Label1,Label2,Label3...}}" />
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Annotations}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Commentaires.}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <input type="text" class="annotationsNote form-control addNoteAttr" data-l1key="annotations" placeholder="{{Annotations}}" />
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Collaborateurs}}
            <sup><i class="fas fa-question-circle tippied" data-title="{{Personnes ayant accès à la note.}}"></i></sup>
          </label>
          <div class="col-sm-9">
            <input type="text" class="collaboNote form-control addNoteAttr" data-l1key="collaborators" placeholder="{{Collaborateur1,Collaborateur2,Collaborateur3...}}" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
<script>
    document.getElementById('table_createNoteGkeep').addEventListener('click', function(event) {
      var _target = null
      if (_target = event.target.closest('.btnNoteOrList')) {
        if (event.target.id == 'createNote') {
          document.querySelector('.itemsOnly').style.display = 'none';
          document.querySelector('.noteOnly').style.display = 'block';
          document.getElementById('createNote').setAttribute('data-action','1');
          document.getElementById('createList').setAttribute('data-action','0');
        } else if (event.target.id == 'createList') {
          document.querySelector('.itemsOnly').style.display = 'block';
          document.querySelector('.noteOnly').style.display = 'none';
          document.getElementById('createList').setAttribute('data-action','1');
          document.getElementById('createNote').setAttribute('data-action','0');   
        }
      }
      if (_target = event.target.closest('#addItem')) {
        addItem();
      }
      if (_target = event.target.closest('.removeItem')) {
        event.target.closest('.item').remove()
        updateItemNumbers();
      }
      if (_target = event.target.closest('.addNoteAttr[data-l1key="color"]')) {
        _target.setAttribute('data-touched', 1);
      }
      if (_target = event.target.closest('#sendIt')) {
        var note = $('#table_createNoteGkeep').getValues('.addNoteAttr')[0];
        var filteredNote = {};

        if (!note.title) {
          $.fn.showAlert({
            message: '{{Veuillez remplir le titre avant de créer la note.}}',
            level: 'danger'
          });
        } else if (document.getElementById('createNote').getAttribute('data-action') == '1') {
          if (!note.text) {
            $.fn.showAlert({
              message: '{{Veuillez remplir le texte avant de créer la note.}}',
              level: 'danger'
            });
          }
        }

        Object.keys(note).forEach(function(key) {
          if (
            note[key] !== null &&
            note[key] !== '' &&
            !(key === 'color' && note[key] === '#000000' && document.querySelector('.addNoteAttr[data-l1key="color"]').getAttribute('data-touched') != 1) &&
            (
              (document.getElementById('createNote').getAttribute('data-action') != '1' && key !== 'text') ||
              (document.getElementById('createNote').getAttribute('data-action') == '1' && key !== 'list')
            )
          ) {
            filteredNote[key] = note[key];
          }
        });
        console.log(filteredNote)
        $.ajax({
          type: "POST",
          url: "plugins/gkeep/core/ajax/gkeep.ajax.php",
          data: {
            action: "addNote",
            object: filteredNote
          },
          dataType: 'json',
          error: function(request, status, error) {
            handleAjaxError(request, status, error);
          },
          success: function(data) {
            console.log(data)
            if (data.state != 'ok') {
              $.fn.showAlert({
                message: data.result,
                level: 'danger'
              });
              return;
            }
          }
        })
      }
    })
    function addItem() {
      var addItemButton = document.getElementById('addItem');
      var newNumber = parseInt(addItemButton.getAttribute('data-number')) + 1;
      addItemButton.setAttribute('data-number', newNumber);
      var text = '<div class="item" data-item="'+newNumber+'">'
      text += '    <label class="col-sm-5 control-label title">{{Élément}} '+newNumber+'</label>';
      text += '    <div class="col-sm-7" style="display: inline-flex;">';
      text += '        <input type="checkbox" class="addNoteAttr" data-l1key="list" data-l2key="'+newNumber+'" data-l2key="checked" >';
      text += '        <input type="text" class="itemNote form-control addNoteAttr" data-l1key="list" data-l2key="'+newNumber+'" data-l2key="text" placeholder="{{Texte de l\'élément}}" />';
      text += '        <a class="btn btn-sm btn-danger removeItem"><i class="fas fa-minus-circle"></i></a>';
      text += '    </div>';
      text += '</div>';
      document.getElementById('itemField').innerHTML += text;
    }
    function updateItemNumbers() {
      var itemItems = document.querySelectorAll('.item');
      var addItemButton = document.getElementById('addItem');
      addItemButton.setAttribute('data-number', itemItems.length);

      for (var i = 0; i < itemItems.length; i++) {
        itemItems[i].setAttribute('data-item', i + 1);
        itemItems[i].querySelector('label.title').innerText = '{{Élément}} '+ (i + 1);
      }
    }

</script>
</div>