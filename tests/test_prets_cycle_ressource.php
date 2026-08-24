<?php

define('_ECRIRE_INC_VERSION', true);

$prets_actifs_test = 0;
$statut_ressource_test = null;

function sql_countsel($table, $where) {
	global $prets_actifs_test;
	return $table === 'spip_asso_prets' ? $prets_actifs_test : 0;
}

function sql_updateq($table, $set, $where) {
	global $statut_ressource_test;
	if ($table === 'spip_asso_ressources') {
		$statut_ressource_test = $set['statut'];
	}
	return true;
}

require dirname(__DIR__) . '/plugins/association-prets/inc/prets.php';

function prets_cycle_assert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

$prets_actifs_test = 1;
prets_cycle_assert(association_prets_synchroniser_statut_ressource(7), 'La synchronisation active a échoué.');
prets_cycle_assert($statut_ressource_test === 'reserve', 'Une ressource prêtée doit être réservée.');

$prets_actifs_test = 0;
prets_cycle_assert(association_prets_synchroniser_statut_ressource(7), 'La synchronisation restituée a échoué.');
prets_cycle_assert($statut_ressource_test === 'ok', 'Une ressource restituée doit redevenir disponible.');
prets_cycle_assert(association_prets_synchroniser_statut_ressource(0) === false, 'Un identifiant nul doit être refusé.');

$racine = dirname(__DIR__) . '/plugins/association-prets';
$schema = file_get_contents($racine . '/base/association_prets.php');
$administration = file_get_contents($racine . '/association_prets_administrations.php');
$ressource = file_get_contents($racine . '/formulaires/editer_asso_ressources.php');
$pret = file_get_contents($racine . '/formulaires/editer_asso_pret.php');
$suppression = file_get_contents($racine . '/action/supprimer_prets.php');
$bouton = file_get_contents($racine . '/prive/squelettes/inclure/prets_ressource.html');

prets_cycle_assert(
	substr_count($schema, "'id_ressource' => 'BIGINT NOT NULL'") >= 2
		&& str_contains($schema, "'id_emprunteur' => 'BIGINT NOT NULL'")
		&& str_contains($schema, "'KEY id_ressource' => 'id_ressource'")
		&& str_contains($schema, "'KEY id_emprunteur' => 'id_emprunteur'"),
	'Les identifiants relationnels des prêts ne sont pas typés et indexés.'
);
prets_cycle_assert(
	str_contains($administration, "'1.1.1' => array(array('association_prets_migrer_identifiants_relationnels'))")
		&& str_contains($administration, 'MODIFY id_ressource BIGINT NOT NULL')
		&& str_contains($administration, 'MODIFY id_emprunteur BIGINT NOT NULL')
		&& str_contains($administration, 'Identifiants de prêt invalides'),
	'La migration ne contrôle ou ne convertit pas explicitement les identifiants historiques.'
);
prets_cycle_assert(
	!str_contains($ressource, '//TODO: bug id ressource')
		&& str_contains($ressource, 'if (!$id_ressource)')
		&& str_contains($ressource, "\$contexte['statut'] = 'ok';")
		&& str_contains($ressource, 'traiter_dist'),
	'Le formulaire Ressource ne préserve pas explicitement son statut en édition.'
);
prets_cycle_assert(
	str_contains($pret, 'association_prets_synchroniser_statut_ressource($id_ressource)')
		&& str_contains($pret, "sql_countsel('spip_asso_ressources'"),
	'Le formulaire Prêt ne valide ou ne synchronise pas la ressource.'
);
prets_cycle_assert(
	str_contains($suppression, "sql_getfetsel('id_ressource', 'spip_asso_prets'")
		&& !preg_match('/list\s*\([^)]*\$id_ressource/', $suppression)
		&& str_contains($bouton, '#URL_ACTION_AUTEUR{supprimer_prets,#ID_PRET,#SELF}'),
	'La suppression fait encore confiance à un identifiant de ressource transmis par le client.'
);

echo "OK: le cycle prêt/ressource conserve ses identifiants et statuts.\n";
