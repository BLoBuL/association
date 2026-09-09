<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être exécuté dans le contexte SPIP.\n");
	exit(2);
}

$normaliser = function ($lignes, $champ) {
	$ids = array_map('intval', array_column((array) $lignes, $champ));
	$ids = array_values(array_unique(array_filter($ids)));
	sort($ids, SORT_NUMERIC);
	return $ids;
};

$erreurs = [];
$types_verifies = 0;
$categories = sql_allfetsel('id_categorie,type_adherent', 'spip_asso_categories_adherents', "statut='ok'");
$par_type = [];
foreach ($categories as $categorie) {
	$type = (string) ($categorie['type_adherent'] ?? '');
	$par_type[$type][] = (int) $categorie['id_categorie'];
}

foreach ($par_type as $type => $ids_categories) {
	$where_categories = sql_in('id_categorie', $ids_categories);
	$anciens = $normaliser(sql_allfetsel(
		'id_auteur',
		'spip_asso_comptes',
		"(objet='cotisation' OR reinscription<>'' OR statut_cotisation<>'') AND " . $where_categories
	), 'id_auteur');
	$nouveaux = $normaliser(sql_allfetsel('id_auteur', 'spip_asso_cotisations', $where_categories), 'id_auteur');
	if ($anciens !== $nouveaux) {
		$erreurs[] = 'Le filtre de catégorie diffère pour un type d’adhérent.';
	}
	$types_verifies++;
}

$anciens_transactions = $normaliser(sql_allfetsel(
	'id_transaction',
	'spip_asso_comptes',
	"id_transaction>0 AND statut_cotisation IN ('demande','attente')"
), 'id_transaction');
$nouvelles_transactions = $normaliser(sql_allfetsel(
	'id_transaction',
	'spip_asso_cotisations',
	"id_transaction>0 AND statut IN ('demande','attente')"
), 'id_transaction');
if ($anciens_transactions !== $nouvelles_transactions) {
	$erreurs[] = 'La détection des transactions en cours diffère de l’historique.';
}

$ancien_hash = hash('sha256', json_encode(sql_allfetsel(
	'id_auteur,id_compte,date',
	'spip_asso_comptes',
	"objet='cotisation' OR reinscription<>'' OR statut_cotisation<>''",
	'',
	'id_compte'
)));
$nouveau_hash = hash('sha256', json_encode(sql_allfetsel(
	'id_auteur,id_compte,date_creation AS date',
	'spip_asso_cotisations',
	'',
	'',
	'id_compte'
)));
if ($ancien_hash !== $nouveau_hash) {
	$erreurs[] = 'Les dates utilisées par la recherche avancée diffèrent de l’historique.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", array_unique($erreurs)) . "\n");
	exit(1);
}

echo json_encode([
	'ok' => true,
	'types_verifies' => $types_verifies,
	'transactions_en_cours' => count($nouvelles_transactions),
	'empreinte_dates' => $nouveau_hash,
], JSON_UNESCAPED_SLASHES) . "\n";
