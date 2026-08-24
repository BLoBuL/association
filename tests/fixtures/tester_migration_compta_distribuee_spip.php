<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour lancer cette recette transactionnelle.\n");
	exit(2);
}

$id_auteur = (int) sql_getfetsel('id_auteur', 'spip_auteurs', '', '', 'id_auteur');
$transaction = sql_fetsel('id_transaction,statut', 'spip_transactions', '', '', 'id_transaction');
if ($id_auteur <= 0 || !$transaction) {
	fwrite(STDERR, "La fixture requiert un auteur et une transaction existants.\n");
	exit(1);
}

sql_query('START TRANSACTION');
try {
	$id_cotisation = sql_insertq('spip_asso_comptes', array(
		'date' => date('Y-m-d H:i:s'),
		'recette' => 12,
		'depense' => 3,
		'justification' => 'Fixture migration cotisation',
		'imputation' => 'LEGACY',
		'id_auteur' => $id_auteur,
		'objet' => 'cotisation',
		'statut_cotisation' => 'ok',
	));
	$id_evenement = sql_insertq('spip_asso_comptes', array(
		'date' => date('Y-m-d H:i:s'),
		'recette' => 8,
		'depense' => 0,
		'justification' => 'Fixture migration evenement',
		'imputation' => 'LEGACY',
		'id_auteur' => $id_auteur,
		'id_objet' => 1,
		'objet' => 'evenement',
		'id_transaction' => (int) $transaction['id_transaction'],
	));

	include_spip('inc/association_adhesions_migration_compta');
	include_spip('inc/association_evenements_migration_compta');
	association_adhesions_migration_compta_automatique();
	association_evenements_migration_compta_automatique();
	$cotisation = sql_fetsel('depense,imputation,justification', 'spip_asso_comptes', 'id_compte=' . (int) $id_cotisation);
	$evenement = sql_fetsel('imputation', 'spip_asso_comptes', 'id_compte=' . (int) $id_evenement);
	$imputation_cotisation = $GLOBALS['association_metas']['pc_cotisations_paiement'] ?? '102';
	$imputation_evenement = ($transaction['statut'] ?? '') === 'ok'
		? ($GLOBALS['association_metas']['pc_activites_paiement'] ?? '104')
		: ($GLOBALS['association_metas']['pc_activites_creance'] ?? '103');
	if ((float) ($cotisation['depense'] ?? -1) !== 0.0
		|| ($cotisation['imputation'] ?? '') !== $imputation_cotisation
		|| !str_contains((string) ($cotisation['justification'] ?? ''), '#' . $id_auteur)
		|| ($evenement['imputation'] ?? '') !== $imputation_evenement) {
		throw new RuntimeException('Les propriétaires métier n’ont pas transformé leurs écritures comme attendu.');
	}
	sql_query('ROLLBACK');
	echo "OK: migration transactionnelle distribuée des écritures cotisation et événement.\n";
} catch (Throwable $e) {
	sql_query('ROLLBACK');
	fwrite(STDERR, 'ECHEC: ' . $e->getMessage() . "\n");
	exit(1);
}
