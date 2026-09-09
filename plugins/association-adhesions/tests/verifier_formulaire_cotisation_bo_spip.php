<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être exécuté dans le contexte SPIP.\n");
	exit(2);
}

include_spip('formulaires/editer_asso_cotisation');

$id_compte = (int) sql_getfetsel('id_compte', 'spip_asso_cotisations', '', '', 'id_compte');
if (!$id_compte) {
	fwrite(STDERR, "Aucune cotisation migrée ne permet de contrôler le formulaire.\n");
	exit(1);
}

$saisies = formulaires_editer_asso_cotisation_saisies($id_compte);
$champs = [];
$parcourir = function ($liste) use (&$parcourir, &$champs) {
	foreach ((array) $liste as $saisie) {
		if (!empty($saisie['options']['nom'])) {
			$champs[] = $saisie['options']['nom'];
		}
		if (!empty($saisie['saisies'])) {
			$parcourir($saisie['saisies']);
		}
	}
};
$parcourir($saisies);

$attendus = [
	'id_auteur',
	'id_compte',
	'id_categorie',
	'date_operation',
	'montant',
	'date_fin_validite',
	'statut_cotisation',
	'reinscription',
	'justification',
	'notifier',
];
$manquants = array_values(array_diff($attendus, array_unique($champs)));
if ($manquants) {
	fwrite(STDERR, 'Champs BO manquants : ' . implode(', ', $manquants) . "\n");
	exit(1);
}

echo json_encode([
	'ok' => true,
	'champs_metier' => count($attendus),
	'cotisation_migree' => true,
], JSON_UNESCAPED_SLASHES) . "\n";
