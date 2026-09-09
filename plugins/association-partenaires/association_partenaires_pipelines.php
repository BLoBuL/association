<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_partenaires_association_configuration_navigation($flux) {
	$flux['data']['partenaires'] = [
		'ordre' => 58,
		'label' => 'association_partenaires:configuration_titre',
	];
	return $flux;
}

function association_partenaires_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'partenaires') {
		return $flux;
	}
	$flux['data'][] = [
		'ordre' => 10,
		'saisies' => [[
			'saisie' => 'fieldset',
			'options' => ['nom' => 'partenaires', 'label' => _T('association_partenaires:configuration_titre')],
			'saisies' => [
				['saisie' => 'input', 'options' => ['nom' => 'partenaires_titre_public', 'label' => _T('association_partenaires:configuration_titre_public'), 'defaut' => _T('association_partenaires:partenaires')]],
			],
		]],
	];
	return $flux;
}

function association_partenaires_association_capacites($capacites) {
	$capacites['partenaires'] = [
		'plugin' => 'association_partenaires',
		'organisations' => true,
		'contacts' => true,
	];
	return $capacites;
}
