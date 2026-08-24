<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour créer les données de recette.\n");
	exit(2);
}

$id_ressource = 900000001;
$id_pret = 900000001;
if (sql_countsel('spip_asso_ressources', 'id_ressource=' . $id_ressource)
	|| sql_countsel('spip_asso_prets', 'id_pret=' . $id_pret)) {
	fwrite(STDERR, "Les identifiants réservés à la recette existent déjà.\n");
	exit(1);
}
$id_auteur = intval(sql_getfetsel('id_auteur', 'spip_auteurs', "statut!='5poubelle'", '', 'id_auteur'));
if ($id_auteur <= 0) {
	fwrite(STDERR, "Aucun auteur disponible pour le prêt de recette.\n");
	exit(1);
}

sql_query('START TRANSACTION');
$ok = sql_insertq('spip_asso_ressources', array(
	'id_ressource' => $id_ressource,
	'code' => 'RECETTE-MIGRATION-4',
	'intitule' => 'Ressource technique de migration',
	'date_acquisition' => '2026-08-24',
	'pu' => 5,
	'statut' => 'reserve',
	'commentaire' => 'Fixture temporaire Association 4',
));
$ok = $ok && sql_insertq('spip_asso_prets', array(
	'id_pret' => $id_pret,
	'id_ressource' => (string) $id_ressource,
	'date_sortie' => '2026-08-24',
	'duree' => 7,
	'date_retour' => '0000-00-00',
	'id_emprunteur' => (string) $id_auteur,
	'statut' => 'ok',
	'commentaire_sortie' => 'Fixture temporaire Association 4',
	'commentaire_retour' => '',
));
sql_query($ok ? 'COMMIT' : 'ROLLBACK');
if (!$ok) {
	fwrite(STDERR, "Impossible de créer la fixture de migration.\n");
	exit(1);
}

echo "OK: fixture Prêts 1.0.0 créée avec identifiants textuels numériques.\n";
