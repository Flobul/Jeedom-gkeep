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
  
    /**
     * Version du plugin.
     *
     * @var string
     */
    public static $_pluginVersion = '0.95';
  
    /**
     * Tableau des templates.
     *
     * @var array
     */

    public static $_templateArray = array();

    /**
     * Tableau des possibilités de widget pour la configuration.
     *
     * @var array
     */
    public static $_widgetPossibility = array(
        'custom' => true,
        'parameters' => array(
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
			'bgEqLogic' => array(
				'name' => 'Couleur de fond',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			)
        )
    );

    /*
     * *************************Méthodes statiques******************************
     */

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
     * Synchronise les données du plugin.
     *
     * @return array Les notes de synchronisation.
     */
    public static function synchronize()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
        $result = self::getNotes();
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
        return $result;
    }

    /**
     * Installation des dépendances du plugin.
     *
     * @return array Les informations d'installation des dépendances.
     */
    public static function dependancy_install()
    {
        log::remove(__CLASS__ . '_update');
        return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder(__CLASS__) . '/dependency' . ' ' . dirname(__FILE__) . '/../../resources', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

    /**
     * Obtient les informations sur les dépendances du plugin.
     *
     * @return array Les informations sur les dépendances.
     */
    public static function dependancy_info()
    {
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

    /**
     * Obtient le chemin d'accès vers Python.
     *
     * @return string Le chemin d'accès vers Python.
     */
    public static function getPythonPath()
    {
        return dirname(__FILE__) . '/../../resources/venv/bin/python3';
    }

    /**
     * Récupère le jeton associé à l'email de connexion.
     *
     * @return array Le jeton de connexion.
     */
    public static function getTokenByUsername($_account) {
        if ($key = array_search($_account, config::byKey('email', __CLASS__))) {
            return config::byKey('token', __CLASS__)[$key];
        }
        return false;
    }

    /**
     * Effectue la connexion à Google Keep.
     *
     * @throws Exception Si l'identifiant ou le mot de passe de connexion n'est pas renseigné.
     *
     * @return bool True si la connexion est réussie, sinon False.
     */
    public static function login($_id = null)
    {
        $cmd = array();
        for ($i = 1; $i <= config::byKey('max_account_number', __CLASS__); $i++) {
            if ($_id && $_id != $i)  continue;
            $email = config::byKey('email', __CLASS__, '')[$i];

            log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
            if ($email == '' || config::byKey('password', __CLASS__, '')[$i] == '') {
                throw new Exception(__('Veuillez renseignez un identifiant et un mot de passe de connexion.', __FILE__));
            }
            $cmd[$i] = ' --username ' . $email;
            $cmd[$i] .= ' save_master_token';
            $cmd[$i] .= ' --password ' . config::byKey('password', __CLASS__)[$i];

            $return[$i] = self::sendCmdAndFormatResult($cmd[$i]);
            if (isset($return[$i]['result'])) {
                log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('Jeton récupéré ', __FILE__). $return[$i]['result']['token']);
                $token = array($i => $return[$i]['result']['token']) + config::byKey('token', __CLASS__);
                config::save('token', $token, __CLASS__);
            }
        }
        return $return;
    }

    public static function getMasterToken($_id = null)
    {
        $cmd = array();
        for ($i = 1; $i <= config::byKey('max_account_number', __CLASS__); $i++) {
            if ($_id && $_id != $i)  continue;
            $email = config::byKey('email', __CLASS__, '')[$i];

            log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__));
            if ($email == '' || config::byKey('password', __CLASS__, '')[$i] == '') {
                throw new Exception(__('Veuillez renseignez un identifiant et un mot de passe de connexion.', __FILE__));
            }
            $cmd[$i] = ' --username ' . $email;
            $cmd[$i] .= ' get_master_token';

            $return[$i] = self::sendCmdAndFormatResult($cmd[$i]);
            if (isset($return[$i]['result'])) {
                log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('Jeton récupéré ', __FILE__). $return[$i]['result']['token']);
                $token = array($i => $return[$i]['result']['token']) + config::byKey('token', __CLASS__);
                config::save('token', $token, __CLASS__);
            }
        }
        return $return;
    }

    /**
     * Crée une nouvelle note avec les informations spécifiées.
     *
     * @param array $_object Les informations de la note à créer.
     *
     * @return array Le résultat de la création de la note.
     */
    public static function addNote($_object, $_account)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' ' . $_account . ' ' . json_encode($_object));
        $cmd = ' --username ' . $_account;
        $cmd .= ' --token "' . gkeep::getTokenByUsername($_account) . '"';
        $cmd .= ' create_note';
        $cmd .= isset($_object['text']) ? ' --text "' . str_replace('"', '\"', $_object['text']) . '"' : '';
        $cmd .= isset($_object['title']) ? ' --title "' . $_object['title'] . '"' : '';
        $cmd .= isset($_object['color']) ? ' --color "' . $_object['color'] . '"' : '';
        $cmd .= isset($_object['labels']) ? ' --labels "' . $_object['labels'] . '"' : '';
        $cmd .= isset($_object['archived']) ? ($_object['archived'] ? ' --archived' : ' --unarchived') : '';
        $cmd .= isset($_object['pinned']) ? ($_object['pinned'] ? ' --pinned' : ' --unpinned') : '';
        $cmd .= isset($_object['annotations']) ? ' --annotations "' . $_object['annotations'] . '"' : '';
        $cmd .= isset($_object['collaborators']) ? ' --collaborators "' . $_object['collaborators'] . '"' : '';
        if (isset($_object['list']) && is_array($_object['list'])) {
            $cmd .= ' --list';
            foreach ($_object['list'] as $list) {
                $cmd .= ' "'.str_replace('"', '', json_encode($list[1], JSON_UNESCAPED_UNICODE)).','.($list[0]?'True':'False').'"';
            }
        }
        $return = self::sendCmdAndFormatResult($cmd);
        self::getNotes();
        return $return;
    }

    /**
     * Obtient les notes de Google Keep.
     *
     * @param bool $_id L'identifiant de la note (facultatif).
     *
     * @throws Exception Si l'identifiant de connexion n'est pas renseigné.
     *
     * @return array Les notes récupérées.
     */
    public static function getNotes($_id = false, $_account = null)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . ' ' . $_id . ' & ' . $_account);
        for ($i = 1; $i <= config::byKey('max_account_number', __CLASS__); $i++) {
            $email = config::byKey('email', __CLASS__, '')[$i];
            if ($_account && $_account != $email)  continue;
            if ($email == '') {
                throw new Exception(__('Veuillez renseignez un identifiant et un mot de passe de connexion.', __FILE__));
            }
            $cmd = ' --username ' . $email;
            $cmd .= ' --token "' . config::byKey('token', __CLASS__)[$i] . '"';
            $cmd .= ' get_notes';
            $cmd .= ($_id?' --note_id "' . $_id . '"':'');

            $return = self::sendCmdAndFormatResult($cmd);
            $result = array();
            if ($_id) {
                $result[] = self::checkAndCreateEquipementAndCmd($return['result'], $email);
            } else {
                foreach ($return['result'] as $note) {
                    $result[] = self::checkAndCreateEquipementAndCmd($note, $email);
                }
                self::removeOldEqlogic($return['result'], $email);
            }
        }
        return $result;
    }

    /**
     * Supprime les anciens équipements qui ne correspondent plus aux notes spécifiées des comptes indiqués.
     *
     * @param array $_notes Les notes actuelles.
     *
     * @return void
     */
    public static function removeOldEqlogic($_notes)
    {
        if (count($_notes) < 1) return;
        $eqLogicsToRemove = array();
        foreach (eqLogic::byType(__CLASS__) as $eqLogic) {
            $found = false;
            if ($eqLogic->getConfiguration('account') != $email) { //autre compte, donc on passe
                $found = true;
                continue;
            }
            foreach ($_notes as $note) {
                if ($eqLogic->getLogicalId() == $note['id']) { // on cherche les notes existantes
                    $found = true;
                    break;
                }
            }
            if (!$found) { // et on compte les non existantes
                $eqLogicsToRemove[] = $eqLogic;
            }
        }
        foreach ($eqLogicsToRemove as $eqLogic) {
            log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('Suppression de la note ', __FILE__) . $eqLogic->getLogicalId() . ' ' . $eqLogic->getName());
            $eqLogic->remove();
        }
    }

    /**
     * Compare les équipements et trie en fonction de la valeur de pinned/archived et de sort.
     *
     * @param object $a Equipement.
     * @param object $b Equipement.
     *
     * @return void
     */
    public static function compareEqLogic($a, $b) {
        $pinnedA = $a->getConfiguration('pinned', false);
        $pinnedB = $b->getConfiguration('pinned', false);
        $archivedA = $a->getConfiguration('archived', false);
        $archivedB = $b->getConfiguration('archived', false);
        $sortA = $a->getConfiguration('sort', 0);
        $sortB = $b->getConfiguration('sort', 0);

        // Si les éqLogic sont épinglés et non archivés, ils sont prioritaires
        if ($pinnedA && !$archivedA) {
            if (!$pinnedB || $archivedB) {
                return -1;
            }
        } elseif ($pinnedB && !$archivedB) {
            return 1;
        }

        // Si les éqLogic sont archivés, ils sont placés en dernier
        if ($archivedA && !$archivedB) {
            return 1;
        } elseif (!$archivedA && $archivedB) {
            return -1;
        }

        // Si les éqLogic ont le même statut d'épinglage et d'archivage, on les trie par ordre décroissant
        return $sortB - $sortA;
    }
  
    /**
     * Convertit une couleur de Google Keep en code couleur hexadécimal.
     *
     * @param string $_color La couleur de Google Keep.
     *
     * @return string La couleur convertie en code hexadécimal.
     */
    public static function getColor($_color)
    {
        $colorMap = array(
            'BROWN' => '#e1caac',
            'ORANGE' => '#f1be42',
            'RED' => '#e49085',
            'YELLOW' => '#fdf487',
            'GREEN' => '#d6fd9d',
            'PINK' => '#f5d0e6',
            'PURPLE' => '#d0aff5',
            'BLUE' => '#d2eef6',
            'GRAY' => '#e8e9ec',
            'CERULEAN' => '#b3caf5',
            'TEAL' => '#bafceb',
            'DEFAULT' => '#fefefe'
        );

        return isset($colorMap[$_color]) ? $colorMap[$_color] : $_color;
    }

    /**
     * Créé l'équipement avec les valeurs du buffer
     * @param array $_data Tableau des valeurs récupérées dans le buffer
     * @param string $_IP   IP relevée à la réception du buffer
     * @return object $Optoma Retourne l'équipement créé
     */
    public static function checkAndCreateEquipementAndCmd($_note, $_account = null)
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
            $eqLogic->setDisplay('widgetTmpl', 1);
            event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => __CLASS__,
                'message' => __('L\'équipement ', __FILE__) . $eqLogic->getHumanName() . __(' vient d\'être créé', __FILE__),
            ));
        }
        $eqLogic->setConfiguration('account', $_account);
        $eqLogic->setDisplay('widgetTmpl', 1);

        if (isset($_note['type'])) {
            $eqLogic->setConfiguration('type', $_note['type']);
            if ($_note['type'] == 'Note') {
                $eqLogic->setDisplay('width', '350px');
            }
        }
        if (isset($_note['archived'])) {
            $eqLogic->setConfiguration('archived', boolval($_note['archived']));
        }
        if (isset($_note['trashed'])) {
            $eqLogic->setConfiguration('trashed', boolval($_note['trashed']));
        }
        if (isset($_note['pinned'])) {
            $eqLogic->setConfiguration('pinned', boolval($_note['pinned']));
        }
        if (isset($_note['collaborators'])) {
            $eqLogic->setConfiguration('collaborators', $_note['collaborators']);
        }
        if (isset($_note['sort'])) {
            $eqLogic->setConfiguration('sort', $_note['sort']);
        }
        if (isset($_note['color'])) {
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicdashboard', self::getColor($_note['color']));
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicdashboard-default', '0');
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicdashboard-transparent', '0');
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicmobile', self::getColor($_note['color']));
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicmobile-default', '0');
		    $eqLogic->setDisplay('advanceWidgetParameterbgEqLogicmobile-transparent', '0');
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

        // DEBUT des Listes
        if (isset($_note['list']) && count($_note['list']) > 0) {
            $nbList = count($_note['list']);
            $uncheckedCmds = [];
            $checkedCmds = [];

            $cmdText = array();
            for ($i=0;$i<$nbList;$i++) {
                $cmdText[$i] = $eqLogic->getCmd('info', $_note['list'][$i]['id']);
                if (!is_object($cmdText[$i])) {
                    $cmdText[$i] = new gkeepCmd();
                    $cmdText[$i]->setEqLogic_id($eqLogic->getId());
                    $cmdText[$i]->setLogicalId($_note['list'][$i]['id']);
                    $cmdText[$i]->setName($_note['list'][$i]['id']);
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
                    log::add(__CLASS__,'debug', __FUNCTION__ . ' ' . __(' Suppression de la commande ', __FILE__) . $aCmd->getLogicalId() . ' ' . $aCmd->getName());
                    $aCmd->remove();
                }
            }

            $cmdaddItem = $eqLogic->getCmd('action', 'addItem');
            if (!is_object($cmdaddItem)) {
                $cmdaddItem = new gkeepCmd();
                $cmdaddItem->setEqLogic_id($eqLogic->getId());
                $cmdaddItem->setLogicalId('addItem');
                $cmdaddItem->setName(__("Ajouter un item", __FILE__));
                $cmdaddItem->setType('action');
                $cmdaddItem->setSubType('message');
                $cmdaddItem->setTemplate('dashboard', 'gkeep::additem');
                $cmdaddItem->setTemplate('mobile', 'gkeep::additem');
                $cmdaddItem->setDisplay('showNameOndashboard', false);
                $cmdaddItem->setDisplay('showNameOnmobile', false);
                $cmdaddItem->setConfiguration('updateCmdToValue', '#message#');
                $cmdaddItem->save();
            }
            $cmdDelItem = $eqLogic->getCmd('action', 'deleteItem');
            if (!is_object($cmdDelItem)) {
                $cmdDelItem = new gkeepCmd();
                $cmdDelItem->setEqLogic_id($eqLogic->getId());
                $cmdDelItem->setLogicalId('deleteItem');
                $cmdDelItem->setName(__("Supprimer un item", __FILE__));
                $cmdDelItem->setType('action');
                $cmdDelItem->setSubType('message');
                $cmdDelItem->setIsVisible(0);
                //$cmdDelItem->setTemplate('dashboard', 'gkeep::additem');
                //$cmdDelItem->setTemplate('mobile', 'gkeep::additem');
                $cmdDelItem->setDisplay('showNameOndashboard', false);
                $cmdDelItem->setDisplay('showNameOnmobile', false);
                $cmdDelItem->setDisplay('title_disable', true);
                $cmdDelItem->setConfiguration('updateCmdToValue', '#message#');
                $cmdDelItem->save();
            }
            // Tri des commandes
            $sortedCmds = array_merge($uncheckedCmds, [$eqLogic->getCmd('action','addItem')], $checkedCmds);
            // Réaffectation des ordres
            foreach ($sortedCmds as $index => $cmd) {
                $cmd->setOrder($index);
                $cmd->save();
            }

        // FIN des Listes - DEBUT des Notes
        } elseif (isset($_note['text'])) {
            log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début', __FILE__) . " text");
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
            $cmdText->event(json_encode($_note['text'], JSON_UNESCAPED_UNICODE));
        }
        // FIN des Notes

        if (isset($_note['blobs']) && $_note['blobs'] > 0) {
            $cmdBlob = array();
            foreach ($_note['blobs'] as $i => $blob) {
                $mediatype = (explode('/',$blob['mimetype']))[0];
                $cmdBlob[$i] = $eqLogic->getCmd('info', 'blob_' . $i);
                if (!is_object($cmdBlob[$i])) {
                    $cmdBlob[$i] = new gkeepCmd();
                }
                $cmdBlob[$i]->setEqLogic_id($eqLogic->getId());
                $cmdBlob[$i]->setLogicalId('blob_' . $i);
                $cmdBlob[$i]->setName(__("Fichier ", __FILE__) . $i);
                $cmdBlob[$i]->setType('info');
                $cmdBlob[$i]->setSubType('string');
                $cmdBlob[$i]->setTemplate('dashboard', 'gkeep::' . $mediatype);
                $cmdBlob[$i]->setTemplate('mobile', 'gkeep::' . $mediatype);
                $cmdBlob[$i]->setDisplay('showNameOndashboard', false);
                $cmdBlob[$i]->setDisplay('showNameOnmobile', false);
                $cmdBlob[$i]->setConfiguration('url', $blob['url']);
                $cmdBlob[$i]->setConfiguration('extension', $blob['extension']);
                $cmdBlob[$i]->setConfiguration('mediatype', $mediatype);
                $cmdBlob[$i]->setConfiguration('mimetype', $blob['mimetype']);
                $cmdBlob[$i]->save();
                $cmdBlob[$i]->event($blob['file']);
            }
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

    /*
     * *************************Méthodes d'instance******************************
     */

    /**
     * Exécute une commande en utilisant sudo et le chemin Python spécifié.
     *
     * @param string $command La commande à exécuter.
     *
     * @return string Le résultat de la commande.
     *
     * @throws Exception En cas d'erreur lors de l'exécution de la commande.
     */
    private function executeCommand($_command)
    {
        $cmd = system::getCmdSudo() . self::getPythonPath() . ' ' . dirname(__FILE__) . '/../../resources/gkeepmanager.py ' . $_command;
        log::add(__CLASS__, 'debug', __('Commande envoyée : ', __FILE__) . $cmd);

        $result = shell_exec($cmd);
        if ($result === false) {
            throw new Exception(__('Erreur lors de l\'exécution de la commande : ', __FILE__) . $cmd);
        }
        return $result;
    }

    /**
     * Envoie une commande et formate le résultat.
     *
     * @param string $cmd La commande à envoyer.
     *
     * @return array Le résultat de la commande formaté.
     */
    public function sendCmdAndFormatResult($cmd)
    {
        $result = self::executeCommand($cmd);
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('Résultat brut ', __FILE__) . $result);
        $result_json = json_decode($result,true);
        log::add(__CLASS__, 'info', __FUNCTION__ . ' : ' . __('Résultat array ', __FILE__) . json_encode($result_json)); 
        if (is_array($result_json) && isset($result_json['code'])) {
            if ($result_json['code'] !== 0) {
                log::add(__CLASS__, 'warning',__('Erreur lors de l\'exécution de la commande : ', __FILE__) . $cmd);
            }
           log::add(__CLASS__, 'info', __FUNCTION__ . ' : ' . __('Résultat code ', __FILE__) . json_encode($result_json)); 
            return $result_json;
        } else {
            log::add(__CLASS__, 'warning',__('Erreur lors de l\'exécution de la commande : ', __FILE__) . $cmd);
        }
    }

    /**
     * Méthode appellée avant la création de l'objet
     *
     * Active et affiche l'objet
     */
    public function preInsert()
    {
        $this->setIsEnable(1);
        $this->setIsVisible(1);
    }

    /**
     * Méthode appellée après la création de l'objet
     *
     * Ajoute la commande refresh
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

    /**
     * Actualise la note.
     *
     * @return array Les informations mises à jour de la note.
     */
    public function refresh()
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('début ', __FILE__));
        return self::getNotes($this->getLogicalId(), $this->getConfiguration('account'));
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('fin', __FILE__));
    }

    /**
     * Renvoi le lien de l'image de l'object eqLogic
     * @return		string		url		url du fichier image
     */
    public function getImage()
    {
        return array('img' => 'plugins/gkeep/plugin_info/gkeep_icon.png', 'color' => $this->getConfiguration('color', 'DEFAULT'));
    }

    /**
     * Effectue une action sur la note.
     *
     * @param string $_action L'action à effectuer sur la note.
     *
     * @return array Le résultat de l'action.
     */
    public function actionNote($_action)
    {
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
                log::add(__CLASS__, 'debug', __FUNCTION__ . ' : ' . __('Commande inexistante ', __FILE__) . $_action);
        }
        $cmd = ' --username ' . $this->getConfiguration('account');
        $cmd .= ' --token "' . gkeep::getTokenByUsername($this->getConfiguration('account')) . '"';
        $cmd .= ' ' . $_action;
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    /**
     * Vérifie et modifie un élément de la note.
     *
     * @param string $_itemId  L'identifiant de l'élément.
     * @param array  $_change  Les modifications à apporter.
     *
     * @return array Le résultat de la modification.
     */
    public function checkItem($_itemId, $_change)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' ' . $_itemId . ' ' . json_encode($_change));
        $cmd = ' --username ' . $this->getConfiguration('account');
        $cmd .= ' --token "' . gkeep::getTokenByUsername($this->getConfiguration('account')) . '"';
        $cmd .= ' modify_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= ' --item_id "' . $_itemId . '"';
        $cmd .= isset($_change['checked']) ? ($_change['checked'] == "true" ? ' --checked' : ' --unchecked') : '';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    /**
     * Ajoute un nouvel élément à la note.
     *
     * @param array $_change Les informations de l'élément à ajouter.
     *
     * @return array Le résultat de l'ajout de l'élément.
     */
    public function addItem($_change)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' ' . json_encode($_change));
        $cmd = ' --username ' . $this->getConfiguration('account');
        $cmd .= ' --token "' . gkeep::getTokenByUsername($this->getConfiguration('account')) . '"';
        $cmd .= ' add_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= isset($_change['text']) ? ' --text "' . str_replace('"', '\"', $_change['text']) . '"' : '';
        log::add(__CLASS__, 'debug', __("Valeur à envoyer addItem ", __FILE__) . json_encode($cmd));
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    /**
     * Supprime un élément de la note.
     *
     * @param string $_itemId L'identifiant de l'élément à supprimer.
     *
     * @return array Le résultat de la suppression de l'élément.
     */
    public function deleteItem($_itemId)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' ' . $_itemId);
        $cmd = ' --username ' . $this->getConfiguration('account');
        $cmd .= ' --token "' . gkeep::getTokenByUsername($this->getConfiguration('account')) . '"';
        $cmd .= ' delete_item';
        $cmd .= ' --note_id "' . $this->getLogicalId() . '"';
        $cmd .= ' --item_id "' . $_itemId . '"';
        $return = self::sendCmdAndFormatResult($cmd);
        $this->refresh();
        return $return;
    }

    /**
     * Met à jour la note avec les modifications spécifiées.
     *
     * @param array $_change Les modifications à apporter à la note.
     *
     * @return array Le résultat de la mise à jour de la note.
     */
    public function updateNote($_change)
    {
        log::add(__CLASS__, 'debug', __FUNCTION__ . ' ' . json_encode($_object));
        $cmd = ' --username ' . $this->getConfiguration('account');
        $cmd .= ' --token "' . gkeep::getTokenByUsername($this->getConfiguration('account')) . '"';
        $cmd .= ' modify_note';
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
        $replace['#height#'] = '500px';

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
        log::add(__CLASS__, 'info', __FUNCTION__ . ' : ' . __('Résultat $_layout ', __FILE__) . $this->getDisplay('layout::' . $_version)); 
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
    /**
     * Tableau des possibilités de widget pour la configuration.
     *
     * @var array
     */
    public static $_widgetPossibility = array('custom' => true);
  
    /**
     * Exécute l'action spécifiée avec les options fournies.
     *
     * @param array $_options Les options pour l'action.
     *
     * @throws Exception Si le message et le sujet sont vides pour l'action "message".
     *
     * @return void
     */
    public function execute($_options = array())
    {
        $eqLogic = $this->getEqLogic();
        log::add('gkeep', 'debug', __("Action sur ", __FILE__) . $this->getLogicalId() . __(" avec options ", __FILE__) . json_encode($_options));
        $parts = explode('::', $this->getLogicalId());

        switch ($this->getSubType()) {
            case 'slider':
                $replace['#slider#'] = floatval($_options['slider']);
                break;
            case 'color':
                $replace['#color#'] = $_options['color'];
                break;
            case 'select':
                $replace['#select#'] = $_options['select'];
                break;
            case 'message':
                $replace['#title#'] = $_options['title'];
                $replace['#message#'] = $_options['message'];
                if ($_options['message'] == '' && $_options['title'] == '') {
                  throw new Exception(__('Le message et le sujet ne peuvent pas être vide', __FILE__));
                }
                break;
        }
        $value = str_replace(array_keys($replace),$replace,$this->getConfiguration('updateCmdToValue', ''));
        log::add('gkeep', 'debug', __("Valeur à envoyer ", __FILE__) . $value);
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
            case 'addItem':
                $eqLogic->addItem(array('text' => $value));
            case 'deleteItem':
                $eqLogic->deleteItem($value);
        }
        return;
    }

    /**
     * Génère le code HTML pour le widget en fonction de la version et des options spécifiées.
     *
     * @param string $_version La version du widget.
     * @param mixed $_options Les options du widget.
     *
     * @return string Le code HTML du widget.
     */
	public function toHtml($_version = 'dashboard', $_options = '')
    {

        if ($this->getEqlogic()->getConfiguration('type') == 'List') {
            $_options = array(
                'checked' => strval($this->getConfiguration('checked', '')),
                'keepId' => $this->getConfiguration('id', ''),
                'order' => $this->getConfiguration('sort', '')
            );
        } else {
            $calculatedHeight = intval($this->getEqlogic()->getDisplay('height')) - 100;
            $_options = array(
			    'height' => $calculatedHeight . 'px',
                'keepId' => $this->getConfiguration('id', ''),
			    'width' => $this->getEqlogic()->getDisplay('width'),
            );
        }
        return parent::toHtml($_version, $_options);
    }
}