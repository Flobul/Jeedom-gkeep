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
require_once __DIR__ . "/../../../../core/php/core.inc.php";

class gkeep extends eqLogic
{
    /*     * *************************Attributs****************************** */
    public static $_pluginVersion = '0.80';
    public static $_widgetPossibility = array(
        'custom' => true,'parameters' => array(
			'colorWidgetName' => array(
				'name' => 'Couleur de la police du bandeau',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'bgWidgetName' => array(
				'name' => 'Couleur de fond du bandeau',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'colorEqLogic' => array(
				'name' => 'Couleur de la police',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'bgEqLogic' => array(
				'name' => 'Couleur de fond',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'cmdName' => array(
				'name' => 'Nom des commandes',
				'type' => '',
				'default' => '',
				'allow_transparent' => false,
				'allow_displayType' => true
			)
        )
    );
    public static $_templateArray = array();

    /**
     * Méthode appellée par le core (moteur de tâche) cron configuré dans la fonction gkeep_install
     * Lance une fonction pour récupérer les appareils et une fonction pour rafraichir les commandes
     * @param
     * @return
     */
    public static function update()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
        $autorefresh = config::byKey('autorefresh', __CLASS__, '');
        if ($autorefresh != '') {
            try {
                $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                if ($c->isDue()) {
                    try {
                        foreach (eqLogic::byType(__CLASS__) as $eqLogic) {
                            if ($eqLogic->getIsEnable()) {
                                $eqLogic->refresh();
                            }
                        }
                    } catch (Exception $exc) {
                        log::add(__CLASS__, 'error', __('Erreur : ', __FILE__) . $exc->getMessage());
                    }
                }
            } catch (Exception $exc) {
                log::add(__CLASS__, 'error', __('Expression cron non valide : ', __FILE__) . $autorefresh);
            }
        }
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
    }

    /**
     * Méthode appellée avant la création de l'objet
     * Active et affiche l'objet
     * @param
     * @return
     */
    public function preInsert()
    {
        $this->setIsEnable(1);
        $this->setIsVisible(1);
    }


    /**
     * Méthode appellée après la création de l'objet
     * Ajoute la commande refresh
     * @param
     * @return
     */
    public function postInsert()
    {
        $cmdRefresh = $this->getCmd('action', 'refresh');
        if (!is_object($cmdRefresh)) {
            $cmdRefresh = new gkeepCmd();
            $cmdRefresh->setEqLogic_id($this->getId());
            $cmdRefresh->setLogicalId('refresh');
            $cmdRefresh->setName(__("Rafraîchir", __FILE__));
            $cmdRefresh->setType('action');
            $cmdRefresh->setSubType('other');
            $cmdRefresh->save();
        }
        $cmdPin = $this->getCmd('action', 'pinNote');
        if (!is_object($cmdPin)) {
            $cmdPin = new gkeepCmd();
            $cmdPin->setEqLogic_id($this->getId());
            $cmdPin->setLogicalId('pinNote');
            $cmdPin->setName(__("Épingler la note", __FILE__));
            $cmdPin->setType('action');
            $cmdPin->setSubType('other');
            $cmdPin->save();
        }
        $cmdUnpin = $this->getCmd('action', 'unpinNote');
        if (!is_object($cmdUnpin)) {
            $cmdUnpin = new gkeepCmd();
            $cmdUnpin->setEqLogic_id($this->getId());
            $cmdUnpin->setLogicalId('unpinNote');
            $cmdUnpin->setName(__("Désépingler la note", __FILE__));
            $cmdUnpin->setType('action');
            $cmdUnpin->setSubType('other');
            $cmdUnpin->save();
        }
        $archiveNote = $this->getCmd('action', 'archiveNote');
        if (!is_object($archiveNote)) {
            $archiveNote = new gkeepCmd();
            $archiveNote->setEqLogic_id($this->getId());
            $archiveNote->setLogicalId('archiveNote');
            $archiveNote->setName(__("Archiver la note", __FILE__));
            $archiveNote->setType('action');
            $archiveNote->setSubType('other');
            $archiveNote->save();
        }
        $cmdUnarchive = $this->getCmd('action', 'unarchiveNote');
        if (!is_object($cmdUnarchive)) {
            $cmdUnarchive = new gkeepCmd();
            $cmdUnarchive->setEqLogic_id($this->getId());
            $cmdUnarchive->setLogicalId('unarchiveNote');
            $cmdUnarchive->setName(__("Désarchiver la note", __FILE__));
            $cmdUnarchive->setType('action');
            $cmdUnarchive->setSubType('other');
            $cmdUnarchive->save();
        }
        $unarchiveNote = $this->getCmd('action', 'restoreNote');
        if (!is_object($unarchiveNote)) {
            $unarchiveNote = new gkeepCmd();
            $unarchiveNote->setEqLogic_id($this->getId());
            $unarchiveNote->setLogicalId('restoreNote');
            $unarchiveNote->setName(__("Restaurer la note", __FILE__));
            $unarchiveNote->setType('action');
            $unarchiveNote->setSubType('other');
            $unarchiveNote->save();
        }
        $cmdDelete = $this->getCmd('action', 'deleteNote');
        if (!is_object($cmdDelete)) {
            $cmdDelete = new gkeepCmd();
            $cmdDelete->setEqLogic_id($this->getId());
            $cmdDelete->setLogicalId('deleteNote');
            $cmdDelete->setName(__("Supprimer la note", __FILE__));
            $cmdDelete->setType('action');
            $cmdDelete->setSubType('other');
            $cmdDelete->setConfiguration('actionConfirm', true);
            $cmdDelete->save();
        }
    }

    public function refresh()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début ', __FILE__));
        log(__CLASS__, 'debug', 'dscfsdffdf ' . self::getNotes($this->getLogicalId()));
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
    }

    public static function synchronize()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
        log(__CLASS__, 'debug', 'dscfsdffdf ' . self::getNotes());
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
    }
  
    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder(__CLASS__) . '/dependency' . ' ' . dirname(__FILE__) . '/../../resources', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

    public static function getPythonPath() {
        return dirname(__FILE__) . '/../../resources/venv/bin/python3';
    }

    public static function dependancy_info() {
        $return = array();
        $return['log'] = log::getPathToLog(__CLASS__ . '_update');
        $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependency';
        if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependency')) {
            $return['state'] = 'in_progress';
        } else {
            if (exec(system::getCmdSudo() . self::getPythonPath() . ' -m pip list | grep -Ewc "keyring|gkeepapi"') < 2) {
                $return['state'] = 'nok';
            } else {
                $return['state'] = 'ok';
            }
        }
        return $return;
    }
  
