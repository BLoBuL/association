<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_bannieres_association_configuration_navigation($flux) {
	$flux['data']['bannieres'] = array('ordre' => 82, 'label' => 'association_bannieres:configuration_titre');
	return $flux;
}
function association_bannieres_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'bannieres') { return $flux; }
	$flux['data'][] = array('ordre' => 10, 'saisies' => array(array('saisie' => 'fieldset', 'options' => array('nom' => 'bannieres', 'label' => _T('association_bannieres:configuration_titre')), 'saisies' => array(
		array('saisie' => 'input', 'options' => array('nom' => 'bannieres_emplacement_defaut', 'label' => _T('association_bannieres:configuration_emplacement'), 'defaut' => 'principal')),
	))));
	return $flux;
}
function association_bannieres_association_capacites($flux) {
	$flux['data']['bannieres'] = array('affichage_public' => true, 'emplacements' => true);
	return $flux;
}
