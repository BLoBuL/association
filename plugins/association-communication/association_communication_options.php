<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function parser_emails_depuis_config($valeur) {
	include_spip('inc/filtres');

	if (is_array($valeur)) {
		$parts = $valeur;
	} else {
		$valeur = is_scalar($valeur) ? trim((string) $valeur) : '';
		if ($valeur === '') {
			return false;
		}
		$parts = preg_split('/[;,\s]+/', $valeur, -1, PREG_SPLIT_NO_EMPTY);
	}

	$emails = [];
	foreach ((array) $parts as $part) {
		$email = strtolower(trim((string) $part));
		if ($email === '') {
			continue;
		}
		if (email_valide($email)) {
			$emails[] = $email;
		}
	}

	$emails = array_values(array_unique($emails));
	return !empty($emails) ? $emails : false;
}

function request_statut_interne_table_destinataire_mail_collectif() {
	$statut_interne = _request('statut_interne');
	$statuts_adherents = $GLOBALS['association_liste_des_statuts'] ?? [];
	if (in_array($statut_interne, $statuts_adherents, true)) {
		return 'statut_interne=' . sql_quote($statut_interne);
	}
	if ($statut_interne == 'tous') {
		return "statut_interne LIKE '%' ";
	} else {
		$b = ['prospect', 'ok', 'echu', 'relance'];
		return sql_in('statut_interne', $b);
	}
}
