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
    .label.label-default.coloredLabel[data-color=DEFAULT] {
        color: black;
    }
</style>
<table class="table table-condensed tablesorter" id="table_healthgkeep">
	<thead>
		<tr>
			<th>{{Appareil}}</th>
			<th>{{Type}}</th>
			<th>{{Statut}}</th>
			<th>{{Couleur}}</th>
			<th>{{Collaborateurs}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
      <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';

          echo '<td><span class="label label-info">' . $eqLogic->getConfiguration('type') . '</span></td>';
          
          $print = '';
          if ($eqLogic->getConfiguration('pinned', false) === true) {
              $print .= '<span class="label label-success" style="font-size : 1em; cursor : default;">{{Épinglé}}</span>';
          }
          if ($eqLogic->getConfiguration('archived', false) === true) {
              $print .= '<span class="label label-warning" style="font-size : 1em; cursor : default;">{{Archivé}}</span>';
          }
          if ($eqLogic->getConfiguration('trashed', false) === true) {
              $print .= '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{Supprimé}}</span>';
          }
          echo '<td>' . $print . '</td>';

          echo '<td><span class="label label-default coloredLabel" data-color="'.$eqLogic->getConfiguration('color').'" style="font-size : 1em; cursor : default;background-color:'.gkeep::getColor($eqLogic->getConfiguration('color')).' !important;">' . $eqLogic->getConfiguration('color') . '</span></td>';

          $colabo = $eqLogic->getConfiguration('collaborators', '');
          $printCo = '';
          if (is_array($colabo) && count($colabo) > 1) {
              foreach ($colabo as $col) {
                  $printCo .= '<span class="label label-info" style="font-size : 1em; cursor : default;">' . $col . '</span></br>';
              }
          } else {
              $printCo = '{{Aucun}}';
          }
          echo '<td>' . $printCo . '</td>';

          echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
          echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
        }
      ?>
	</tbody>
</table>