    public static function login()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
        if (config::byKey('email', __CLASS__, '') == '' || config::byKey('password', __CLASS__, '') == '') {
            throw new Exception(__('Veuillez renseignez un identifiant et un mot de passe de connexion.', __FILE__));
        }
        $cmd = system::getCmdSudo() . self::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py save_master';
        $cmd .= ' --username ' . config::byKey('email', __CLASS__);
        $cmd .= ' --password ' . config::byKey('password', __CLASS__);

        $return = gkeepCmd::sendCmdAndFormatResult($cmd);
        if ($return['code']) {
            return true;
        } else {
            return false;
        }
    }

    public static function getNotes($_id = false)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
        if (config::byKey('email', __CLASS__, '') == '') {
            throw new Exception(__('Veuillez renseignez un identifiant et un mot de passe de connexion.', __FILE__));
        }
      
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', __CLASS__) . ' get_notes';
        $cmd .= ($_id?' --note_id "' . $_id . '"':'');

        $return = self::sendCmdAndFormatResult($cmd);
        if ($_id) {
            self::checkAndCreateEquipementAndCmd($return['message']);
        } else {
            foreach ($return['message'] as $note) {
                self::checkAndCreateEquipementAndCmd($note);
            }
        }
    }

    /**
     * Créé l'équipement avec les valeurs du buffer
     * @param array $_data Tableau des valeurs récupérées dans le buffer
     * @param string $_IP   IP relevée à la réception du buffer
     * @return object $Optoma Retourne l'équipement créé
     */
    public static function checkAndCreateEquipementAndCmd($_note)
    {
      
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . json_encode($_note));
        if ($_note['id'] == '') {
            throw new Exception(__('ID vide, pas d\'équipement créé : ', __FILE__) . json_encode($_note));
        }
        $_note['title'] = ($_note['title'] == '') ? __("Sans titre", __FILE__):$_note['title'];
        $eqLogic = self::byLogicalId($_note['id'], __CLASS__);
        if (!is_object($eqLogic)) {
            $eqLogic = new gkeep();
            $eqLogic->setName($_note['title']);
            $eqLogic->setLogicalId($_note['id']);
            $eqLogic->setObject_id(null);
            $eqLogic->setEqType_name(__CLASS__);
            $eqLogic->setIsEnable(1);
            $eqLogic->setIsVisible(1);
            event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => __CLASS__,
                'message' => __('L\'équipement ', __FILE__) . $eqLogic->getHumanName() . __(' vient d\'être créé', __FILE__),
            ));
        }
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " new object");
        if (isset($_note['type'])) {
            $eqLogic->setConfiguration('type', $_note['type']);
        }
        if (isset($_note['archived'])) {
            $eqLogic->setConfiguration('archived', $_note['archived']);
        }
        if (isset($_note['trashed'])) {
            $eqLogic->setConfiguration('trashed', $_note['trashed']);
        }
        if (isset($_note['pinned'])) {
            $eqLogic->setConfiguration('pinned', $_note['pinned']);
        }
        if (isset($_note['collaborators'])) {
            $eqLogic->setConfiguration('collaborators', $_note['collaborators']);
        }
        if (isset($_note['sort'])) {
            $eqLogic->setConfiguration('sort', $_note['sort']);
        }
        if (isset($_note['color'])) {
            $eqLogic->setConfiguration('color', $_note['color']);
        }
        if (isset($_note['created'])) {
            $eqLogic->setConfiguration('created', date('Y-m-d H:i:s', $_note['created']));
        }
        if (isset($_note['edited'])) {
            $eqLogic->setConfiguration('edited', date('Y-m-d H:i:s', $_note['edited']));
        }
        if (isset($_note['updated'])) {
            $eqLogic->setConfiguration('updated', date('Y-m-d H:i:s', $_note['updated']));
        }
        $eqLogic->save();
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " save object" . count($_note['list']));

        if (isset($_note['list']) && count($_note['list']) > 1) {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " debut list");
            $nbList = count($_note['list']);
            $uncheckedCmds = [];
            $checkedCmds = [];

        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " create cmds");
            for ($i=0;$i<$nbList;$i++) {
                $cmdText[$i] = $eqLogic->getCmd('info', $_note['list'][$i]['id']);
                if (!is_object($cmdText[$i])) {
                    $cmdText[$i] = new gkeepCmd();
                    $cmdText[$i]->setEqLogic_id($eqLogic->getId());
                    $cmdText[$i]->setLogicalId($_note['list'][$i]['id']);
                    $cmdText[$i]->setName($_note['list'][$i]['id']);
                    //$cmdText[$i]->setOrder($_note['list'][$i]['sort']);
                    $cmdText[$i]->setType('info');
                    $cmdText[$i]->setSubType('string');
                    $cmdText[$i]->setTemplate('dashboard', 'gkeep::list');
                    $cmdText[$i]->setTemplate('mobile', 'gkeep::list');
                    $cmdText[$i]->setDisplay('showNameOndashboard', false);
                    $cmdText[$i]->setDisplay('showNameOnmobile', false);
                }
                if ($_note['list'][$i]['checked'] === false) {
                    $uncheckedCmds[] = $cmdText[$i];
                } else {
                    $checkedCmds[] = $cmdText[$i];
                }
                $cmdText[$i]->setConfiguration('id', $_note['list'][$i]['id']);
                $cmdText[$i]->setConfiguration('checked', $_note['list'][$i]['checked']);
                $cmdText[$i]->setConfiguration('sort', $_note['list'][$i]['sort']);
                $cmdText[$i]->save();
                if (is_object($cmdText[$i])) {
                    $cmdText[$i]->event($_note['list'][$i]['text']);
                }
            }
            // Tri des commandes
            $sortedCmds = array_merge($uncheckedCmds, array($eqLogic->getCmd('action','addItem')), $checkedCmds);
            // Réaffectation des ordres
            foreach ($sortedCmds as $index => $cmd) {
                $cmd->setOrder($index);
                $cmd->save();
            }

            // Suppression des commandes retirées
            $allCmds = $eqLogic->getCmd('info');
            foreach ($allCmds as $aCmd) {
                $found = false;
                foreach ($_note['list'] as $item) {
                    if ($aCmd->getLogicalId() === $item['id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    log::add('gkeep','debug', __FUNCTION__ . ' ' . __(' Suppression de la commande ', __FILE__) . $aCmd->getLogicalId() . ' ' . $aCmd->getName());
                    $aCmd->remove();
                }
            }

            $cmdText = $eqLogic->getCmd('action', 'addItem');
            if (!is_object($cmdText)) {
                $cmdText = new gkeepCmd();
                $cmdText->setEqLogic_id($eqLogic->getId());
                $cmdText->setLogicalId('addItem');
                $cmdText->setName(__("Ajouter un item", __FILE__));
                $cmdText->setType('action');
                $cmdText->setSubType('message');
                $cmdText->setTemplate('dashboard', 'gkeep::additem');
                $cmdText->setTemplate('mobile', 'gkeep::additem');
                $cmdText->setDisplay('showNameOndashboard', false);
                $cmdText->setDisplay('showNameOnmobile', false);
                $cmdText->save();
            }
        } elseif (isset($_note['text'])) {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " debut text");
            $cmdText = $eqLogic->getCmd('info', 'text');
            if (!is_object($cmdText)) {
                $cmdText = new gkeepCmd();
                $cmdText->setEqLogic_id($eqLogic->getId());
                $cmdText->setLogicalId('text');
                $cmdText->setName(__("Texte", __FILE__));
                $cmdText->setType('info');
                $cmdText->setSubType('string');
                $cmdText->setTemplate('dashboard', 'gkeep::text');
                $cmdText->setTemplate('mobile', 'gkeep::text');
                $cmdText->setDisplay('showNameOndashboard', false);
                $cmdText->setDisplay('showNameOnmobile', false);
                $cmdText->setConfiguration('sort', $_note['sort']);
                $cmdText->save();
            }
            //$cmdText->event(str_replace('\n','</br>',$_note['list'][$i]['text']));
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " debut tedfsdfdsfdsfdxt" . $_note['text']);
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " debut tedfsdfdsfdsfdxt2" . json_encode($_note['text'], JSON_UNESCAPED_UNICODE));

            $cmdText->event(json_encode($_note['text'], JSON_UNESCAPED_UNICODE));
        }
        $actionCmds = $eqLogic->getCmd('action');
        $infoCmds = $eqLogic->getCmd('info');
        $nbChecked = 0;
        foreach ($infoCmds as $infoCmd) {
            if ($infoCmd->getConfiguration('checked') !== true) {
                $nbChecked++;
            }
        }
        $j = 1;
        foreach ($actionCmds as $actionCmd) {
            if ($actionCmd->getLogicalId() == 'addItem')  continue;
            log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " nb cmd action to set " . (count($infoCmds)+$j));
            $actionCmd->setOrder(count($infoCmds)+$j)->save();
            $j++;
        }

        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
        $eqLogic->refreshWidget();
        return $eqLogic;
    }

    /**
     * Renvoi le lien de l'image de l'object eqLogic
     * @return		string		url		url du fichier image
     */
    public function getImage()
    {
        return 'plugins/gkeep/plugin_info/gkeep_icon.png';
    }
  
    public function actionNote($_action)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        switch ($_action) {
            case 'pinNote':
                $_action = 'pin_note';
                break;
            case 'unpinNote':
                $_action = 'unpin_note';
                break;
            case 'archiveNote':
                $_action = 'archive_note';
                break;
            case 'restoreNote':
                $_action = 'restore_note';
                break;
            case 'deleteNode':
                $_action = 'delete_note';
                break;
            default:
                log::add('gkeep', 'debug', __FUNCTION__ . ' : ' . __('Commande inexistante ', __FILE__) . $_action);
        }
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' ' . $_action;
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function checkItem($_itemId, $_change)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' modify_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= ' --item_id "' . $_itemId . '"';
        $cmd .= isset($_change['checked']) ? ($_change['checked'] ? ' --unchecked' : ' --checked') : '';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function addItem($_itemId, $_change)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' add_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function deleteItem($_itemId)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' delete_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= ' --item_id "' . $_itemId . '"';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function updateNote($_change)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' modify_note';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        $cmd .= isset($_change['title']) ? ' --title "' . $_change['title'] . '"' : '';
        $cmd .= isset($_change['color']) ? ' --color "' . $_change['color'] . '"' : '';
        $cmd .= isset($_change['labels']) ? ' --labels "' . $_change['labels'] . '"' : '';
        $cmd .= isset($_change['annotations']) ? ' --annotations "' . $_change['annotations'] . '"' : '';
        $cmd .= isset($_change['collaborators']) ? ' --collaborators "' . $_change['collaborators'] . '"' : '';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function addNote($_object)
    {
        $cmd = system::getCmdSudo() . gkeep::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/get_notes.py';
        $cmd .= ' --username ' . config::byKey('email', 'gkeep') . ' create_note';
        $cmd .= isset($_object['text']) ? ' --text "' . str_replace('"', '\"', $_object['text']) . '"' : '';
        $cmd .= isset($_object['title']) ? ' --title "' . $_object['title'] . '"' : '';
        $cmd .= isset($_object['color']) ? ' --color "' . $_object['color'] . '"' : '';
        $cmd .= isset($_object['labels']) ? ' --labels "' . $_object['labels'] . '"' : '';
        $cmd .= isset($_object['archived']) ? ($_object['archived'] ? ' --archived' : ' --unarchived') : '';
        $cmd .= isset($_object['pinned']) ? ($_object['pinned'] ? ' --pinned' : ' --unpinned') : '';
        $cmd .= isset($_object['annotations']) ? ' --annotations "' . $_object['annotations'] . '"' : '';
        $cmd .= isset($_object['collaborators']) ? ' --collaborators "' . $_object['collaborators'] . '"' : '';
        if (isset($_object['list']) && is_array($_object['list'])) {
          $cmd .=  ' --list';
          foreach ($_object['list'] as $list) {
              $cmd .= ' "'.str_replace('"', '', json_encode($list[1], JSON_UNESCAPED_UNICODE)).','.($list[0]?'True':'False').'"';
          }
        }
                log::add('gkeep', 'debug', __FUNCTION__ . ' : ' . __('Commande $cmd ', __FILE__) . $cmd);
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    public function sendCmdAndFormatResult($cmd)
    {
        log::add('gkeep', 'info', __FUNCTION__ . ' : ' . __('Commande envoyée : ', __FILE__) . $cmd);
        $config = trim(exec($cmd));
        log::add('gkeep', 'debug', __FUNCTION__ . ' : ' . __('Résultat brut ', __FILE__) . $config);
        $result_json = json_decode($config,true);
        log::add('gkeep', 'info', __FUNCTION__ . ' : ' . __('Résultat array ', __FILE__) . json_encode($result_json)); 
        if (is_array($result_json) && isset($result_json['code'])) {
            return $result_json;
        } else {
            return array('code' => -1, 'message' => $config);
        }
    }

    /**
     * Créé l'équipement avec les valeurs de paramètres
     * @param		string		$_version	Dashboard ou mobile
     * @return		string		$html		Retourne la page générée de l'équipement
     */
	public function toHtml($_version = 'dashboard') {
        if ($this->getDisplay('widgetTmpl') != 1) {
            return parent::toHtml($_version);
        }
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
        $replace['height'] = '500px';

        $replace['#pin_id#'] = ($pin_cmd = $this->getCmd('action', 'pinNote')) && $pin_cmd->getIsVisible() == 1 ? $pin_cmd->getId() : '';
        $replace['#unpin_id#'] = ($unpin_cmd = $this->getCmd('action', 'unpinNote')) && $unpin_cmd->getIsVisible() == 1 ? $unpin_cmd->getId() : '';
        $replace['#archive_id#'] = ($archive_cmd = $this->getCmd('action', 'archiveNote')) && $archive_cmd->getIsVisible() == 1 ? $archive_cmd->getId() : '';
        $replace['#unarchive_id#'] = ($unarchive_cmd = $this->getCmd('action', 'unarchiveNote')) && $unarchive_cmd->getIsVisible() == 1 ? $unarchive_cmd->getId() : '';

        $replace['#restore_id#'] = ($restore_cmd = $this->getCmd('action', 'restoreNote')) && $restore_cmd->getIsVisible() == 1 ? $restore_cmd->getId() : '';
        $replace['#delete_id#'] = ($delete_cmd = $this->getCmd('action', 'deleteNote')) && $delete_cmd->getIsVisible() == 1 ? $delete_cmd->getId() : '';

        $replace['#pinned#'] = in_array($this->getConfiguration('pinned'), array(true, 'true'), true) ? 'isPinned' : '';
        $replace['#archived#'] = in_array($this->getConfiguration('archived'), array(true, 'true'), true) ? 'isArchived' : '';
        $replace['#trashed#'] = in_array($this->getConfiguration('trashed'), array(true, 'true'), true) ? 'isTrashed' : '';

		$_version = jeedom::versionAlias($_version);
		$replace['#calledFrom#'] = __CLASS__;
		switch ($this->getDisplay('layout::' . $_version)) {
			case 'table':
				$replace['#eqLogic_class#'] = 'eqLogic_layout_table';
				$table = self::generateHtmlTable($this->getDisplay('layout::' . $_version . '::table::nbLine', 1), $this->getDisplay('layout::' . $_version . '::table::nbColumn', 1), $this->getDisplay('layout::' . $_version . '::table::parameters'));
				foreach ($this->getCmd(null, null, true) as $cmd) {
					if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
						continue;
					}
					if (isset($replace['#pin_id#']) && $cmd->getId() == $replace['#pin_id#']) {
						continue;
					}
					if (isset($replace['#unpin_id#']) && $cmd->getId() == $replace['#unpin_id#']) {
						continue;
					}
					if (isset($replace['#archive_id#']) && $cmd->getId() == $replace['#archive_id#']) {
						continue;
					}
					if (isset($replace['#unarchive_id#']) && $cmd->getId() == $replace['#unarchive_id#']) {
						continue;
					}
					if (isset($replace['#restore_id#']) && $cmd->getId() == $replace['#restore_id#']) {
						continue;
					}
					if (isset($replace['#delete_id#']) && $cmd->getId() == $replace['#delete_id#']) {
						continue;
					}
					$tag = '#cmd::' . $this->getDisplay('layout::' . $_version . '::table::cmd::' . $cmd->getId() . '::line', 1) . '::' . $this->getDisplay('layout::' . $_version . '::table::cmd::' . $cmd->getId() . '::column', 1) . '#';
					if ($cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
						$table['tag'][$tag] .= '<div class="break"></div>';
					}
					$table['tag'][$tag] .= $cmd->toHtml($_version, '');
					if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
						$table['tag'][$tag] .= '<div class="break"></div>';
					}
				}
				$replace['#cmd#'] = template_replace($table['tag'], $table['html']);
				break;

			default:
				$replace['#eqLogic_class#'] = 'eqLogic_layout_default';
				$cmd_html = '';
				foreach ($this->getCmd(null, null, true) as $cmd) {
					if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
						continue;
					}
					if (isset($replace['#pin_id#']) && $cmd->getId() == $replace['#pin_id#']) {
						continue;
					}
					if (isset($replace['#unpin_id#']) && $cmd->getId() == $replace['#unpin_id#']) {
						continue;
					}
					if (isset($replace['#archive_id#']) && $cmd->getId() == $replace['#archive_id#']) {
						continue;
					}
					if (isset($replace['#unarchive_id#']) && $cmd->getId() == $replace['#unarchive_id#']) {
						continue;
					}
					if (isset($replace['#restore_id#']) && $cmd->getId() == $replace['#restore_id#']) {
						continue;
					}
					if (isset($replace['#delete_id#']) && $cmd->getId() == $replace['#delete_id#']) {
						continue;
					}
					if ($_version == 'dashboard' && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
						$cmd_html .= '<div class="break"></div>';
					}
					$cmd_html .= $cmd->toHtml($_version, '');
					if ($_version == 'dashboard' && $cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
						$cmd_html .= '<div class="break"></div>';
					}
				}
				$replace['#cmd#'] = $cmd_html;
				break;
		}
		if (!isset(self::$_templateArray[$_version])) {
			self::$_templateArray[$_version] = getTemplate('core', $_version, 'gkeepEqlogic', __CLASS__);
		}
        $html = translate::exec($replace, 'plugins/gkeep/core/template/' . $_version . '/gkeepEqlogic.html');
		return $this->postToHtml($_version, template_replace($html, self::$_templateArray[$_version]));
	}
}

