<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_adhesions_module_actif($prefixe) {
	include_spip('inc/association_capacites');
	return association_plugin_actif($prefixe);
}

include_spip('inc/fonctions/priviliges_adherent');
include_spip('inc/fonctions/association_job_notifier_echeance');
include_spip('inc/fonctions/facteur_envoyer_recu_adhesion');

if (test_plugin_actif('gis')) {
	include_spip('inc/fonctions/facteur_envoyer_notification_gis');
}

$GLOBALS['association_cotisation_statuts'] = ['demande', 'attente', 'ok'];
$GLOBALS['association_liste_des_statuts'] = ['sorti', 'prospect', 'ok', 'echu', 'relance'];
$GLOBALS['association_styles_des_statuts'] = [
	'echu' => 'echu',
	'ok' => 'ok',
	'prospect' => 'prospect',
	'relance' => 'relance',
	'desactive' => 'desactive',
];
$GLOBALS['table_titre']['auteurs'] = "nom_famille AS titre, '' AS lang";

$GLOBALS['table_des_tables']['asso_categories_adherents'] = 'asso_categories_adherents';

/**
 * Calculer le nombre de jours calendaires entre deux dates d'adhésion.
 *
 * @param string $debut
 * @param string $fin
 * @param bool $absolu
 * @return int|null
 */
function association_adhesions_nombre_jours($debut, $fin, $absolu = true) {
	$timestamp_debut = strtotime($debut);
	$timestamp_fin = strtotime($fin);
	if ($timestamp_debut === false || $timestamp_fin === false) {
		association_log(
			'adhesions',
			"association_adhesions_nombre_jours: date invalide debut={$debut} fin={$fin}",
			'erreur'
		);
		return null;
	}

	$date_debut = new DateTimeImmutable(date('Y-m-d', $timestamp_debut));
	$date_fin = new DateTimeImmutable(date('Y-m-d', $timestamp_fin));
	$jours = (int) $date_debut->diff($date_fin)->format('%r%a');

	return $absolu ? abs($jours) : $jours;
}
