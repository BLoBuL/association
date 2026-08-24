<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour tester la désinscription comptable.\n");
	exit(2);
}

$activite = sql_fetsel('id_activite,id_evenement,id_transaction', 'spip_asso_activites', '', '', 'id_activite');
if (!$activite) {
	fwrite(STDERR, "Aucune inscription disponible pour la recette Événements.\n");
	exit(1);
}

$id_activite = (int) $activite['id_activite'];
$id_evenement = (int) $activite['id_evenement'];
$id_transaction_recette = -900000078;
sql_query('START TRANSACTION');
try {
	sql_updateq('spip_asso_activites', array('id_transaction' => $id_transaction_recette), 'id_activite=' . $id_activite);
	$id_compte = sql_insertq('spip_asso_comptes', array(
		'date' => date('Y-m-d H:i:s'),
		'recette' => 9.5,
		'depense' => 0,
		'justification' => 'Écriture transactionnelle de désinscription',
		'imputation' => 'RECETTE',
		'journal' => 'RECETTE',
		'id_auteur' => 0,
		'id_objet' => $id_evenement,
		'objet' => 'evenement',
		'id_transaction' => $id_transaction_recette,
	));
	include_spip('inc/association_evenements_comptabilite');
	$nb = association_evenements_comptes_supprimer_inscription($id_activite);
	if ($nb !== 1 || sql_countsel('spip_asso_comptes', 'id_compte=' . (int) $id_compte)) {
		throw new RuntimeException("L'écriture canonique de l'inscription n'a pas été supprimée.");
	}

	sql_query('ROLLBACK');
	echo "OK: suppression transactionnelle de l'écriture Événements canonique.\n";
} catch (Throwable $e) {
	sql_query('ROLLBACK');
	fwrite(STDERR, 'ECHEC: ' . $e->getMessage() . "\n");
	exit(1);
}
