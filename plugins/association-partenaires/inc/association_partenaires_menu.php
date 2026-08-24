<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_partenaires_association_menu_entrees($flux) {
	return association_menu_entrees_ajouter($flux, array(
		'partenaires' => array('ordre' => 42, 'label' => _T('association_partenaires:partenaires'), 'exec' => 'partenaires', 'icone' => 'partenaires'),
	));
}