class gkeepCmd extends cmd
{
    public static $_widgetPossibility = array('custom' => true);

    public function execute($_options = array())
    {
        $eqLogic = $this->getEqLogic();
        log::add('gkeep', 'debug', __("Action sur ", __FILE__) . $this->getLogicalId() . __(" avec options ", __FILE__) . json_encode($_options));
        $parts = explode('::', $this->getLogicalId());

        switch ($this->getLogicalId()) {
            case 'refresh':
                $eqLogic->refresh();
                break;
            case 'archiveNote':
            case 'unarchiveNote':
            case 'pinNote':
            case 'unpinNote':
            case 'deleteNote':
            case 'restoreNote':
                $eqLogic->actionNote($this->getLogicalId());
                break;
        }
    }

	public function toHtml($_version = 'dashboard', $_options = '') {

        if ($this->getEqlogic()->getConfiguration('type') == 'list') {
            $_options = array(
                'checked' => $this->getConfiguration('checked', ''),
                'keepId' => $this->getConfiguration('id', ''),
                'order' => $this->getConfiguration('sort', '')
            );
        } else {
            $calculatedHeight = intval($this->getEqlogic()->getDisplay('height')) - 100;
            $_options = array(
			    'height' => $calculatedHeight . 'px',
			    'width' => $this->getEqlogic()->getDisplay('width'),
            );
        }
        return parent::toHtml($_version, $_options);
    }
}