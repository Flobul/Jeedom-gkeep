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

<table class="table table-condensed tablesorter" id="table_healthgkeep">
	<thead>
		<tr>
			<th>{{Appareil}}</th>
			<th>{{Type}}</th>
			<th>{{Statut}}</th>
			<th>{{Connecté}}</th>
			<th>{{RSSI}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
      <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';

          echo '<td>' . $eqLogic->getConfiguration('type') . '</td>';

          $status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
          if ($eqLogic->getStatus('state') == 'nok') {
              $status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
          }
          echo '<td>' . $status . '</td>';

          $connected = $eqLogic->getCmd('info', 'connectionStatus');
          $print = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NON}}</span>';
          if (is_object($connected) && $connected->execCmd() > "0") {
              $print = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OUI}}</span>';
          }
          echo '<td>' . $print . '</td>';

          $type = $eqLogic->getConfiguration('type');
          $couche = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{N/A}}</span>';
          if (isset($type)) {
              $couche = '<span class="label label-info" style="font-size : 1em; cursor : default;">' . $type . '</span>';
          }
          echo '<td>' . $couche . '</td>';

          echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
          echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
        }
      ?>
	</tbody>
</table>