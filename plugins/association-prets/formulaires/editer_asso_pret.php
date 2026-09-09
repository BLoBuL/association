<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/prets');

function formulaires_editer_asso_pret_charger_dist($id_pret = 0, $id_ressource = 0) {
	$id_pret = (int) $id_pret;
	$id_ressource = (int) $id_ressource;
	if (!autoriser('modifier', 'pret', $id_pret)) {
		return false;
	}

	$pret = $id_pret ? sql_fetsel('*', 'spip_asso_prets', 'id_pret=' . $id_pret) : [];
	if ($id_pret && !$pret) {
		return [
			'editable' => false,
			'message_erreur' => _T('association_prets:pret_introuvable'),
		];
	}
	if ($pret) {
		$id_ressource = (int) $pret['id_ressource'];
	}
	$ressource = $id_ressource ? sql_fetsel('*', 'spip_asso_ressources', 'id_ressource=' . $id_ressource) : [];
	if (!$ressource) {
		return [
			'editable' => false,
			'message_erreur' => _T('association_prets:ressource_introuvable'),
		];
	}
	$compte = $id_pret ? association_prets_compte_lire($id_pret) : [];

	return [
		'editable' => true,
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
	];
}

function formulaires_editer_asso_pret_verifier_dist($id_pret = 0, $id_ressource = 0) {
	if (!autoriser('modifier', 'pret', (int) $id_pret)) {
		return ['message_erreur' => _T('info_acces_interdit')];
	}
	$erreurs = [];
	$id_pret = intval($id_pret);
	$id_ressource = $id_pret
		? intval(sql_getfetsel('id_ressource', 'spip_asso_prets', 'id_pret=' . $id_pret))
		: intval($id_ressource);
	if ($id_ressource <= 0 || !sql_countsel('spip_asso_ressources', 'id_ressource=' . $id_ressource)) {
		$erreurs['message_erreur'] = _T('association_prets:ressource_introuvable');
		return $erreurs;
	}
	foreach (['date_sortie', 'id_emprunteur'] as $champ) {
		if (!strlen(trim((string) _request($champ)))) {
			$erreurs[$champ] = _T('info_obligatoire');
		}
	}
	foreach (['date_sortie', 'date_retour'] as $champ) {
		$date = trim((string) _request($champ));
		if ($date && ($erreur = association_verifier_date($date))) {
			$erreurs[$champ] = $erreur;
		}
	}
	$id_emprunteur = (int) _request('id_emprunteur');
	if ($id_emprunteur && !sql_countsel('spip_auteurs', 'id_auteur=' . $id_emprunteur)) {
		$erreurs['id_emprunteur'] = _T('association_prets:pret_emprunteur_introuvable');
	}
	if ((int) _request('duree') < 0) {
		$erreurs['duree'] = _T('association_prets:erreur_montant');
	}
	$montant = association_recupere_montant(_request('montant'));
	if ($montant < 0) {
		$erreurs['montant'] = _T('association_prets:erreur_montant');
	}
	if ($montant > 0 && association_plugin_actif('association_compta') && empty($GLOBALS['association_metas']['pc_prets'])) {
		$erreurs['montant'] = _T('association_prets:pret_imputation_obligatoire');
	}
	if ($erreurs) {
		$erreurs['message_erreur'] = _T('association_prets:erreur_titre');
	}
	return $erreurs;
}

function formulaires_editer_asso_pret_traiter_dist($id_pret = 0, $id_ressource = 0) {
	if (!autoriser('modifier', 'pret', (int) $id_pret)) {
		return ['message_erreur' => _T('info_acces_interdit')];
	}
	$id_pret = (int) $id_pret;
	$id_ressource = (int) $id_ressource;
	if ($id_pret) {
		$id_ressource = (int) sql_getfetsel('id_ressource', 'spip_asso_prets', 'id_pret=' . $id_pret);
	}
	if ($id_ressource <= 0 || !sql_countsel('spip_asso_ressources', 'id_ressource=' . $id_ressource)) {
		return ['message_erreur' => _T('association_prets:ressource_introuvable')];
	}
	$date_sql = static function ($date, $vide = '0000-00-00') {
		$date = trim((string) $date);
		if (!$date) {
			return $vide;
		}
		[$jour, $mois, $annee] = array_map('intval', explode('/', $date));
		return sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
	};
	$pret = [
		'id_ressource' => $id_ressource,
		'date_sortie' => $date_sql(_request('date_sortie')),
		'duree' => max(0, (int) _request('duree')),
		'date_retour' => $date_sql(_request('date_retour')),
		'id_emprunteur' => (int) _request('id_emprunteur'),
		'commentaire_sortie' => trim((string) _request('commentaire_sortie')),
		'commentaire_retour' => trim((string) _request('commentaire_retour')),
	];
	$compte = [
		'date' => $pret['date_sortie'],
		'journal' => trim((string) _request('journal')),
		'recette' => association_recupere_montant(_request('montant')),
		'imputation' => $GLOBALS['association_metas']['pc_prets'] ?? '',
		'id_auteur' => (int) $pret['id_emprunteur'],
	];
	$montant = (float) $compte['recette'];

	sql_query('START TRANSACTION');
	if ($id_pret) {
		$ok = sql_updateq('spip_asso_prets', $pret, 'id_pret=' . $id_pret);
		$compte['justification'] = _T('association_prets:pret_nd') . $id_ressource . '/' . $id_pret;
		$ok = ($ok !== false) && association_prets_compte_enregistrer($id_pret, $compte);
	} else {
		$id_pret = (int) sql_insertq('spip_asso_prets', $pret);
		$ok = (bool) $id_pret;
		if ($ok && $montant > 0) {
			$compte['justification'] = _T('association_prets:pret_nd') . $id_ressource . '/' . $id_pret;
			$ok = association_prets_compte_enregistrer($id_pret, $compte);
		}
	}
	$ok = $ok && association_prets_synchroniser_statut_ressource($id_ressource);
	if (!$ok) {
		sql_query('ROLLBACK');
		return ['message_erreur' => _T('association_prets:pret_enregistrement_erreur')];
	}
	sql_query('COMMIT');
	return [
		'message_ok' => _T('association_prets:pret_enregistrement_ok'),
		'redirect' => generer_url_ecrire('prets', 'id_ressource=' . $id_ressource),
	];
}
