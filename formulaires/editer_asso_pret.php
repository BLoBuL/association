<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_editer_asso_pret_charger_dist($id_pret = 0, $id_ressource = 0) {
	$id_pret = (int) $id_pret;
	$id_ressource = (int) $id_ressource;
	if (!autoriser('modifier', 'pret', $id_pret)) {
		return false;
	}

	$pret = $id_pret ? sql_fetsel('*', 'spip_asso_prets', 'id_pret=' . $id_pret) : array();
	if ($id_pret && !$pret) {
		return false;
	}
	if ($pret) {
		$id_ressource = (int) $pret['id_ressource'];
	}
	$ressource = $id_ressource ? sql_fetsel('*', 'spip_asso_ressources', 'id_ressource=' . $id_ressource) : array();
	if (!$ressource) {
		return false;
	}
	$compte = $id_pret ? sql_fetsel('journal,recette', 'spip_asso_comptes', 'id_journal=' . $id_pret) : array();

	return array(
		'id_pret' => $id_pret,
		'id_ressource' => $id_ressource,
		'ressource' => $ressource['intitule'],
		'date_sortie' => $pret ? association_datefr($pret['date_sortie']) : date('d/m/Y'),
		'duree' => $pret['duree'] ?? 0,
		'id_emprunteur' => $pret['id_emprunteur'] ?? '',
		'commentaire_sortie' => $pret['commentaire_sortie'] ?? '',
		'date_retour' => $pret && $pret['date_retour'] !== '0000-00-00' ? association_datefr($pret['date_retour']) : '',
		'commentaire_retour' => $pret['commentaire_retour'] ?? '',
		'montant' => isset($compte['recette']) ? association_nbrefr($compte['recette']) : association_nbrefr($ressource['pu']),
		'journal' => $compte['journal'] ?? '',
		'classe_banques' => $GLOBALS['association_metas']['classe_banques'] ?? '',
	);
}

function formulaires_editer_asso_pret_verifier_dist($id_pret = 0, $id_ressource = 0) {
	$erreurs = array();
	foreach (array('date_sortie', 'id_emprunteur') as $champ) {
		if (!strlen(trim((string) _request($champ)))) {
			$erreurs[$champ] = _T('info_obligatoire');
		}
	}
	foreach (array('date_sortie', 'date_retour') as $champ) {
		$date = trim((string) _request($champ));
		if ($date && ($erreur = association_verifier_date($date))) {
			$erreurs[$champ] = $erreur;
		}
	}
	$id_emprunteur = (int) _request('id_emprunteur');
	if ($id_emprunteur && !sql_countsel('spip_auteurs', 'id_auteur=' . $id_emprunteur)) {
		$erreurs['id_emprunteur'] = _T('association:pret_emprunteur_introuvable');
	}
	if ((int) _request('duree') < 0) {
		$erreurs['duree'] = _T('association:erreur_montant');
	}
	if (association_recupere_montant(_request('montant')) < 0) {
		$erreurs['montant'] = _T('association:erreur_montant');
	}
	if ($erreurs) {
		$erreurs['message_erreur'] = _T('association:erreur_titre');
	}
	return $erreurs;
}

function formulaires_editer_asso_pret_traiter_dist($id_pret = 0, $id_ressource = 0) {
	$id_pret = (int) $id_pret;
	$id_ressource = (int) $id_ressource;
	if ($id_pret) {
		$id_ressource = (int) sql_getfetsel('id_ressource', 'spip_asso_prets', 'id_pret=' . $id_pret);
	}
	$date_sql = static function ($date, $vide = '0000-00-00') {
		$date = trim((string) $date);
		if (!$date) {
			return $vide;
		}
		list($jour, $mois, $annee) = array_map('intval', explode('/', $date));
		return sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
	};
	$pret = array(
		'id_ressource' => $id_ressource,
		'date_sortie' => $date_sql(_request('date_sortie')),
		'duree' => max(0, (int) _request('duree')),
		'date_retour' => $date_sql(_request('date_retour')),
		'id_emprunteur' => (int) _request('id_emprunteur'),
		'commentaire_sortie' => trim((string) _request('commentaire_sortie')),
		'commentaire_retour' => trim((string) _request('commentaire_retour')),
	);
	$compte = array(
		'date' => $pret['date_sortie'],
		'journal' => trim((string) _request('journal')),
		'recette' => association_recupere_montant(_request('montant')),
		'imputation' => $GLOBALS['association_metas']['pc_prets'] ?? '',
	);

	sql_query('START TRANSACTION');
	if ($id_pret) {
		$ok = sql_updateq('spip_asso_prets', $pret, 'id_pret=' . $id_pret);
		$ok = ($ok !== false) && sql_updateq('spip_asso_comptes', $compte, 'id_journal=' . $id_pret) !== false;
	} else {
		$id_pret = (int) sql_insertq('spip_asso_prets', $pret);
		$compte['justification'] = _T('association:pret_nd') . $id_ressource . '/' . $id_pret;
		$compte['id_journal'] = $id_pret;
		$ok = $id_pret && sql_insertq('spip_asso_comptes', $compte);
	}
	$ok = $ok && sql_updateq('spip_asso_ressources', array('statut' => 'reserve'), 'id_ressource=' . $id_ressource) !== false;
	if (!$ok) {
		sql_query('ROLLBACK');
		return array('message_erreur' => _T('association:pret_enregistrement_erreur'));
	}
	sql_query('COMMIT');
	return array(
		'message_ok' => _T('association:pret_enregistrement_ok'),
		'redirect' => generer_url_ecrire('prets', 'id_ressource=' . $id_ressource),
	);
}
