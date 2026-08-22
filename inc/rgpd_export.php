<?php
/**
 * Export RGPD des donnees metier association.
 *
 * @package SPIP\Association\RGPD
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourne les donnees association rattachees a un auteur.
 *
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_donnees_auteur($id_auteur) {
	include_spip('base/abstract_sql');

	$id_auteur = intval($id_auteur);
	if ($id_auteur <= 0) {
		return array();
	}

	$auteur = sql_fetsel('id_auteur,email', 'spip_auteurs', 'id_auteur=' . $id_auteur);
	if (!$auteur) {
		return array();
	}

	$email = trim((string)($auteur['email'] ?? ''));

	return array(
		'date_export_association' => date('c'),
		'cotisations' => association_rgpd_export_cotisations($id_auteur),
		'inscriptions_evenements' => association_rgpd_export_inscriptions_evenements($id_auteur, $email),
		'operations_comptables' => association_rgpd_export_operations_comptables($id_auteur),
		'dons' => association_rgpd_export_dons($id_auteur),
		'ventes' => association_rgpd_export_ventes($id_auteur),
		'prets' => association_rgpd_export_prets($id_auteur),
	);
}

/**
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_cotisations($id_auteur) {
	$where = array('id_auteur=' . intval($id_auteur));
	$conditions_cotisation = array();
	if (association_rgpd_table_has_column('spip_asso_comptes', 'objet')) {
		$conditions_cotisation[] = 'objet=' . sql_quote('cotisation');
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'id_categorie')) {
		$conditions_cotisation[] = 'id_categorie>0';
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'reinscription')) {
		$conditions_cotisation[] = 'reinscription<>' . sql_quote('');
	}
	if ($conditions_cotisation) {
		$where[] = '(' . implode(' OR ', $conditions_cotisation) . ')';
	}

	$rows = sql_allfetsel(
		'*',
		'spip_asso_comptes',
		implode(' AND ', $where),
		'',
		'date DESC, id_compte DESC'
	);

	$export = array();
	foreach ($rows as $row) {
		$ligne = association_rgpd_export_compte($row);
		$ligne['categorie'] = association_rgpd_export_categorie_cotisation(intval($row['id_categorie'] ?? 0));
		$export[] = $ligne;
	}

	return $export;
}

/**
 * @param int $id_auteur
 * @param string $email
 * @return array
 */
