<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Export borné aux auteurs et aux colonnes explicitement sélectionnés.
 */
function action_exporter_adherents_pdf_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	if (!autoriser('associer', 'adherents')) {
		return false;
	}
	include_spip('inc/association_adhesions_export');
	$selection = _request('champs') ?? _request('csv');
	$colonnes = association_adhesions_export_colonnes($selection);
	$brut = _request('id_auteur_boucle');
	if (!$colonnes || !is_string($brut) || !preg_match('/^[1-9][0-9]*(,[1-9][0-9]*)*$/D', $brut)) {
		return false;
	}
	$ids = array_values(array_unique(array_map('intval', explode(',', $brut))));
	$lignes = sql_allfetsel(
		array_keys($colonnes),
		'spip_auteurs',
		[sql_in('id_auteur', $ids), "webmestre='non'", "statut!='5poubelle'"],
		'',
		'id_auteur'
	);
	// Le squelette reçoit des cellules ordonnées, pas des noms de champs SQL.
	$donnees = [];
	foreach ($lignes as $ligne) {
		$cellules = [];
		foreach ($colonnes as $champ => $label) {
			$cellules[] = (string) ($ligne[$champ] ?? '');
		}
		$donnees[] = $cellules;
	}
	$titre = _request('pdf_name') ?? _request('csv_name');
	include_spip('inc/association_pdf');
	association_pdf_envoyer('prive/pdf/association_adherents', [
		'titre' => is_string($titre) && trim($titre) !== '' ? $titre : _T('association_adhesions:export_pdf_titre'),
		'colonnes' => array_values($colonnes),
		'lignes' => $donnees,
	], 'adherents');
	return true;
}
