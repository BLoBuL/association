<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_partenaires_association_configuration_navigation($flux) {
	$flux['data']['partenaires'] = array('ordre' => 58, 'label' => 'association_partenaires:configuration_titre');
	return $flux;
}

function association_partenaires_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'partenaires') { return $flux; }
	$flux['data'][] = array('ordre' => 10, 'saisies' => array(array(
		'saisie' => 'fieldset',
		'options' => array('nom' => 'partenaires', 'label' => _T('association_partenaires:configuration_titre')),
		'saisies' => array(
			array('saisie' => 'input', 'options' => array('nom' => 'partenaires_titre_public', 'label' => _T('association_partenaires:configuration_titre_public'), 'defaut' => _T('association_partenaires:partenaires'))),
		),
	)));
	return $flux;
}

function association_partenaires_association_capacites($capacites) {
	$capacites['partenaires'] = array(
		'plugin' => 'association_partenaires',
		'organisations' => true,
		'contacts' => true,
	);
	return $capacites;
}
