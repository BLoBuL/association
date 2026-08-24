<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour tester le cycle Ventes.\n");
	exit(2);
}

$id_auteur = (int) sql_getfetsel('id_auteur', 'spip_auteurs', "statut!='5poubelle'", '', 'id_auteur');
if ($id_auteur <= 0) {
	fwrite(STDERR, "Aucun auteur interne disponible pour la recette Ventes.\n");
	exit(1);
}

sql_query('START TRANSACTION');
try {
	include_spip('action/editer_asso_ventes');
	set_request('date_vente', date('Y-m-d'));
	set_request('article', 'Article transactionnel de recette');
	set_request('code', 'RECETTE-VENTE');
	set_request('acheteur', 'Acheteur de recette');
	set_request('id_acheteur', $id_auteur);
	set_request('quantite', '2');
	set_request('date_envoi', date('Y-m-d'));
	set_request('frais_envoi', '1,50');
	set_request('prix_vente', '5,00');
	set_request('journal', 'RECETTE');
	set_request('commentaire', 'Fixture transactionnelle Association 4');

	[$id_vente] = action_editer_asso_ventes(0);
	$vente = sql_fetsel('*', 'spip_asso_ventes', 'id_vente=' . (int) $id_vente);
	$comptes = sql_allfetsel('*', 'spip_asso_comptes', "objet='asso_vente' AND id_objet=" . (int) $id_vente);
	$attendu = ($GLOBALS['association_metas']['pc_ventes'] ?? '') === ($GLOBALS['association_metas']['pc_frais_envoi'] ?? '') ? 1 : 2;

	if (!$vente || count($comptes) !== $attendu) {
		throw new RuntimeException("La vente ne possède pas le nombre attendu d'écritures comptables.");
	}
	foreach ($comptes as $compte) {
		if ((int) $compte['id_auteur'] !== $id_auteur || $compte['objet'] !== 'asso_vente') {
			throw new RuntimeException("Une écriture de vente ne conserve pas l'acheteur ou l'objet.");
		}
	}

	sql_query('ROLLBACK');
	echo "OK: création Ventes transactionnelle, {$attendu} écriture(s) canonique(s).\n";
} catch (Throwable $e) {
	sql_query('ROLLBACK');
	fwrite(STDERR, 'ECHEC: ' . $e->getMessage() . "\n");
	exit(1);
}
