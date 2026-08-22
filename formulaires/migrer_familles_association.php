<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_migrer_familles_association_charger_dist() {
	include_spip('inc/association_familles');
	return array('rapport' => association_familles_previsualiser_migration());
}

function formulaires_migrer_familles_association_verifier_dist() {
	if (!autoriser('migrerfamilles', '_association')) {
		return array('message_erreur' => _T('info_acces_interdit'));
	}
	return array();
}

function formulaires_migrer_familles_association_traiter_dist() {
	if (!autoriser('migrerfamilles', '_association')) {
		return array('message_erreur' => _T('info_acces_interdit'));
	}
	include_spip('inc/association_familles');
	$resultat = association_familles_executer_migration();
	if ($resultat['erreurs']) {
		return array('message_erreur' => _T('association:migration_familles_erreurs', array('nb' => count($resultat['erreurs']))));
	}
	return array('message_ok' => _T('association:migration_familles_resultat', $resultat), 'editable' => true);
}