function association_rgpd_export_inscriptions_evenements($id_auteur, $email = '') {
	$where = array('id_auteur=' . intval($id_auteur));
	if ($email !== '') {
		$where[] = 'email_inscrit=' . sql_quote($email);
	}

	$rows = sql_allfetsel(
		'*',
		'spip_asso_activites',
		'(' . implode(' OR ', $where) . ')',
		'',
		'date DESC, id_activite DESC'
	);

	$export = array();
	foreach ($rows as $row) {
		$id_evenement = intval($row['id_evenement'] ?? 0);
		$id_transaction = intval($row['id_transaction'] ?? 0);

		$export[] = array(
			'id_activite' => intval($row['id_activite'] ?? 0),
			'id_evenement' => $id_evenement,
			'evenement' => association_rgpd_export_evenement($id_evenement),
			'id_auteur' => intval($row['id_auteur'] ?? 0),
			'date' => association_rgpd_export_date($row['date'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
			'statut' => (string)($row['statut'] ?? ''),
			'en_attente' => intval($row['en_attente'] ?? 0),
			'valider' => intval($row['valider'] ?? 0),
			'nombre_inscrits' => intval($row['nombre_inscrits'] ?? 0),
			'nb_invite' => intval($row['nb_invite'] ?? 0),
			'prenom_inscrit' => (string)($row['prenom_inscrit'] ?? ''),
			'nom_inscrit' => (string)($row['nom_inscrit'] ?? ''),
			'email_inscrit' => (string)($row['email_inscrit'] ?? ''),
			'tel_inscrit' => (string)($row['tel_inscrit'] ?? ''),
			'ip_inscrit' => (string)($row['ip_inscrit'] ?? ''),
			'nom_participants' => association_rgpd_decoder_structure($row['nom_participants'] ?? ''),
			'commentaire' => (string)($row['commentaire'] ?? ''),
			'log' => (string)($row['log'] ?? ''),
			'id_transaction' => $id_transaction,
			'transaction' => association_rgpd_export_transaction($id_transaction),
			'transaction_enregistree' => association_rgpd_decoder_structure($row['transaction'] ?? ''),
		);
	}

	return $export;
}

/**
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_operations_comptables($id_auteur) {
	$where = array('id_auteur=' . intval($id_auteur));
	$conditions_cotisation = array();
	if (association_rgpd_table_has_column('spip_asso_comptes', 'objet')) {
		$conditions_cotisation[] = 'objet=' . sql_quote('cotisation');
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'id_categorie')) {
		$conditions_cotisation[] = 'id_categorie>0';
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'reinscription')) {
		$conditions_cotisation[] = 'reinscription<>' . sql_quote('');
	}
	if ($conditions_cotisation) {
		$where[] = 'NOT (' . implode(' OR ', $conditions_cotisation) . ')';
	}

	$rows = sql_allfetsel(
		'*',
		'spip_asso_comptes',
		implode(' AND ', $where),
		'',
		'date DESC, id_compte DESC'
	);

	$export = array();
	foreach ($rows as $row) {
		$export[] = association_rgpd_export_compte($row);
	}

	return $export;
}

/**
 * @param array $row
 * @return array
 */
function association_rgpd_export_compte($row) {
	$id_transaction = intval($row['id_transaction'] ?? 0);

	return array(
		'id_compte' => intval($row['id_compte'] ?? 0),
		'id_auteur' => intval($row['id_auteur'] ?? 0),
		'date' => association_rgpd_export_date($row['date'] ?? ''),
		'reinscription' => (string)($row['reinscription'] ?? ''),
		'statut_cotisation' => (string)($row['statut_cotisation'] ?? ''),
		'id_categorie' => intval($row['id_categorie'] ?? 0),
		'objet' => (string)($row['objet'] ?? ''),
		'id_objet' => intval($row['id_objet'] ?? 0),
		'recette' => (float)($row['recette'] ?? 0),
		'depense' => (float)($row['depense'] ?? 0),
		'justification' => (string)($row['justification'] ?? ''),
		'imputation' => (string)($row['imputation'] ?? ''),
		'journal' => (string)($row['journal'] ?? ''),
		'id_journal' => intval($row['id_journal'] ?? 0),
		'vu' => intval($row['vu'] ?? 0),
		'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		'id_transaction' => $id_transaction,
		'transaction' => association_rgpd_export_transaction($id_transaction),
	);
}

/**
 * @param int $id_categorie
 * @return array
 */
function association_rgpd_export_categorie_cotisation($id_categorie) {
	include_spip('inc/cotisations_devises');

	$id_categorie = intval($id_categorie);
	if ($id_categorie <= 0) {
		return array();
	}

	$row = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
	if (!$row) {
		return array();
	}

	return array(
		'id_categorie' => intval($row['id_categorie'] ?? 0),
		'valeur' => (string)($row['valeur'] ?? ''),
		'statut' => (string)($row['statut'] ?? ''),
		'cotisation' => (float)($row['cotisation'] ?? 0),
		'devise' => association_cotisation_resoudre_devise($row['devise'] ?? ''),
		'paiement_en_ligne' => intval($row['paiement_en_ligne'] ?? 0),
		'commentaires' => (string)($row['commentaires'] ?? ''),
		'maj' => association_rgpd_export_date($row['maj'] ?? ''),
	);
}

/**
 * @param int $id_evenement
 * @return array
 */
function association_rgpd_export_evenement($id_evenement) {
	$id_evenement = intval($id_evenement);
	if ($id_evenement <= 0) {
		return array();
	}

	$row = sql_fetsel('id_evenement,titre,date_debut,date_fin,lieu,adresse', 'spip_evenements', 'id_evenement=' . $id_evenement);
	if (!$row) {
		return array();
	}

	return array(
		'id_evenement' => intval($row['id_evenement'] ?? 0),
		'titre' => (string)($row['titre'] ?? ''),
		'date_debut' => association_rgpd_export_date($row['date_debut'] ?? ''),
		'date_fin' => association_rgpd_export_date($row['date_fin'] ?? ''),
		'lieu' => (string)($row['lieu'] ?? ''),
		'adresse' => (string)($row['adresse'] ?? ''),
	);
}

/**
 * @param int $id_transaction
 * @return array
 */
function association_rgpd_export_transaction($id_transaction) {
	$id_transaction = intval($id_transaction);
	if ($id_transaction <= 0) {
		return array();
	}

	$row = sql_fetsel(
		'*',
		'spip_transactions',
		'id_transaction=' . $id_transaction
	);
	if (!$row) {
		return array();
	}

	return array(
		'id_transaction' => intval($row['id_transaction'] ?? 0),
		'id_auteur' => intval($row['id_auteur'] ?? 0),
		'auteur_id' => (string)($row['auteur_id'] ?? ''),
		'auteur' => (string)($row['auteur'] ?? ''),
		'date_transaction' => association_rgpd_export_date($row['date_transaction'] ?? ''),
		'montant_ht' => (string)($row['montant_ht'] ?? ''),
		'montant' => (string)($row['montant'] ?? ''),
		'devise' => (string)($row['devise'] ?? ''),
		'mode' => (string)($row['mode'] ?? ''),
		'autorisation_id' => (string)($row['autorisation_id'] ?? ''),
		'refcb' => (string)($row['refcb'] ?? ''),
		'validite' => (string)($row['validite'] ?? ''),
		'abo_uid' => (string)($row['abo_uid'] ?? ''),
		'montant_regle' => (string)($row['montant_regle'] ?? ''),
		'date_paiement' => association_rgpd_export_date($row['date_paiement'] ?? ''),
		'statut' => (string)($row['statut'] ?? ''),
		'reglee' => (string)($row['reglee'] ?? ''),
		'finie' => intval($row['finie'] ?? 0),
		'message' => (string)($row['message'] ?? ''),
		'id_panier' => intval($row['id_panier'] ?? 0),
		'id_commande' => intval($row['id_commande'] ?? 0),
		'id_facture' => intval($row['id_facture'] ?? 0),
		'cadeau_email' => (string)($row['cadeau_email'] ?? ''),
		'cadeau_message' => (string)($row['cadeau_message'] ?? ''),
	);
}

/**
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_dons($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_dons', 'id_adherent=' . intval($id_auteur), '', 'date_don DESC, id_don DESC');
	$export = array();

	foreach ($rows as $row) {
		$export[] = array(
			'id_don' => intval($row['id_don'] ?? 0),
			'date_don' => association_rgpd_export_date($row['date_don'] ?? ''),
			'bienfaiteur' => (string)($row['bienfaiteur'] ?? ''),
			'id_adherent' => intval($row['id_adherent'] ?? 0),
			'argent' => (string)($row['argent'] ?? ''),
			'colis' => (string)($row['colis'] ?? ''),
			'valeur' => (string)($row['valeur'] ?? ''),
			'contrepartie' => (string)($row['contrepartie'] ?? ''),
			'commentaire' => (string)($row['commentaire'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		);
	}

	return $export;
}

/**
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_ventes($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_ventes', 'id_acheteur=' . intval($id_auteur), '', 'date_vente DESC, id_vente DESC');
	$export = array();

	foreach ($rows as $row) {
		$export[] = array(
			'id_vente' => intval($row['id_vente'] ?? 0),
			'article' => (string)($row['article'] ?? ''),
			'code' => (string)($row['code'] ?? ''),
			'acheteur' => (string)($row['acheteur'] ?? ''),
			'id_acheteur' => intval($row['id_acheteur'] ?? 0),
			'quantite' => (string)($row['quantite'] ?? ''),
			'date_vente' => association_rgpd_export_date($row['date_vente'] ?? ''),
			'date_envoi' => association_rgpd_export_date($row['date_envoi'] ?? ''),
			'prix_vente' => (string)($row['prix_vente'] ?? ''),
			'frais_envoi' => (float)($row['frais_envoi'] ?? 0),
			'commentaire' => (string)($row['commentaire'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		);
	}

	return $export;
}

/**
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_prets($id_auteur) {
	$rows = sql_allfetsel('*', 'spip_asso_prets', 'id_emprunteur=' . sql_quote((string)intval($id_auteur)), '', 'date_sortie DESC, id_pret DESC');
	$export = array();

	foreach ($rows as $row) {
		$export[] = array(
			'id_pret' => intval($row['id_pret'] ?? 0),
			'id_ressource' => (string)($row['id_ressource'] ?? ''),
			'ressource' => association_rgpd_export_ressource($row['id_ressource'] ?? ''),
			'date_sortie' => association_rgpd_export_date($row['date_sortie'] ?? ''),
			'duree' => intval($row['duree'] ?? 0),
			'date_retour' => association_rgpd_export_date($row['date_retour'] ?? ''),
			'id_emprunteur' => (string)($row['id_emprunteur'] ?? ''),
			'statut' => (string)($row['statut'] ?? ''),
			'commentaire_sortie' => (string)($row['commentaire_sortie'] ?? ''),
			'commentaire_retour' => (string)($row['commentaire_retour'] ?? ''),
			'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		);
	}

	return $export;
}

/**
 * @param string|int $id_ressource
 * @return array
 */
function association_rgpd_export_ressource($id_ressource) {
	$id_ressource = intval($id_ressource);
	if ($id_ressource <= 0) {
		return array();
	}

	$row = sql_fetsel('id_ressource,code,intitule,statut', 'spip_asso_ressources', 'id_ressource=' . $id_ressource);
	if (!$row) {
		return array();
	}

	return array(
		'id_ressource' => intval($row['id_ressource'] ?? 0),
		'code' => (string)($row['code'] ?? ''),
		'intitule' => (string)($row['intitule'] ?? ''),
		'statut' => (string)($row['statut'] ?? ''),
	);
}

/**
 * @param mixed $valeur
 * @return mixed
 */
function association_rgpd_decoder_structure($valeur) {
	if (!is_string($valeur) || trim($valeur) === '') {
		return $valeur;
	}

	$decode = json_decode($valeur, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		return $decode;
	}

	$unserialize = @unserialize($valeur, array('allowed_classes' => false));
	if ($unserialize !== false || $valeur === serialize(false)) {
		return $unserialize;
	}

	return $valeur;
}

/**
 * @param mixed $date
 * @return string
 */
function association_rgpd_export_date($date) {
	$date = trim((string)$date);
	if ($date === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
		return '';
	}

	return $date;
}

/**
 * Teste l'existence d'une colonne SQL.
 *
 * @param string $table
 * @param string $column
 * @return bool
 */
function association_rgpd_table_has_column($table, $column) {
	$desc = sql_showtable($table, true);
	return isset($desc['field']) && is_array($desc['field']) && array_key_exists($column, $desc['field']);
}
