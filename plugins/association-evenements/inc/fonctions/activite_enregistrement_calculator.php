<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
/**
 * GESTION DE L'ENREGISTREMENT LORS DE LA
 * L'ENREGISTREMENT D'UN ADHÃ‰RENT Ã€ UNE ACTIVITÃ‰
 *
 * @since 3.1.0
 * @version 0.4b
 *
 * @param string $id_evenement
 * @param int $nombre_inscrits
 * @param bool $valider
 * @param bool $montant_payer
 * @param int $id_activite
 * @return array
 */
function activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider = '', $montant_payer = '', $id_activite = '') {
	# ## Liste des statuts
	# OK = inscrit
	# liste_attente = en attente
	# preinscrit = préinscrit
	$query_evenement = !$id_evenement ? false : sql_fetsel('*', 'spip_evenements', "id_evenement=$id_evenement");
	$affichage_dans_activites = affichage_dans_activites($id_evenement);
	$result['gestion'] = $affichage_dans_activites;
	$gestion_places = gestions_places($id_evenement);

	// $result['valider'] = $valider;
	if ($id_activite) {
		$query_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite = $id_activite");
		$places_disponibles = ($query_activite['statut'] == 'ok') ? $gestion_places['places_disponibles'] + $query_activite['nombre_inscrits'] : $gestion_places['places_disponibles'];
	} else {
		$places_disponibles = $gestion_places['places_disponibles'];
	}
	$result['en_attente'] = 0;
	// Calcul du statut
	// if(!$montant_payer OR (!$valider AND $affichage_dans_activites['validation'])) # Montant n'est pas payé ou non validé
	if (!$affichage_dans_activites['places']) {
		if (!$affichage_dans_activites['validation']) {
			$result['statut'] = 'ok';
		}
		if ($affichage_dans_activites['validation']) {
			$result['statut'] = 'preinscrit';
		}
	}

	/* SI LE NOMBRE DE PLACE EST LIMITE */

	if ($affichage_dans_activites['places']) {
		if (
			($places_disponibles == 0)
			or ($nombre_inscrits > $places_disponibles)
			or ($gestion_places['places_disponibles'] == 0 and $affichage_dans_activites['attentes_illimite'])
		) {
			$result['statut'] = 'liste_attente';
		} else {
			$result['statut'] = (!$affichage_dans_activites['validation']) ? 'ok' : 'preinscrit';
		}
	}
	$result['en_attente'] = ($result['statut'] == 'liste_attente') ? 1 : 0;
	$result['validation'] = ($affichage_dans_activites['validation']) ? true : false;

	return $result;
}
