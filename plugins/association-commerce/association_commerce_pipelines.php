<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_commerce_association_configuration_navigation($flux) {
	$flux['data']['commerce'] = array(
		'ordre' => 55,
		'label' => 'association_commerce:configuration_titre',
	);
	return $flux;
}

function association_commerce_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'commerce') {
		return $flux;
	}
	$flux['data'][] = array(
		'ordre' => 10,
		'saisies' => array(array(
			'saisie' => 'fieldset',
			'options' => array('nom' => 'commerce', 'label' => _T('association_commerce:configuration_titre')),
			'saisies' => array(
				array('saisie' => 'input', 'options' => array(
					'nom' => 'commerce_rubrique',
					'label' => _T('association_commerce:configuration_rubrique'),
					'type' => 'number',
					'min' => 0,
				)),
				array('saisie' => 'input', 'options' => array(
					'nom' => 'commerce_devise',
					'label' => _T('association_commerce:configuration_devise'),
					'defaut' => 'EUR',
					'size' => 8,
				)),
			),
		)),
	);
	return $flux;
}

function association_commerce_association_capacites($flux) {
	$flux['data']['commerce'] = array(
		'panier' => true,
		'commandes' => true,
		'paiement' => association_plugin_actif('association_paiements'),
	);
	return $flux;
}
