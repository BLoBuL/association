<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_commerce_association_configuration_navigation($flux) {
	$flux['data']['commerce'] = [
		'ordre' => 55,
		'label' => 'association_commerce:configuration_titre',
	];
	return $flux;
}

function association_commerce_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'commerce') {
		return $flux;
	}
	$flux['data'][] = [
		'ordre' => 10,
		'saisies' => [[
			'saisie' => 'fieldset',
			'options' => ['nom' => 'commerce', 'label' => _T('association_commerce:configuration_titre')],
			'saisies' => [
				['saisie' => 'input', 'options' => [
					'nom' => 'commerce_rubrique',
					'label' => _T('association_commerce:configuration_rubrique'),
					'type' => 'number',
					'min' => 0,
				]],
				['saisie' => 'input', 'options' => [
					'nom' => 'commerce_devise',
					'label' => _T('association_commerce:configuration_devise'),
					'defaut' => 'EUR',
					'size' => 8,
				]],
			],
		]],
	];
	return $flux;
}

function association_commerce_association_capacites($capacites) {
	$capacites['commerce'] = [
		'plugin' => 'association_commerce',
		'panier' => true,
		'commandes' => true,
		'paiement' => association_plugin_actif('association_paiements'),
		'contrats' => association_plugin_actif('contrats'),
	];
	return $capacites;
}

function association_commerce_association_contrat_demander($demande) {
	if (!association_plugin_actif('contrats')) {
		return $demande;
	}

	include_spip('inc/contrats');
	if (!function_exists('contrats_creer_ou_mettre_a_jour_depuis_flux')) {
		return $demande;
	}

	$action = (string) ($demande['action'] ?? 'synchroniser');
	if (!in_array($action, ['creer', 'synchroniser'], true)) {
		return $demande;
	}

	try {
		$id_contrat = contrats_creer_ou_mettre_a_jour_depuis_flux($demande);
		$demande['id_contrat'] = (int) $id_contrat;
		$demande['contrat_cree'] = $id_contrat > 0;
	} catch (Throwable $e) {
		$demande['erreur'] = $e->getMessage();
		$demande['contrat_cree'] = false;
		association_log('commerce', 'Création de contrat ignorée : ' . $e->getMessage(), 'erreur');
	}

	return $demande;
}
