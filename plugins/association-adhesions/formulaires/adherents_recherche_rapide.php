<?php

/*\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/adherents_search_context');
function formulaires_adherents_recherche_rapide_charger_dist() {
	$context = AdherentsSearchContext::fromRequest();
	$defaults = [
		'_input_prenom' => $context->prenom ?: (_request('_input_prenom') ?: ''),
		'_input_nom_famille' => $context->nom ?: (_request('_input_nom_famille') ?: ''),
		'_input_email' => $context->email ?: (_request('_input_email') ?: ''),
		'_input_mobile' => $context->mobile ?: (_request('_input_mobile') ?: ''),
	];

	$saisies = [
		[
			'saisie' => 'hidden',
			'options' => [
				'nom' => 'recherche',
				'defaut' => 'rapide',
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'nom' => '_input_prenom',
				'label' => _T('association_adhesions:activite_form_public_prenom_inscrit'),
				'defaut' => $defaults['_input_prenom'],
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'nom' => '_input_nom_famille',
				'label' => _T('association_adhesions:activite_form_public_nom_inscrit'),
				'defaut' => $defaults['_input_nom_famille'],
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'nom' => '_input_email',
				'label' => _T('association_adhesions:activite_form_public_email_inscrit'),
				'defaut' => $defaults['_input_email'],
			],
		],
		[
			'saisie' => 'input',
			'options' => [
				'nom' => '_input_mobile',
				'label' => _T('association_adhesions:activite_form_public_tel_inscrit'),
				'defaut' => $defaults['_input_mobile'],
			],
		],
	];

	return array_merge($defaults, [
		'_saisies' => $saisies,
		'recherche' => 'rapide',
	]);
}
function formulaires_adherents_recherche_rapide_verifier_dist() {
	$erreurs = [];
	if (count($erreurs)) {
		$erreurs['message_erreur'] = 'Votre saisie contient des erreurs !';
	}
	return $erreurs;
}

function formulaires_adherents_recherche_rapide_traiter_dist() {
	include_spip('inc/session');

	// Sauvegarder les critères de recherche rapide en session
	$criteres_recherche = [];

	// Récupérer les champs de recherche
	$champs = ['_input_nom_famille', '_input_prenom', '_input_email', '_input_mobile'];
	foreach ($champs as $champ) {
		$valeur = _request($champ);
		if (!empty($valeur)) {
			$criteres_recherche[$champ] = $valeur;
		}
	}

	// Sauvegarder en session si des critères sont présents
	if (!empty($criteres_recherche)) {
		$criteres_recherche['recherche'] = 'rapide'; // Marqueur
		session_set('adherents_recherche_rapide', $criteres_recherche);

		association_log('adherents', 'Recherche rapide sauvegardée en session: ' . count($criteres_recherche) . ' critères', 'debug');
	} else {
		session_set('adherents_recherche_rapide', null);
		association_log('adherents', 'Recherche rapide réinitialisée (aucun critère soumis)', 'debug');
	}

	return [
		'message_ok' => _T('association_adhesions:recherche_effectuee'),
		'redirect' => generer_url_ecrire('adherents'),
	];
}
