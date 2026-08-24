<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Normalise une valeur de responsables historique en liste d'identifiants.
 *
 * Les versions précédentes ont pu stocker un tableau, une sérialisation PHP
 * ou une liste séparée par des virgules.
 */
function association_evenements_normaliser_responsables($valeur): array {
	if (is_string($valeur)) {
		$valeur = trim($valeur);
		if ($valeur === '') {
			return array();
		}
		$deserialisee = @unserialize($valeur, array('allowed_classes' => false));
		if (is_array($deserialisee)) {
			$valeur = $deserialisee;
		} else {
			$valeur = preg_split('/[\s,;|]+/', $valeur, -1, PREG_SPLIT_NO_EMPTY);
		}
	}

	$ids = array_map('intval', (array) $valeur);
	$ids = array_filter($ids, static fn($id) => $id > 0);
	return array_values(array_unique($ids));
}

/**
 * Retourne les responsables sélectionnés d'un événement sous forme plate.
 *
 * Si aucun événement n'est fourni, les auteurs actifs liés à l'article sont
 * utilisés. Ce second mode sert à initialiser un nouvel événement.
 */
function association_evenements_responsables_ids($id_evenement = 0, $id_article = 0): array {
	$id_evenement = intval($id_evenement);
	$id_article = intval($id_article);
	$ids = array();

	if ($id_evenement > 0) {
		$evenement = sql_fetsel(
			'inscription, responsables',
			'spip_evenements',
			'id_evenement=' . $id_evenement
		);
		if (!$evenement || intval($evenement['inscription'] ?? 0) !== 1) {
			return array();
		}
		$ids = association_evenements_normaliser_responsables($evenement['responsables'] ?? array());
	} elseif ($id_article > 0) {
		$lignes = sql_allfetsel(
			'DISTINCT auteurs.id_auteur',
			'spip_auteurs AS auteurs INNER JOIN spip_auteurs_liens AS lien ON auteurs.id_auteur=lien.id_auteur',
			array(
				"lien.objet='article'",
				'lien.id_objet=' . $id_article,
				"auteurs.statut_interne='ok'",
			)
		);
		$ids = array_column($lignes ?: array(), 'id_auteur');
	}

	$ids = association_evenements_normaliser_responsables($ids);
	if (!$ids) {
		return array();
	}

	$lignes = sql_allfetsel(
		'id_auteur',
		'spip_auteurs',
		array(sql_in('id_auteur', $ids), "statut_interne='ok'")
	);
	$ids_actifs = array_map('intval', array_column($lignes ?: array(), 'id_auteur'));
	return array_values(array_filter($ids, static fn($id) => in_array($id, $ids_actifs, true)));
}

/**
 * Prépare les choix du champ extra Agenda et ses valeurs par défaut.
 */
function association_evenements_responsables_choix($id_evenement = 0, $id_article = 0): array {
	$id_evenement = intval($id_evenement);
	$id_article = intval($id_article);
	$defaut = $id_evenement > 0
		? association_evenements_responsables_ids($id_evenement)
		: array();
	if ($id_evenement > 0 && $id_article <= 0) {
		$id_article = intval(sql_getfetsel('id_article', 'spip_evenements', 'id_evenement=' . $id_evenement));
	}

	$auteurs = array();
	if ($id_article > 0) {
		$auteurs = sql_allfetsel(
			'DISTINCT auteurs.id_auteur, auteurs.nom_famille, auteurs.prenom, auteurs.nom',
			'spip_auteurs AS auteurs INNER JOIN spip_auteurs_liens AS lien ON auteurs.id_auteur=lien.id_auteur',
			array(
				"lien.objet='article'",
				'lien.id_objet=' . $id_article,
				"auteurs.statut_interne='ok'",
			),
			'',
			'auteurs.nom_famille, auteurs.prenom, auteurs.nom'
		);
	}

	$ids_disponibles = array_map('intval', array_column($auteurs ?: array(), 'id_auteur'));
	$ids_a_ajouter = array_values(array_diff($defaut, $ids_disponibles));
	if ($ids_a_ajouter) {
		$selectionnes = sql_allfetsel(
			'id_auteur, nom_famille, prenom, nom',
			'spip_auteurs',
			array(sql_in('id_auteur', $ids_a_ajouter), "statut_interne='ok'")
		);
		$auteurs = array_merge($auteurs ?: array(), $selectionnes ?: array());
	}

	$choix = array();
	if ($auteurs) {
		usort($auteurs, static function ($auteur_a, $auteur_b) {
			$nom_a = ($auteur_a['nom_famille'] ?? '') . ' ' . ($auteur_a['prenom'] ?? '') . ' ' . ($auteur_a['nom'] ?? '');
			$nom_b = ($auteur_b['nom_famille'] ?? '') . ' ' . ($auteur_b['prenom'] ?? '') . ' ' . ($auteur_b['nom'] ?? '');
			return strcasecmp($nom_a, $nom_b);
		});
		foreach ($auteurs as $auteur) {
			$id_auteur = intval($auteur['id_auteur']);
			$nom = trim(($auteur['nom_famille'] ?? '') . ' ' . ($auteur['prenom'] ?? ''));
			$choix[$id_auteur] = $nom !== '' ? $nom : (string) ($auteur['nom'] ?? $id_auteur);
		}
	}

	if ($id_evenement <= 0) {
		$defaut = array_keys($choix);
	}

	return array(
		'choix' => $choix,
		'ids' => array_keys($choix),
		'defaut' => $defaut,
		'disable' => !$choix,
	);
}
