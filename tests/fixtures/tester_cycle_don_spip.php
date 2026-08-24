<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour tester le cycle Dons.\n");
	exit(2);
}

$id_auteur = (int) sql_getfetsel('id_auteur', 'spip_auteurs', "statut!='5poubelle'", '', 'id_auteur');
if ($id_auteur <= 0) {
	fwrite(STDERR, "Aucun auteur interne disponible pour la recette Dons.\n");
	exit(1);
}

sql_query('START TRANSACTION');
try {
	include_spip('action/editer_asso_dons');
	set_request('date_don', date('Y-m-d'));
	set_request('journal', 'RECETTE');
	set_request('bienfaiteur', '');
	set_request('id_adherent', $id_auteur);
	set_request('argent', '12,34');
	set_request('colis', '');
	set_request('valeur', '0');
	set_request('contrepartie', '');
	set_request('commentaire', 'Fixture transactionnelle Association 4');

	[$id_don] = action_editer_asso_dons(0);
	$don = sql_fetsel('*', 'spip_asso_dons', 'id_don=' . (int) $id_don);
	include_spip('inc/association_dons_comptabilite');
	$compte = association_dons_compte_lire($id_don);

	if (!$don || !$compte
		|| (int) $don['id_adherent'] !== $id_auteur
		|| (int) $compte['id_auteur'] !== $id_auteur
		|| (int) $compte['id_objet'] !== (int) $id_don
		|| $compte['objet'] !== 'asso_don'
	) {
		throw new RuntimeException('Le don et son écriture comptable ne sont pas reliés canoniquement.');
	}

	sql_query('ROLLBACK');
	echo "OK: création Dons transactionnelle, auteur et objet comptable cohérents.\n";
} catch (Throwable $e) {
	sql_query('ROLLBACK');
	fwrite(STDERR, 'ECHEC: ' . $e->getMessage() . "\n");
	exit(1);
}
