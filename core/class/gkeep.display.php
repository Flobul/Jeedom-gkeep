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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../plugins/gkeep/core/class/gkeep.class.php';

class gkeep_display extends eqLogic
{
    public static function displayActionCard($action_name, $fa_icon, $attr = '', $class = '') {
        $actionCard = '<div class="eqLogicDisplayAction eqLogicAction cursor ' . $class . '" ';
        if ($attr != '') $actionCard .= $attr;
        $actionCard .= '>';
        $actionCard .= '    <i class="fas ' . $fa_icon . '"></i><br>';
        $actionCard .= '    <span>' . $action_name . '</span>';
        $actionCard .= '</div>';
        echo $actionCard;
    }

    public static function displayBtnAction($class, $action, $title, $logo, $text, $display = FALSE) {
        $btn = '<a class="eqLogicAction btn btn-sm ' . $class . '"';
        $btn .= '    data-action="' . $action . '"';
        $btn .= '    title="' . $title . '"';
        if ($display) $btn .= '    style="display:none"';
        $btn .= '>';
        $btn .= '  <i class="fas ' . $logo . '"></i> ';
        $btn .= $text;
        $btn .= '</a>';
        echo $btn;
    }

    public static function displayEqLogicThumbnailContainer($eqLogics) {
        echo '<div class="panel panel-default">';
        echo '    <h3 class="panel-title">';
        echo '        <a id="accordiongkeep" class="accordion-toggle" data-toggle="collapse" data-parent="" href="#gkeep"><i class=""></i></a>';
       // echo '        <a id="accordiongkeep" class="accordion-toggle" data-toggle="collapse" data-parent="" href="#gkeep_' . $_type . '"><i class="' . gkeep::getIconClass($_type) . '"></i> ' . gkeep::getDeviceLabel($_type) . '</a>';
        echo '    </h3>';
        echo '    <div id="gkeep" class="panel-collapse collapse in">';
        echo '        <div class="eqLogicThumbnailContainer">';
        foreach ($eqLogics as $eqLogic) {
            $additionalInfo = ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Équipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Équipement non visible}}"></i>';

            $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
            echo '            <div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
            echo '                <img src="' . $eqLogic->getImage() . '"/>';
            echo '                <br>';
            echo '                <span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
            echo '                <span class="hidden hiddenAsCard displayTableRight">' . $additionalInfo . '</span>';
            echo '            </div>';
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
}