<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * L'identité commune appartient au socle, les précisions fiscales au module Dons.
 */
function association_dons_recu_fiscal_emetteur(): array {
	include_spip('inc/config');
	$champs = [
		'nom' => 'nom',
		'adresse' => 'rue',
		'code_postal' => 'cp',
		'ville' => 'ville',
		'pays' => 'pays',
		'identifiant' => 'num_enregistrement',
		'objet' => 'recu_fiscal_objet',
		'qualite' => 'recu_fiscal_qualite',
		'signataire_nom' => 'recu_fiscal_signataire_nom',
		'signataire_fonction' => 'recu_fiscal_signataire_fonction',
	];
	$emetteur = [];
	$manquants = [];
	foreach ($champs as $cle => $meta) {
		$valeur = lire_config('/association_metas/' . $meta, '');
		$emetteur[$cle] = is_string($valeur) ? trim($valeur) : '';
		if ($emetteur[$cle] === '') {
			$manquants[] = $cle;
		}
	}
	return ['emetteur' => $emetteur, 'manquants' => $manquants, 'complet' => !$manquants];
}
