<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * VERIFICATION DE PREREQUIS POUR L'INSCRIPTION A UNE ACTIVITE
 *
 * On vérifie les critère suivant :
 * - Date
 * - Quota
 *
 * @param string $id_evenement
 * @return array
 */
function ouverture_inscription_evenement($id_evenement) {
	/* Infos sur l'événement */
	$gestions_places = gestions_places($id_evenement);
	$affichage_dans_activites = affichage_dans_activites($id_evenement);
	/*
		1/ VERIFICATION SI L'EVENEMENT EST OUVERT
	*/
	$inscription_statut = $affichage_dans_activites['statut_ouverture_inscription'] ?? 'erreur_statut_ouverture';
	if ($inscription_statut === 'inscription_ouverte') {
		$inscription_ouverte = 'oui';
	} else {
		$inscription_ouverte = $inscription_statut;
	}
	/*
		2/ VERIFICATION SI IL Y A ENCORE DES PLACES
	*/
	if (!empty($gestions_places['plein'])) {
		$places_disponibles = 'non';

	} else {
		$places_disponibles = 'oui';
	}

	/* TEST FINAL */
	if ($inscription_ouverte == 'oui' && $places_disponibles == 'oui') {

		$ouverture_inscription_evenement = [
			'inscription_ouverte' => 'oui',

			'statut_ouverture_inscription' => $affichage_dans_activites['statut_ouverture_inscription'] ?? null,
			'date_ouverture_inscription' => $affichage_dans_activites['date_ouverture_inscription'] ?? null,
			'date_fermeture_inscription' => $affichage_dans_activites['date_fermeture_inscription'] ?? null,
			'places_disponibles' => $places_disponibles,
		];
	} else {
		$ouverture_inscription_evenement = [
			'inscription_ouverte' => 'non',

			'statut_ouverture_inscription' => $affichage_dans_activites['statut_ouverture_inscription'] ?? null,
			'date_ouverture_inscription' => $affichage_dans_activites['date_ouverture_inscription'] ?? null,
			'date_fermeture_inscription' => $affichage_dans_activites['date_fermeture_inscription'] ?? null,
			'places_disponibles' => $places_disponibles,
		];
	}

	return $ouverture_inscription_evenement;
}
