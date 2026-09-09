<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_migrer_familles_association_charger_dist() {
	include_spip('inc/association_familles');
	return ['rapport' => association_familles_previsualiser_migration()];
}

function formulaires_migrer_familles_association_verifier_dist() {
	if (!autoriser('migrerfamilles', '_association')) {
		return ['message_erreur' => _T('info_acces_interdit')];
	}
	return [];
}

function formulaires_migrer_familles_association_traiter_dist() {
	if (!autoriser('migrerfamilles', '_association')) {
		return ['message_erreur' => _T('info_acces_interdit')];
	}
	include_spip('inc/association_familles');
	$resultat = association_familles_executer_migration();
	if ($resultat['erreurs']) {
		return ['message_erreur' => _T('association_adhesions:migration_familles_erreurs', ['nb' => count($resultat['erreurs'])])];
	}
	return ['message_ok' => _T('association_adhesions:migration_familles_resultat', $resultat), 'editable' => true];
}
