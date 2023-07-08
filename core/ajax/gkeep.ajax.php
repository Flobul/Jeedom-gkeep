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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception('401 Unauthorized');
	}

	ajax::init();

    if (init('action') == 'getCredentials') {
        $result = gkeep::login();
        ajax::success($result);
    }

    if (init('action') == 'synchronize') {
        $result = gkeep::synchronize();
        ajax::success($result);
    }

    if (init('action') == 'deleteEquipments') {
       if (init('what') == 'all' || init('what') == 'appareils') {
            $eqLogics = eqLogic::byType('gkeep');
            if (init('what') == 'appareils') {

            } elseif (init('what') == 'all') {
                foreach ($eqLogics as $eqLogic) {
                    $eqLogic->remove();
                }
            }
        }
        ajax::success();
    }

    if (init('action') == 'getImage') {
        $eqLogic = gkeep::byId(init('id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('gkeep eqLogic non trouvé : ', __FILE__) . init('id'));
        }
        $result = $eqLogic->getImage();
        ajax::success($result);
    }
  
    if (init('action') == 'checkItem') {
        $eqLogic = gkeep::byId(init('eqLogic_id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('eqLogic gkeep non trouvé : ', __FILE__) . init('eqLogic_id'));
        }
        $cmd = gkeepCmd::byId(init('cmd_id'));
        if (!is_object($cmd)) {
            throw new Exception(__('cmd gkeep non trouvée : ', __FILE__) . init('id'));
        }
        ajax::success($eqLogic->checkItem(init('item_id'), init('change')));
    }

    if (init('action') == 'addItem') {
        $eqLogic = gkeep::byId(init('eqLogic_id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('eqLogic gkeep non trouvé : ', __FILE__) . init('eqLogic_id'));
        }
        $cmd = gkeepCmd::byId(init('cmd_id'));
        if (!is_object($cmd)) {
            throw new Exception(__('cmd gkeep non trouvée : ', __FILE__) . init('id'));
        }
        ajax::success($eqLogic->addItem(init('item_id'), init('change')));
    }

    if (init('action') == 'deleteItem') {
        $eqLogic = gkeep::byId(init('eqLogic_id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('eqLogic gkeep non trouvé : ', __FILE__) . init('eqLogic_id'));
        }
        $cmd = gkeepCmd::byId(init('cmd_id'));
        if (!is_object($cmd)) {
            throw new Exception(__('cmd gkeep non trouvée : ', __FILE__) . init('id'));
        }
        ajax::success($eqLogic->deleteItem(init('item_id')));
    }
  
    if (init('action') == 'updateNote') {
        $eqLogic = gkeep::byId(init('eqLogic_id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('eqLogic gkeep non trouvé : ', __FILE__) . init('eqLogic_id'));
        }
        ajax::success($eqLogic->updateNote(init('change')));
    }
  
    if (init('action') == 'addNote') {
        ajax::success(gkeep::addNote(init('object')));
    }
  
	throw new Exception('Aucune methode correspondante');
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}
?>