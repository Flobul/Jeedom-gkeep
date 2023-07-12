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

    public static function displayEqLogicThumbnailContainer($eqLogics, $_option = null) {
        switch ($_option) {
            case 'pinned':
                $option = $_option;
                $val = true;
                $text = '{{ÉPINGLÉ}}';
                $icon = '<i class="fas fa-map-pin colored"></i>';
                break;
            case 'unpinned':
                $option = 'pinned';
                $val = false;
                $text = '{{AUTRES}}';
                $icon = '<i class="fas fa-map-pin"></i>';
                break;
            case 'archived':
                $option = $_option;
                $val = true;
                $text = '{{ARCHIVÉ}}';
                $icon = '<i class="fas fa-archive colored"></i>';
                break;
            case 'trashed':
                $option = $_option;
                $val = true;
                $text = '{{SUPPRIMÉ}}';
                $icon = '<i class="fas fa-trash colored"></i>';
                break;
            default:
                $option = $_option;
                $val = true;
                $text = '';
                $icon = '';
        }

        echo '<div class="panel panel-default">';
        echo '    <h3 class="panel-title">';
        echo '        <a id="accordiongkeep" class="accordion-toggle" data-toggle="collapse" data-parent="" href="#gkeep_' . $_option . '"> ' . $icon . ' ' . $text . '</a>';
        echo '    </h3>';
        echo '    <div id="gkeep_' . $_option . '" class="panel-collapse collapse in">';
        echo '        <div class="eqLogicThumbnailContainer">';
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getConfiguration($option, false) === $val) {
                $visibleInfo = ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Équipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Équipement non visible}}"></i>';
                //$additionalInfo = ($eqLogic->getConfiguration('pinned') === true) ? '<i class="fas fa-map-pin colored" title="{{Épinglé}}"></i>' : '<i class="fas fa-map-pin" title="{{Autre}}"></i>';

                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '            <div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo '                <span class="hiddenAsTable"></span>';
                echo '                <img class="imgColorFilter_' . $eqLogic->getConfiguration('color', 'DEFAULT') . '" src="' . $eqLogic->getImage()['img'] . '"/>';
                echo '                <br>';
                echo '                <span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '                <span class="hidden hiddenAsCard displayTableRight">' . $visibleInfo . '</span>';
                echo '            </div>';
            }
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
}