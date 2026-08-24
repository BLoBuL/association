<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_adhesions_migration_compta_manuelle(array $imputations, $compte_creance, $compte_paiement) {
	if (!$imputations) {
		return 0;
	}
	$res = sql_select(
		'id_compte,imputation,statut_cotisation',
		'spip_asso_comptes',
		"(objet='cotisation' OR id_categorie>0) AND " . sql_in('imputation', $imputations)
	);
	$nb = 0;
	while ($compte = sql_fetch($res)) {
		$cible = ($compte['statut_cotisation'] ?? '') === 'ok' ? $compte_paiement : $compte_creance;
		if ($cible && $cible !== ($compte['imputation'] ?? '')) {
			sql_updateq('spip_asso_comptes', array('imputation' => $cible), 'id_compte=' . (int) $compte['id_compte']);
			$nb++;
		}
	}
	return $nb;
}

function association_adhesions_migration_compta_automatique() {
	$creance = $GLOBALS['association_metas']['pc_cotisations_creance'] ?? '101';
	$paiement = $GLOBALS['association_metas']['pc_cotisations_paiement'] ?? '102';
	sql_updateq('spip_asso_comptes', array('depense' => 0), "(objet='cotisation' OR id_categorie>0) AND depense>0");
	$res = sql_select('id_compte,imputation,statut_cotisation,id_auteur', 'spip_asso_comptes', "objet='cotisation' OR id_categorie>0");
	$nb = 0;
	while ($compte = sql_fetch($res)) {
		$id_compte = (int) $compte['id_compte'];
		$cible = ($compte['statut_cotisation'] ?? '') === 'ok' ? $paiement : $creance;
		$set = array('imputation' => $cible);
		$id_auteur = (int) ($compte['id_auteur'] ?? 0);
		if ($id_auteur > 0) {
			$set['justification'] = association_adhesions_migration_justification_cotisation($id_auteur);
		}
		sql_updateq('spip_asso_comptes', $set, 'id_compte=' . $id_compte);
		$nb++;
	}
	return $nb;
}

function association_adhesions_migration_justification_cotisation($id_auteur) {
	$id_auteur = (int) $id_auteur;
	$auteur = sql_fetsel('nom,nom_famille,prenom,statut,validite', 'spip_auteurs', 'id_auteur=' . $id_auteur);
	if (!$auteur) {
		return 'Cotisation de #' . $id_auteur;
	}
	$nom = trim(($auteur['nom_famille'] ?? '') . ' ' . ($auteur['prenom'] ?? ''));
	if ($nom === '') {
		$nom = trim((string) ($auteur['nom'] ?? ''));
	}
	$actif = ($auteur['statut'] ?? '') !== '5poubelle' && !empty($auteur['validite']);
	if ($actif) {
		try {
			$actif = new DateTime($auteur['validite']) >= (new DateTime())->modify('-24 months');
		} catch (Exception $e) {
			$actif = false;
		}
	}
	return $actif && $nom !== '' ? 'Cotisation de ' . $nom . ' #' . $id_auteur : 'Cotisation de #' . $id_auteur;
}
