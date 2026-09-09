<?php

/*\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
// TODO -> Peut être passer 'modifier_activites' et 'ajouter_activites' en un seul et meme fichier
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('inc/association_evenements_paiements');
/**
 * @deprecated Utiliser charger_fonction() pour cette action.
 */
function action_modifier_activites() {
	return action_modifier_activites_dist();
}

function action_modifier_activites_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_activite = (int) $securiser_action();
	include_spip('inc/autoriser');
	$id_evenement_reel = (int) sql_getfetsel('id_evenement', 'spip_asso_activites', 'id_activite=' . $id_activite);
	if (!$id_evenement_reel || $id_evenement_reel !== (int) _request('id_evenement')
		|| !autoriser('gererinscriptions', 'evenement', $id_evenement_reel)) {
		return false;
	}
	$inserer_transaction = function ($montant, $options) {
		return association_evenements_transaction_creer($montant, $options);
	};
	$categorie_result = _request('categorie');
	$id_evenement = $id_evenement_reel;
	$id_auteur = (int) _request('id_auteur');
	$statut_activite = _request('statut_inscription');
	// $valider                  = _request('valider');
	$valider = ($statut_activite == 'ok') ? '1' : '0';
	$nombre_inscrits = 0;
	$nom_participants = _request('nom_participants');
	$commentaire = _request('commentaire');
	$gratuit_single = _request('gratuit_single');
	$total_inscrits = _request('total_inscrits');
	$notify_the_members = _request('notify_the_members');
	$montant_total = 0;
	$gestions_places = gestions_places($id_evenement);
	$query_activites = sql_fetsel('*', 'spip_asso_activites', 'id_activite=' . (int) $id_activite);
	if (!isset($gratuit_single) && $query_activites) {
		$transaction_existante = association_evenements_transaction_lire((int) ($query_activites['id_transaction'] ?? 0));
		$query_activites['statut_activite'] = $query_activites['statut'] ?? '';
		$query_activites['statut_transaction'] = $transaction_existante['statut'] ?? '';
		$query_activites = array_merge($transaction_existante, $query_activites);
	}
	$query_evenement = sql_fetsel('*', 'spip_evenements', "id_evenement=$id_evenement");
	$evenement_payant = $query_evenement['payant'];
	$accompagnant_desactives = ($query_evenement['accompagnants'] == 'non') ? true : false;
	$validation = ($query_evenement['validation'] == 'oui') ? true : false;
	$querie_categorie_activite = sql_select('*', 'spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a ON(a.id_categorie=b.id_categorie)', "a.id_evenement=$id_evenement AND b.deleted=0", '', 'montant DESC');
	# # 1/ VERIFICATION D'UN CAS DÉJÀ PAYÉ ET VALIDÉ OU GRATUIT ET VALIDÉ (PAS BESOIN D'ALLER PLUS LOIN)
	if (!isset($gratuit_single) and $query_activites['statut_transaction'] == 'ok') {
		sql_updateq(
			'spip_asso_activites',
			[
				'nom_participants' => $nom_participants,
				'commentaire' => $commentaire,
			],
			"id_activite=$id_activite"
		);
		return;
	}
	if (!isset($gratuit_single)) {
		# # 2/ RECUPERATION ET CALCULE
		while ($categories_activite = sql_fetch($querie_categorie_activite)) {
			$result = $categorie_result[$categories_activite['id_categorie']];
			if ($result) {
				$nombre_inscrits += $result;
				$montant_prepa = (!$evenement_payant) ? 0 : $categories_activite['montant'];
				$montant_total = ($categories_activite['quantite'] > 1) ? $montant_total + $montant_prepa : $montant_total + ($montant_prepa * $result);
				// Création de la ligne pour la colonne transaction
				if (!empty($result)) {
					$transaction[$categories_activite['id_categorie']] = [
						'nombre' => $result,
						'montant' => $montant_prepa,
					];
				}
			}
		}
		sql_free($querie_categorie_activite);
	} else {
		$nombre_inscrits = !empty($total_inscrits) ? $total_inscrits : 1;
	}
	# # 3/ VERIFICATION DES ERREURS
	$redirect = false;
	$places_disponibles = ($gestions_places['places_disponibles'] == 0) ? 500 : $gestions_places['places_disponibles'];
	$places_disponibles += $gestions_places['places_disponibles'];
	if ($nombre_inscrits > $gestions_places['places_limites']) {
		$args = 'id=' . $id_activite . '&erreur=depassement';
		$redirect = urldecode(generer_url_ecrire('editer_asso_activite', $args));
	} elseif ($nombre_inscrits == 0) {
		$args = 'id=' . $id_activite . '&erreur=selection';
		$redirect = generer_url_ecrire('editer_asso_activite', $args);
	} elseif ($accompagnant_desactives and $nombre_inscrits > 1) {
		$args = 'id=' . $id_activite . '&erreur=accompagnant';
		$redirect = urldecode(generer_url_ecrire('editer_asso_activite', $args));
	}
	if ($redirect) {
		return redirige_formulaire($redirect, '', false);
	}
	# # 4/ ENREGISTREMENT DES DONNEES
	if ($gratuit_single) {
		# # CAS GRATUIT SANS ACCOMPAGNANTS
		$cal_result = activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider, $montant_payer = true, $id_activite);
	} elseif ($montant_total == 0) {
		# # AUTRES CAS GRATUIT
		if (!$validation or $valider) {
			$cal_result = activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider, $montant_payer = true, $id_activite);
		} else {
			$cal_result = activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider, $montant_payer = false, $id_activite);
		}
		/* A/
		 * transaction enrengistrée n'est pas définie sur gratuite ET
		 * (pas de validation OU
		 * validation + validé)
		 * RESULTAT : Modification de la transaction en gratuit et validation
		 */
		if (($query_activites['mode'] ?? '') !== 'gratuit' and (!$cal_result['gestion']['validation'] or ($cal_result['gestion']['validation'] and $valider))) {
			association_evenements_transaction_modifier((int) $query_activites['id_transaction'], [
				'mode' => 'gratuit',
				'reglee' => 'oui',
				'statut' => 'ok',
				'finie' => 1,
				'montant_regle' => '0',
				'montant_ht' => '0',
				'montant' => '0',
				'date_paiement' => date('Y-m-d H:i:s'),
			]);
		}
		/* B/
	 * transaction enrengistrée n'est pas définie sur gratuite ET
	 * validation + pas validé
	 * Modification de la transaction en gratuit sans validation
	 */
		elseif (($query_activites['mode'] ?? '') !== 'gratuit' and $cal_result['gestion']['validation'] and !$valider) {
			association_evenements_transaction_modifier((int) $query_activites['id_transaction'], [
				'mode' => 'gratuit',
				'montant_ht' => '0',
				'montant' => '0',
			]);
		}
		/* C/
	 * transaction enrengistrée définie sur gratuite ET
	 * (pas de validation OU
	 * validation + validé)
	 * Validation de la transaction
	 */
		elseif (($query_activites['mode'] ?? '') == 'gratuit' and
			(!$cal_result['gestion']['validation'] or ($cal_result['gestion']['validation'] and $valider))) {
			association_evenements_transaction_modifier((int) $query_activites['id_transaction'], [
				'reglee' => 'oui',
				'statut' => 'ok',
				'finie' => 1,
				'date_paiement' => date('Y-m-d H:i:s'),
			]);
		}
	} else {
		$cal_result = activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider, $montant_payer = false, $id_activite);
		/* A/
	 * transaction enregistrée définie sur gratuite ou non
	 * RESULTAT : Modification de la transaction en payant
	 */
		association_evenements_transaction_modifier((int) $query_activites['id_transaction'], [
			'mode' => '',
			'montant_ht' => $montant_total,
			'montant' => $montant_total,
		]);
	}

	/* PREPARATION Message Journal */
	if ($cal_result['statut'] == 'preinscrit') {
		$message_journal = _T('association_evenements:journal_preinscription_site_prive');
	} elseif ($cal_result['statut'] == 'ok') {
		$message_journal = _T('association_evenements:journal_inscription_site_prive');
	} elseif ($cal_result['statut'] == 'desinscrit') {
		$message_journal = _T('association_evenements:journal_desinscription_site_prive');
	} elseif ($cal_result['statut'] == 'liste_attente') {
		$message_journal = _T('association_evenements:journal_liste_attente_site_prive');
	}
	$entree_journal = date('d/m/Y H:i:s') . ' : ' . $message_journal . '<br>' . $query_activites['journal'];
	// Mise à jour de l'activité
	sql_updateq(
		'spip_asso_activites',
		[
			'statut' => $cal_result['statut'],
			// "valider"           => $valider,
			'nom_participants' => $nom_participants,
			'nombre_inscrits' => $nombre_inscrits,
			'commentaire' => $commentaire,
			'journal' => $message_journal,
			'tarifs_selectionnes' => serialize($transaction),
		],
		"id_activite=$id_activite"
	);
	// Envoi d'un email de confirmation a l'adhérent inscrit
	$type = 'modification_backend';
	if ($cal_result['statut'] == 'ok') {
		$type = 'inscription_backend';
	} elseif ($cal_result['statut'] == 'liste_attente') {
		$type = 'attente_backend';
	}
	$id_activite = [$id_activite];
	if ($notify_the_members == 1) {
		// facteur_envoyer_mail_activites($id_evenement, $id_auteur, $type, $id_activite);
		job_queue_add('facteur_envoyer_mail_activites', 'Notification - mettre_attente_activites', $arguments = [$id_evenement, $type, $id_activite], $file = '', $no_duplicate = false, $time = 0, $priority = 0);
	}
}
