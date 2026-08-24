<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function asso_recuperer_auteurs_inactifs($limite_inactifs, $lot = 1000) {
	$ids = array();
	$where = "((statut='5poubelle')"
		. " OR (statut_interne='sorti')"
		. " OR (statut='6forum' AND validite='' AND inscription < " . sql_quote($limite_inactifs) . " AND statut_interne='prospect')"
		. " OR (statut='8aconfirmer' AND inscription < " . sql_quote($limite_inactifs) . '))';
	$res = sql_select('id_auteur', 'spip_auteurs', $where, '', '', intval($lot));
	while ($row = sql_fetch($res)) {
		$ids[] = intval($row['id_auteur']);
	}
	return $ids;
}

function asso_separer_auteurs_par_encaissements(array $ids_auteurs) {
	if (!$ids_auteurs) {
		return array(array(), array());
	}
	$avec = pipeline('association_maintenance_auteurs_encaisses', array(
		'args' => array('ids_auteurs' => array_values(array_map('intval', $ids_auteurs))),
		'data' => array(),
	));
	$avec = is_array($avec) ? array_values(array_unique($avec)) : array();
	$sans = array_values(array_diff($ids_auteurs, $avec));
	return array($sans, $avec);
}

function asso_supprimer_auteurs(array $ids_auteurs, $dry_run = true) {
	if (!$ids_auteurs) {
		return array('supprimes' => 0);
	}
	$in = sql_in('id_auteur', $ids_auteurs);
	$resultat = pipeline('association_maintenance_supprimer_donnees_auteurs', array(
		'args' => array(
			'ids_auteurs' => array_values(array_map('intval', $ids_auteurs)),
			'dry_run' => (bool) $dry_run,
		),
		'data' => array(),
	));
	$resultat = is_array($resultat) ? $resultat : array();
	if (association_maintenance_resultat_en_echec($resultat)) {
		$resultat['supprimes'] = 0;
		$resultat['erreur'] = 'suppression_associee_echouee';
		$resultat['suppression_auteurs_skippee'] = true;
		return $resultat;
	}
	$resultat['supprimes'] = $dry_run ? sql_countsel('spip_auteurs', $in) : sql_delete('spip_auteurs', $in);
	if ($resultat['supprimes'] === false) {
		$resultat['supprimes'] = 0;
		$resultat['erreur'] = 'suppression_auteurs_echouee';
	}
	return $resultat;
}

function asso_anonymiser_auteurs(array $ids_auteurs, $dry_run = true) {
	if (!$ids_auteurs) {
		return array('anonymises' => 0);
	}
	include_spip('inc/rgpd_anonymisation');
	if ($dry_run) {
		return array(
			'anonymises' => count($ids_auteurs),
			'dry_run' => true,
			'ids' => array_values(array_map('intval', $ids_auteurs)),
		);
	}
	$ok = 0;
	$erreurs = array();
	$details = array();
	foreach ($ids_auteurs as $id) {
		$id = intval($id);
		if ($id <= 0) {
			continue;
		}
		$auteur = sql_fetsel('id_auteur,nom,email,login', 'spip_auteurs', 'id_auteur=' . $id);
		if (!$auteur) {
			$erreurs[$id] = 'auteur introuvable';
			continue;
		}
		$n = sql_updateq('spip_auteurs', array(
			'nom' => 'Anonyme ' . $id,
			'email' => 'anon+' . $id . '@example.invalid',
			'login' => 'anon_' . $id,
		), 'id_auteur=' . $id);
		if ($n === false) {
			$erreurs[$id] = 'echec anonymisation auteur';
			continue;
		}
		$resultat = association_rgpd_anonymiser_auteur($id, $auteur);
		if (empty($resultat['ok'])) {
			$erreurs[$id] = $resultat['erreur'] ?? 'echec anonymisation donnees association';
			continue;
		}
		$details[$id] = $resultat['resume'] ?? array();
		$ok++;
	}
	return array('anonymises' => $ok, 'dry_run' => false, 'details' => $details, 'erreurs' => $erreurs);
}
