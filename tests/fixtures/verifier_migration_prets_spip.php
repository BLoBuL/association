<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour vérifier et nettoyer les données de recette.\n");
	exit(2);
}

include_spip('inc/prets');
$id_ressource = 900000001;
$id_pret = 900000001;
$trouver_table = charger_fonction('trouver_table', 'base');
$table = $trouver_table('spip_asso_prets');
$type_ressource = strtolower((string) ($table['field']['id_ressource'] ?? ''));
$type_emprunteur = strtolower((string) ($table['field']['id_emprunteur'] ?? ''));
$pret = sql_fetsel('*', 'spip_asso_prets', 'id_pret=' . $id_pret);
$ressource = sql_fetsel('*', 'spip_asso_ressources', 'id_ressource=' . $id_ressource);

if (!str_contains($type_ressource, 'bigint') || !str_contains($type_emprunteur, 'bigint')
	|| !$pret || !$ressource
	|| intval($pret['id_ressource']) !== $id_ressource
	|| intval($pret['id_emprunteur']) <= 0
	|| $pret['commentaire_sortie'] !== 'Fixture temporaire Association 4') {
	fwrite(STDERR, "La migration 1.1.0 n'a pas conservé ou typé la fixture.\n");
	exit(1);
}

sql_query('START TRANSACTION');
$ok = association_prets_synchroniser_statut_ressource($id_ressource);
$ok = $ok && sql_getfetsel('statut', 'spip_asso_ressources', 'id_ressource=' . $id_ressource) === 'reserve';
$ok = $ok && sql_updateq('spip_asso_prets', array('date_retour' => '2026-08-25'), 'id_pret=' . $id_pret) !== false;
$ok = $ok && association_prets_synchroniser_statut_ressource($id_ressource);
$ok = $ok && sql_getfetsel('statut', 'spip_asso_ressources', 'id_ressource=' . $id_ressource) === 'ok';
$ok = $ok && sql_delete('spip_asso_prets', 'id_pret=' . $id_pret) !== false;
$ok = $ok && sql_delete('spip_asso_ressources', 'id_ressource=' . $id_ressource) !== false;
sql_query($ok ? 'COMMIT' : 'ROLLBACK');

if (!$ok) {
	fwrite(STDERR, "Le cycle ou le nettoyage de la fixture a échoué.\n");
	exit(1);
}
echo "OK: migration Prêts 1.0.0 -> 1.1.0 conservée, cycle reserve/ok validé, fixture supprimée.\n";
