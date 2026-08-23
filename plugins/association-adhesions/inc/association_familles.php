<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Indiquer si l'intégration entre Adhésions et Familles peut fonctionner.
 */
function association_familles_integration_disponible() {
	include_spip('inc/plugin');
	if (!test_plugin_actif('familles')) {
		return false;
	}

	$description = sql_showtable('spip_auteurs', true);
	return isset($description['field']['auteur_compte_principal']);
}

/**
 * Construire les groupes compte principal / comptes secondaires à migrer.
 *
 * La lecture SQL est nécessaire car `auteur_compte_principal` est un champ
 * métier du plugin Association et non un lien d'objet SPIP.
 */
function association_familles_lister_groupes() {
	$auteurs = sql_allfetsel(
		'id_auteur,nom,email,auteur_compte_principal',
		'spip_auteurs',
		"webmestre!='oui'",
		'',
		'id_auteur'
	) ?: array();

	$par_id = array();
	foreach ($auteurs as $auteur) {
		$par_id[intval($auteur['id_auteur'])] = $auteur;
	}

	$groupes = array();
	foreach ($par_id as $id_auteur => $auteur) {
		$id_principal = intval($auteur['auteur_compte_principal'] ?? 0);
		if ($id_principal <= 0 || $id_principal === $id_auteur) {
			$id_principal = $id_auteur;
		}

		if (!isset($groupes[$id_principal])) {
			$groupes[$id_principal] = array(
				'id_principal' => $id_principal,
				'principal' => $par_id[$id_principal] ?? array(),
				'secondaires' => array(),
				'alertes' => array(),
			);
		}

		if ($id_auteur !== $id_principal) {
			$groupes[$id_principal]['secondaires'][] = $auteur;
			if (!isset($par_id[$id_principal])) {
				$groupes[$id_principal]['alertes'][] = _T('association_adhesions:migration_familles_principal_absent');
			}
		}
	}

	ksort($groupes);
	return array_values($groupes);
}

/**
 * Prévisualiser la migration sans modifier les données.
 */
function association_familles_previsualiser_migration() {
	$rapport = array(
		'disponible' => false,
		'groupes' => array(),
		'totaux' => array('familles' => 0, 'principaux' => 0, 'secondaires' => 0, 'alertes' => 0),
	);
	if (!association_familles_integration_disponible()) {
		return $rapport;
	}
	include_spip('inc/familles');
	$rapport['disponible'] = function_exists('familles_objet_lister_familles');
	if (!$rapport['disponible']) {
		return $rapport;
	}

	foreach (association_familles_lister_groupes() as $groupe) {
		$id_principal = intval($groupe['id_principal']);
		$familles = familles_objet_lister_familles('auteur', $id_principal, array('inclure_inactifs' => true));
		if (count($familles) > 1) {
			$groupe['alertes'][] = _T('association_adhesions:migration_familles_plusieurs_familles');
		}
		$groupe['familles'] = $familles;
		$rapport['groupes'][] = $groupe;
		$rapport['totaux']['familles'] += $familles ? 0 : 1;
		$rapport['totaux']['principaux']++;
		$rapport['totaux']['secondaires'] += count($groupe['secondaires']);
		$rapport['totaux']['alertes'] += count($groupe['alertes']);
	}

	return $rapport;
}

/**
 * Exécuter une migration idempotente vers les liens du plugin Familles.
 */
function association_familles_executer_migration() {
	$rapport = association_familles_previsualiser_migration();
	$resultat = array('familles_creees' => 0, 'liens_crees' => 0, 'erreurs' => array());
	if (empty($rapport['disponible'])) {
		$resultat['erreurs'][] = 'plugin/familles';
		return $resultat;
	}
	$options_principal = array_fill_keys(
		array('peut_voir', 'peut_modifier', 'peut_gerer_liens', 'peut_payer', 'recoit_emails', 'recoit_factures', 'contact_principal'),
		'oui'
	);
	$options_secondaire = array_fill_keys(array('peut_voir', 'recoit_emails'), 'oui');

	foreach ($rapport['groupes'] as $groupe) {
		$id_principal = intval($groupe['id_principal']);
		if (empty($groupe['principal']) || count($groupe['familles']) > 1) {
			$resultat['erreurs'][] = 'auteur/' . $id_principal;
			continue;
		}

		$id_famille = intval($groupe['familles'][0] ?? 0);
		if (!$id_famille) {
			$nom = trim((string) ($groupe['principal']['nom'] ?? ''));
			$id_famille = familles_creer(array(
				'titre' => _T('association_adhesions:migration_familles_titre_genere', array('nom' => $nom, 'id' => $id_principal)),
				'statut' => 'publie',
			));
			$resultat['familles_creees'] += $id_famille ? 1 : 0;
		}
		if (!$id_famille) {
			$resultat['erreurs'][] = 'auteur/' . $id_principal;
			continue;
		}

		$liens = array(array($id_principal, 'administrateur_famille', $options_principal));
		foreach ($groupe['secondaires'] as $secondaire) {
			$liens[] = array(intval($secondaire['id_auteur']), 'contact_secondaire', $options_secondaire);
		}

		foreach ($liens as $lien) {
			list($id_auteur, $role, $options) = $lien;
			$existait = familles_objet_est_lie($id_famille, 'auteur', $id_auteur, $role);
			if (familles_associer_objet($id_famille, 'auteur', $id_auteur, $role, $options) && !$existait) {
				$resultat['liens_crees']++;
			}
		}
	}

	spip_log($resultat, 'association_familles' . ($resultat['erreurs'] ? _LOG_ERREUR : _LOG_INFO_IMPORTANTE));
	return $resultat;
}
