<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_bannieres_association_configuration_navigation($flux) {
	$flux['data']['bannieres'] = [
		'ordre' => 82,
		'label' => 'association_bannieres:configuration_titre',
	];
	return $flux;
}
function association_bannieres_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'bannieres') {
		return $flux;
	}
	$flux['data'][] = [
		'ordre' => 10,
		'saisies' => [['saisie' => 'fieldset', 'options' => ['nom' => 'bannieres', 'label' => _T('association_bannieres:configuration_titre')], 'saisies' => [
			['saisie' => 'input', 'options' => ['nom' => 'bannieres_emplacement_defaut', 'label' => _T('association_bannieres:configuration_emplacement'), 'defaut' => 'principal']],
		]]],
	];
	return $flux;
}
function association_bannieres_association_capacites($capacites) {
	$capacites['bannieres'] = [
		'plugin' => 'association_bannieres',
		'affichage_public' => true,
		'emplacements' => true,
	];
	return $capacites;
}
