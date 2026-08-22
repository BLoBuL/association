<?php
/**
 * Anonymisation RGPD des donnees metier association.
 *
 * @package SPIP\Association\RGPD
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Anonymise les donnees association rattachees a un auteur.
 *
 * @param int $id_auteur
 * @param array $auteur
 * @return array
 */
function association_rgpd_anonymiser_auteur($id_auteur, $auteur = array()) {
	include_spip('base/abstract_sql');

	$id_auteur = intval($id_auteur);
	if ($id_auteur <= 0) {
		return array('ok' => false, 'erreur' => _T('association:erreur_auteur_inexistant'));
	}

	$email = trim((string)($auteur['email'] ?? ''));
	$anon = 'anonyme_' . $id_auteur;
	$resume = array();

	$where_activites = array('id_auteur=' . $id_auteur);
	if ($email !== '') {
		$where_activites[] = 'email_inscrit=' . sql_quote($email);
	}

	$maj_activites = association_rgpd_filtrer_champs('spip_asso_activites', array(
		'nom_inscrit' => 'Anonyme',
		'prenom_inscrit' => '',
		'email_inscrit' => '',
		'tel_inscrit' => '',
		'ip_inscrit' => '',
		'nom_participants' => '',
		'commentaire' => '',
	));
	$resume['activites_anonymisees'] = association_rgpd_updateq(
		'spip_asso_activites',
		$maj_activites,
		'(' . implode(' OR ', $where_activites) . ')'
	);

	$resume['dons_anonymises'] = association_rgpd_updateq(
		'spip_asso_dons',
		association_rgpd_filtrer_champs('spip_asso_dons', array(
			'bienfaiteur' => 'Anonyme',
			'colis' => '',
			'contrepartie' => '',
			'commentaire' => '',
		)),
		'id_adherent=' . $id_auteur
	);

	$resume['ventes_anonymisees'] = association_rgpd_updateq(
		'spip_asso_ventes',
		association_rgpd_filtrer_champs('spip_asso_ventes', array(
			'acheteur' => 'Anonyme',
			'commentaire' => '',
		)),
		'id_acheteur=' . $id_auteur
	);

	$resume['prets_anonymises'] = association_rgpd_updateq(
		'spip_asso_prets',
		association_rgpd_filtrer_champs('spip_asso_prets', array(
			'commentaire_sortie' => '',
			'commentaire_retour' => '',
		)),
		'id_emprunteur=' . sql_quote((string)$id_auteur)
	);

	$resume['comptes_anonymises'] = association_rgpd_updateq(
		'spip_asso_comptes',
		association_rgpd_filtrer_champs('spip_asso_comptes', array(
			'justification' => 'Operation associee a un compte anonymise ' . $id_auteur,
		)),
		'id_auteur=' . $id_auteur
	);

	$resume['transactions_anonymisees'] = association_rgpd_anonymiser_transactions($id_auteur, $email, $anon);

	return array(
		'ok' => true,
		'resume' => $resume,
	);
}

/**
 * Anonymise les transactions Bank liees a l'auteur.
 *
 * @param int $id_auteur
 * @param string $email
 * @param string $anon
 * @return int
 */
function association_rgpd_anonymiser_transactions($id_auteur, $email, $anon) {
	$where = array('id_auteur=' . intval($id_auteur));
	if ($email !== '') {
		$where[] = 'auteur=' . sql_quote($email);
	}

	return association_rgpd_updateq(
		'spip_transactions',
		association_rgpd_filtrer_champs('spip_transactions', array(
			'auteur_id' => (string)intval($id_auteur),
			'auteur' => $anon,
			'refcb' => '',
			'validite' => '',
			'abo_uid' => '',
			'pay_id' => '',
			'cadeau_email' => '',
			'cadeau_message' => '',
			'url_retour' => '',
			'token' => '',
			'message' => '',
			'erreur' => '',
		)),
		'(' . implode(' OR ', $where) . ')'
	);
}

/**
 * Filtre des champs SQL selon le schema reel de la table.
 *
 * @param string $table
 * @param array $champs
 * @return array
 */
function association_rgpd_filtrer_champs($table, $champs) {
	$desc = sql_showtable($table, true);
	if (!isset($desc['field']) || !is_array($desc['field'])) {
		return array();
	}

	return array_intersect_key($champs, $desc['field']);
}

/**
 * Met a jour une table si au moins un champ est disponible.
 *
 * @param string $table
 * @param array $champs
 * @param string $where
 * @return int
 */
function association_rgpd_updateq($table, $champs, $where) {
	if (!$champs) {
		return 0;
	}

	return intval(sql_updateq($table, $champs, $where));
}
