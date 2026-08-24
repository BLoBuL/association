<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour tester la cotisation comptable.\n");
	exit(2);
}

$id_auteur = (int) sql_getfetsel('id_auteur', 'spip_auteurs', "statut!='5poubelle'", '', 'id_auteur');
$id_categorie = (int) sql_getfetsel('id_categorie', 'spip_asso_categories_adherents', '', '', 'id_categorie');
if ($id_auteur <= 0 || $id_categorie <= 0) {
	fwrite(STDERR, "Auteur ou catégorie indisponible pour la recette Cotisations.\n");
	exit(1);
}

sql_query('START TRANSACTION');
try {
	include_spip('inc/association_adhesions_comptabilite');
	$id_compte = association_adhesions_compte_cotisation_creer(
		date('Y-m-d H:i:s'),
		7.5,
		'Cotisation transactionnelle de recette',
		$GLOBALS['association_metas']['pc_cotisations_creance'] ?? ($GLOBALS['association_metas']['pc_cotisations'] ?? ''),
		'RECETTE',
		$id_auteur,
		'inscription',
		$id_categorie,
		'ok',
		0
	);
	$cotisation = sql_fetsel('*', 'spip_asso_cotisations', 'id_compte=' . (int) $id_compte);
	$compte = sql_fetsel('*', 'spip_asso_comptes', 'id_compte=' . (int) $id_compte);
	if (!$cotisation || !$compte
		|| (int) $cotisation['id_auteur'] !== $id_auteur
		|| (int) $compte['id_objet'] !== (int) $cotisation['id_cotisation']
		|| $compte['objet'] !== 'cotisation'
	) {
		throw new RuntimeException("L'écriture et la cotisation métier ne sont pas rattachées.");
	}

	sql_query('ROLLBACK');
	echo "OK: création Cotisation transactionnelle et rattachement comptable canonique.\n";
} catch (Throwable $e) {
	sql_query('ROLLBACK');
	fwrite(STDERR, 'ECHEC: ' . $e->getMessage() . "\n");
	exit(1);
}
