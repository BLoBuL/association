<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_evenements_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'activites' => array('ordre' => 30, 'label' => _T('association_evenements:titre_onglet_activites'), 'exec' => 'activites', 'icone' => 'activites'),
	));
}
