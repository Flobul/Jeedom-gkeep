<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$gkeep_widgets = array();
$eqLogics = eqLogic::byType('gkeep');
usort($eqLogics, 'gkeep::compareEqLogic');
foreach ($eqLogics as $eqLogic) {
    $gkeep_widgets[] = array('widget' => $eqLogic->toHtml('dashboard'), 'type' => $eqLogic->getConfiguration('type'));
}
echo '<div class="div_displayEquipement" style="width: 100%;">';
echo '<div class="row" >';
foreach ($gkeep_widgets as $widget) {
    if ($widget['type'] == 'Note') {
        echo '<div class="col-md-3 col-sm-6 col-xs-12">';
    } else {
        echo '<div class="col-md-2 col-sm-6 col-xs-12">';
    }
    echo $widget['widget'];
    echo '</div>';
    echo '<div class="clearfix visible-xs-block"></div>'; // Ajout d'une ligne claire pour les écrans extra-small
}
echo '</div>';
echo '</div>';
?>