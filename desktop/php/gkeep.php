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

require_once __DIR__ . '/../../../../plugins/gkeep/core/class/gkeep.display.php';

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('gkeep');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

?>
<link rel="stylesheet" href="/plugins/gkeep/desktop/css/gkeep.css">

<div class="row row-overflow">
  <div class="eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">

        <?php
          gkeep_display::displayActionCard('{{Synchronisation}}', 'fa-sync', 'id="bt_synchronizegkeep"', 'eqLogicAction logoPrimaryGkeep');
          gkeep_display::displayActionCard('{{Ajouter}}', 'fa-folder-plus', 'id="bt_addgkeep"', 'eqLogicAction logoSecondaryGkeep');
          gkeep_display::displayActionCard('{{Configuration}}', 'fa-wrench', 'data-action="gotoPluginConf"', 'logoSecondary');
          gkeep_display::displayActionCard('{{Santé}}', 'fa-medkit', 'id="bt_healthgkeep"', 'logoSecondary');
          gkeep_display::displayActionCard('{{Documentation}}', 'fa-book-reader', 'id="bt_documentationgkeep" data-location="' . $plugin->getDocumentation() . '"', 'logoSecondary');
          gkeep_display::displayActionCard('{{Suppression de tous les appareils}}', 'fa-trash-alt', 'data-action="delete" data-action2="all"', 'logoTrashGkeep');
        ?>
    </div>
    <legend><i class="icon kiko-smart-house"></i> {{Mes appareils}}</legend>
		<?php
            if (count($eqLogics) == 0) {
                echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement trouvé, cliquez sur "Synchronisation" pour commencer}}</div>';
            } else {
                echo '<div class="input-group" style="margin:5px;">';
                echo '    <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
                echo '    <div class="input-group-btn">';
                echo '        <a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
                echo '        <a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
                echo '    </div>';
                echo '</div>';
                gkeep_display::displayEqLogicThumbnailContainer($eqLogics);
            }
        ?>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
        <a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a>
        <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
      </span>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Équipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <div class="col-xs-6">
          <form class="form-horizontal">
            <fieldset>
              <div class="form-group">
                <legend><i class="fas fa-sitemap icon_green"></i> {{Général}}</legend>
                <label class="col-sm-4 control-label">{{Nom du vidéoprojecteur}}</label>
                <div class="col-sm-5">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de la box}}" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                <div class="col-sm-5">
                  <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                      $options = '';
                      foreach ((jeeObject::buildTree(null, false)) as $object) {
                          $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration("parentNumber")) . $object->getName() . '</option>';
                      }
                      echo $options;
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                <div class="col-sm-8">
                  <?php
                      foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                          echo '<label class="checkbox-inline">';
                          echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                          echo '</label>';
                      }
                  ?>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label">{{Options}}</label>
                <div class="col-sm-8">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label help" data-help="{{Cocher la case pour utiliser le widget associé au type de l'appareil.}}</br>{{Laissez décoché pour laisser le core générer le widget par défaut.}}">{{Widget équipement}}
                </label>
                <div class="col-sm-8">
                  <input type="checkbox" class="eqLogicAttr form-control" id="widgetTemplate" data-l1key="display" data-l2key="widgetTmpl" />
                </div>
              </div>
          </form>
        </div>
        <div class="col-sm-6">
          <form class="form-horizontal">
            <legend><i class="fas fa-info-circle icon_yellow"></i> {{Informations}}</legend>
            <fieldset>


<div class="form-group">
                <table id="table_infoseqlogic" class="col-sm-9 table-bordered table-condensed" style="border-radius: 10px;">
                  <thead>
                  </thead>
                  <tbody>
                  
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Type}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="type"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Épinglé}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="pinned"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Archivé}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="archived"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Supprimé}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="trashed"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Collaborateurs}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="collaborators" data-l3key="0"></span>
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="collaborators" data-l3key="1"></span>
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="collaborators" data-l3key="2"></span>
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="collaborators" data-l3key="3"></span>
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="collaborators" data-l3key="4"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Couleur}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="color"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Date de création}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="created"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Date de modification}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="edited"></span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="col-sm-4">
                        <span style="font-size : 1em;">{{Date de mise à jour}}</span>
                      </td>
                      <td>
                        <span class="label label-default" style="font-size:1em;white-space:unset !important">
                          <span class="eqLogicAttr" data-l1key="configuration" data-l2key="updated"></span>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <br>
              </div>

              <div class="form-group">
                <table id="table_infoseqlogic" class="col-sm-9 table-bordered table-condensed" style="border-radius: 10px;">
                  <thead>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
                </br>
              </div>
              <div class="form-group">
                <div class="col-sm-10">
                  <center>
                    <img src="plugins/gkeep/plugin_info/gkeep_icon.png" data-original=".svg" id="img_device" class="img-responsive" style="max-height:450px;max-width:400px" onerror="this.src='core/img/no_image.gif'" />
                  </center>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-10">
                  <a class="btn btn-success btn-sm roundedLeft roundedRight" id="bt_showPrograms"><i class="fa fa-cogs"></i> {{Programmes}}</a>
                </div>
              </div>

            </fieldset>
          </form>
        </div>
      </div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
              <th style="min-width:200px;width:350px;">{{Nom}}</th>
              <th>{{Type}}</th>
              <th style="min-width:260px;">{{Options}}</th>
              <th>{{Valeur}}</th>
              <th style="min-width:80px;width:200px;">{{Actions}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
include_file('desktop', 'gkeep', 'js', 'gkeep');
include_file('core', 'plugin.template', 'js');
?>