<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Enregistre une vente manuelle ou issue d'une commande, sans dépendance dure.
 */
function association_ventes_enregistrer(array $vente): array {
	$vente += [
		'id_vente' => 0,
		'id_produit' => null,
		'id_commande' => null,
		'id_commandes_detail' => null,
		'origine' => 'manuelle',
		'article' => '',
		'code' => '',
		'acheteur' => '',
		'id_acheteur' => 0,
		'quantite' => 1,
		'date_vente' => date('Y-m-d'),
		'date_envoi' => '0000-00-00',
		'prix_vente' => 0,
		'prix_unitaire_ht' => null,
		'taxe' => null,
		'reduction' => null,
		'frais_envoi' => 0,
		'commentaire' => '',
	];

	$champs = array_intersect_key($vente, array_flip([
		'id_produit', 'id_commande', 'id_commandes_detail', 'origine', 'article', 'code',
		'acheteur', 'id_acheteur', 'quantite', 'date_vente', 'date_envoi', 'prix_vente',
		'prix_unitaire_ht', 'taxe', 'reduction', 'frais_envoi', 'commentaire',
	]));

	$id_vente = (int) $vente['id_vente'];
	if (!$id_vente && !empty($vente['id_commande']) && !empty($vente['id_commandes_detail'])) {
		$id_vente = (int) sql_getfetsel(
			'id_vente',
			'spip_asso_ventes',
			[
				'id_commande=' . (int) $vente['id_commande'],
				'id_commandes_detail=' . (int) $vente['id_commandes_detail'],
			]
		);
	}

	include_spip('action/editer_objet');
	$erreur = '';
	if ($id_vente) {
		$erreur = objet_modifier('asso_vente', $id_vente, $champs);
	} else {
		$id_vente = (int) objet_inserer('asso_vente', null, $champs);
	}

	$vente['id_vente'] = $id_vente;
	$vente['enregistree'] = $id_vente > 0 && !$erreur;
	$vente['erreur'] = $erreur ?: ($id_vente ? '' : 'enregistrement_impossible');

	return $vente;
}

/**
 * Transforme les lignes Produit d'une commande validée en ventes historiques.
 * La clé commande/détail rend l'opération rejouable sans doublon.
 */
function association_ventes_commande_synchroniser(int $id_commande): array {
	include_spip('inc/association_capacites');
	if ($id_commande <= 0 || !association_plugin_actif('commandes')) {
		return [];
	}
	$commande = sql_fetsel('*', 'spip_commandes', 'id_commande=' . $id_commande);
	if (!$commande || !in_array($commande['statut'] ?? '', ['attente', 'partiel', 'attente_echeance', 'paye', 'envoye'], true)) {
		return [];
	}

	include_spip('inc/association_capacites');
	$resultats = [];
	$acheteur = '';
	if (!empty($commande['id_auteur'])) {
		$acheteur = (string) sql_getfetsel('nom', 'spip_auteurs', 'id_auteur=' . (int) $commande['id_auteur']);
	}
	$lignes = sql_allfetsel('*', 'spip_commandes_details', ['id_commande=' . $id_commande, "objet='produit'", "statut!='poubelle'"]);
	foreach ($lignes as $ligne) {
		$id_produit = (int) ($ligne['id_objet'] ?? 0);
		$produit = [];
		if ($id_produit && sql_showtable('spip_produits', true)) {
			$produit = sql_fetsel('titre,reference', 'spip_produits', 'id_produit=' . $id_produit) ?: [];
		}
		$prix_ht = (float) ($ligne['prix_unitaire_ht'] ?? 0);
		$taxe = (float) ($ligne['taxe'] ?? 0);
		$reduction = (float) ($ligne['reduction'] ?? 0);
		$prix_ttc = $prix_ht * (1 + $taxe) * (1 - $reduction);

		$resultats[] = association_enregistrer_vente([
			'id_produit' => $id_produit ?: null,
			'id_commande' => $id_commande,
			'id_commandes_detail' => (int) $ligne['id_commandes_detail'],
			'origine' => 'commande',
			'article' => (string) ($ligne['descriptif'] ?: ($produit['titre'] ?? '')),
			'code' => (string) ($produit['reference'] ?? ''),
			'acheteur' => $acheteur,
			'id_acheteur' => (int) ($commande['id_auteur'] ?? 0),
			'quantite' => (float) ($ligne['quantite'] ?? 1),
			'date_vente' => substr((string) ($commande['date'] ?? date('Y-m-d')), 0, 10),
			'date_envoi' => !empty($commande['date_envoi']) ? substr((string) $commande['date_envoi'], 0, 10) : '0000-00-00',
			'prix_vente' => $prix_ttc,
			'prix_unitaire_ht' => $prix_ht,
			'taxe' => $taxe,
			'reduction' => $reduction,
			'commentaire' => 'Synchronisation depuis la commande #' . $id_commande,
		]);
	}

	return $resultats;
}
