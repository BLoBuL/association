<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Jeu de donnees idempotent pour la recette manuelle de la suite Association 4.
 *
 * Aucun envoi, encaissement ou appel a une passerelle externe n'est effectue.
 * Les objets sont reconnaissables grace au marqueur RECETTE-ASSO4.
 */

$marqueur = 'RECETTE-ASSO4';
$maintenant = date('Y-m-d H:i:s');
$aujourdhui = date('Y-m-d');
$dans_un_an = date('Y-m-d', strtotime('+1 year'));
$resultats = array();

$ecrire_meta_association = static function ($nom, $valeur, $impt = 'non') {
	$donnees = array('nom' => $nom, 'valeur' => (string) $valeur, 'impt' => $impt);
	if (sql_countsel('spip_association_metas', 'nom=' . sql_quote($nom))) {
		return sql_updateq('spip_association_metas', $donnees, 'nom=' . sql_quote($nom));
	}
	return sql_insertq('spip_association_metas', $donnees);
};

$upsert = static function ($table, $cle, $valeur, array $donnees) {
	$where = $cle . '=' . (is_int($valeur) ? $valeur : sql_quote($valeur));
	$id = sql_getfetsel($cle, $table, $where);
	if ($id) {
		sql_updateq($table, $donnees, $where);
		return (int) $id;
	}
	return (int) sql_insertq($table, $donnees);
};

$upsert_champ = static function ($table, $champ_recherche, $valeur_recherche, $cle, array $donnees) {
	$where = $champ_recherche . '=' . sql_quote($valeur_recherche);
	$id = sql_getfetsel($cle, $table, $where);
	if ($id) {
		sql_updateq($table, $donnees, $where);
		return (int) $id;
	}
	return (int) sql_insertq($table, $donnees);
};

// Socle et auteur commun aux parcours metier.
$ecrire_meta_association('recette_asso4', json_encode(array('marqueur' => $marqueur, 'maj' => $maintenant)));
$resultats['association_meta'] = 'recette_asso4';
$auteur = array(
	'nom' => '[' . $marqueur . '] Camille Recette',
	'email' => 'recette-association4@example.invalid',
	'statut' => '6forum',
	'lang' => 'fr',
	'bio' => 'Compte fictif reserve aux tests fonctionnels de la suite Association 4.',
);
$champs_auteurs = array_keys((array) ((sql_showtable('spip_auteurs', true)['field'] ?? array())));
foreach (array(
	'prenom' => 'Camille', 'nom_famille' => 'Recette', 'sexe' => 'Mme',
	'telephone' => '+33 1 00 00 00 00', 'mobile' => '+33 6 00 00 00 00',
	'fonction' => 'Responsable recette', 'statut_interne' => 'ok',
	'inscription' => $aujourdhui, 'validite' => $dans_un_an,
) as $champ => $valeur) {
	if (in_array($champ, $champs_auteurs, true)) {
		$auteur[$champ] = $valeur;
	}
}
$id_auteur = $resultats['auteur'] = $upsert_champ('spip_auteurs', 'email', $auteur['email'], 'id_auteur', $auteur);

// Contenus SPIP communs a Evenements, Commerce et Communication.
$id_rubrique = $resultats['rubrique'] = $upsert_champ('spip_rubriques', 'titre', '[' . $marqueur . '] Contenus de recette', 'id_rubrique', array(
	'id_parent' => 0, 'titre' => '[' . $marqueur . '] Contenus de recette',
	'descriptif' => 'Rubrique fictive pour tester les modules Association 4.',
	'statut' => 'publie', 'date' => $maintenant, 'lang' => 'fr',
));
sql_updateq('spip_rubriques', array('id_secteur' => $id_rubrique), 'id_rubrique=' . $id_rubrique);
$id_article_evenement = $resultats['article_evenement'] = $upsert_champ('spip_articles', 'titre', '[' . $marqueur . '] Agenda et inscriptions', 'id_article', array(
	'titre' => '[' . $marqueur . '] Agenda et inscriptions', 'id_rubrique' => $id_rubrique,
	'id_secteur' => $id_rubrique, 'texte' => 'Article support du parcours evenement public.',
	'statut' => 'publie', 'date' => $maintenant, 'date_modif' => $maintenant, 'lang' => 'fr',
));
$id_article_boutique = $resultats['article_boutique'] = $upsert_champ('spip_articles', 'titre', '[' . $marqueur . '] Guide associatif', 'id_article', array(
	'titre' => '[' . $marqueur . '] Guide associatif', 'id_rubrique' => $id_rubrique,
	'id_secteur' => $id_rubrique, 'descriptif' => 'Produit fictif de recette.',
	'texte' => 'Article testable dans le catalogue et le panier.', 'statut' => 'publie',
	'date' => $maintenant, 'date_modif' => $maintenant, 'lang' => 'fr',
));
$id_produit = $resultats['produit'] = $upsert_champ('spip_produits', 'reference', 'RECETTE-GUIDE-01', 'id_produit', array(
	'id_rubrique' => $id_rubrique, 'id_secteur' => $id_rubrique,
	'titre' => '[' . $marqueur . '] Guide associatif', 'reference' => 'RECETTE-GUIDE-01',
	'descriptif' => 'Produit fictif pour tester le catalogue Produits.',
	'texte' => 'Ce produit ne doit jamais être livré ni encaissé.',
	'prix_ht' => '15.000000', 'taxe' => '0.0000', 'statut' => 'publie',
	'lang' => 'fr', 'date' => $maintenant, 'date_com' => $maintenant, 'immateriel' => 0,
));
$resultats['article_email'] = $upsert_champ('spip_articles', 'page', 'email_collectif_recette_asso4', 'id_article', array(
	'titre' => '[' . $marqueur . '] Gabarit communication', 'page' => 'email_collectif_recette_asso4',
	'id_rubrique' => $id_rubrique, 'id_secteur' => $id_rubrique,
	'texte' => 'Bonjour, ceci est un apercu de recette. Aucun message ne doit etre envoye.',
	'statut' => 'prepa', 'date' => $maintenant, 'date_modif' => $maintenant, 'lang' => 'fr',
));

// Adhesions / cotisations.
$id_categorie_adherent = $resultats['categorie_adherent'] = $upsert_champ('spip_asso_categories_adherents', 'valeur', '[' . $marqueur . '] Adherent individuel', 'id_categorie', array(
	'valeur' => '[' . $marqueur . '] Adherent individuel', 'statut' => 'ok', 'cotisation' => 25,
	'devise' => 'EUR', 'paiement_en_ligne' => 0, 'commentaires' => 'Categorie de recette.',
	'type_adherent' => 'adherent', 'validation' => 'auto', 'document_justificatif' => 'non',
));

// Evenements et inscription sans transaction reelle.
$id_evenement = $resultats['evenement'] = $upsert_champ('spip_evenements', 'titre', '[' . $marqueur . '] Atelier decouverte', 'id_evenement', array(
	'id_article' => $id_article_evenement, 'titre' => '[' . $marqueur . '] Atelier decouverte',
	'descriptif' => 'Evenement fictif ouvert aux visiteurs et aux adherents.',
	'date_debut' => date('Y-m-d 18:00:00', strtotime('+14 days')),
	'date_fin' => date('Y-m-d 20:00:00', strtotime('+14 days')),
	'lieu' => 'Maison des associations', 'adresse' => '1 rue de la Recette',
	'inscription' => 1, 'places' => 20, 'horaire' => 'oui', 'statut' => 'publie',
	'payant' => 'oui', 'mode_paiement' => '', 'validation_sur_paiement' => 'non',
	'type_inscrits_evenement' => 'indifferent', 'fermeture_inscription' => 'last_minute',
));
$id_categorie_activite = $resultats['categorie_activite'] = $upsert_champ('spip_asso_categories_activites', 'valeur', '[' . $marqueur . '] Tarif general', 'id_categorie', array(
	'valeur' => '[' . $marqueur . '] Tarif general', 'statut' => 'ok', 'quantite' => 1,
	'paiement_en_ligne' => 0, 'commentaires' => 'Tarif generique de recette.', 'deleted' => 0,
	'type_inscrit' => 'indifferent',
));
$lien_tarif = sql_countsel('spip_asso_categories_activites_liens', 'id_evenement=' . $id_evenement . ' AND id_categorie=' . $id_categorie_activite);
if ($lien_tarif) {
	sql_updateq('spip_asso_categories_activites_liens', array('montant' => '12.50'), 'id_evenement=' . $id_evenement . ' AND id_categorie=' . $id_categorie_activite);
} else {
	sql_insertq('spip_asso_categories_activites_liens', array('id_evenement' => $id_evenement, 'id_categorie' => $id_categorie_activite, 'montant' => '12.50'));
}
$resultats['activite'] = $upsert_champ('spip_asso_activites', 'email_inscrit', 'recette-association4@example.invalid', 'id_activite', array(
	'id_evenement' => $id_evenement, 'id_auteur' => $id_auteur, 'email_inscrit' => 'recette-association4@example.invalid',
	'nom_inscrit' => 'Recette', 'prenom_inscrit' => 'Camille', 'tel_inscrit' => '+33 6 00 00 00 00',
	'statut' => 'ok', 'tarifs_selectionnes' => json_encode(array($id_categorie_activite => 1)),
	'nombre_inscrits' => 1, 'id_transaction' => 0, 'commentaire' => $marqueur,
	'journal' => 'Inscription fictive creee par le jeu de recette.', 'date' => $maintenant, 'maj' => $maintenant,
));

// Paiements : transaction strictement fictive, non reglee et sans passerelle.
$id_transaction = $resultats['transaction'] = $upsert_champ('spip_transactions', 'auteur_id', $marqueur, 'id_transaction', array(
	'id_auteur' => $id_auteur, 'auteur_id' => $marqueur, 'auteur' => 'Camille Recette',
	'date_transaction' => $maintenant, 'contenu' => 'Transaction fictive pour recette fonctionnelle.',
	'montant_ht' => '25.00', 'montant' => '25.00', 'devise' => 'EUR', 'mode' => 'recette',
	'statut' => 'attente', 'reglee' => 'non', 'finie' => 0,
	'message' => 'Ne pas encaisser - donnees de recette.', 'data' => json_encode(array('fixture' => $marqueur)),
));

// Comptabilite et cotisation liee, sans imposer un paiement.
$id_plan = $resultats['plan'] = $upsert_champ('spip_asso_plan', 'code', 'RECETTE-756', 'id_plan', array(
	'code' => 'RECETTE-756', 'intitule' => '[' . $marqueur . '] Produits associatifs',
	'classe' => '7', 'type_op' => 'credit', 'commentaire' => 'Compte fictif de recette.', 'active' => 1,
));
$id_destination = $resultats['destination'] = $upsert_champ('spip_asso_destination', 'intitule', '[' . $marqueur . '] Activites membres', 'id_destination', array(
	'intitule' => '[' . $marqueur . '] Activites membres', 'commentaire' => 'Destination analytique fictive.',
));
$id_cotisation = $resultats['cotisation'] = $upsert_champ('spip_asso_cotisations', 'id_auteur', $id_auteur, 'id_cotisation', array(
	'id_auteur' => $id_auteur, 'id_categorie' => $id_categorie_adherent, 'id_transaction' => 0,
	'inscription' => 'recette', 'statut' => 'ok', 'date_creation' => $maintenant,
	'date_debut_validite' => $aujourdhui, 'date_fin_validite' => $dans_un_an,
	'montant' => '25.000000', 'devise' => 'EUR',
));
$id_compte_cotisation = $resultats['compte_cotisation'] = $upsert_champ('spip_asso_comptes', 'justification', '[' . $marqueur . '] Cotisation annuelle', 'id_compte', array(
	'id_auteur' => $id_auteur, 'date' => $aujourdhui, 'id_transaction' => 0,
	'objet' => 'asso_cotisation', 'id_objet' => $id_cotisation, 'recette' => 25, 'depense' => 0,
	'justification' => '[' . $marqueur . '] Cotisation annuelle', 'imputation' => 'RECETTE-756',
	'journal' => 'recette', 'vu' => 1,
));
sql_updateq('spip_asso_cotisations', array('id_compte' => $id_compte_cotisation), 'id_cotisation=' . $id_cotisation);
$resultats['destination_operation'] = $upsert_champ('spip_asso_destination_op', 'id_compte', $id_compte_cotisation, 'id_dest_op', array(
	'id_compte' => $id_compte_cotisation, 'id_destination' => $id_destination, 'recette' => 25, 'depense' => 0,
));

// Dons, ventes et prets.
$id_don = $resultats['don'] = $upsert_champ('spip_asso_dons', 'bienfaiteur', '[' . $marqueur . '] Camille Recette', 'id_don', array(
	'date_don' => $aujourdhui, 'bienfaiteur' => '[' . $marqueur . '] Camille Recette', 'id_adherent' => $id_auteur,
	'argent' => '50.00', 'colis' => '', 'valeur' => '50.00', 'contrepartie' => 'Aucune', 'commentaire' => 'Don fictif.',
));
$resultats['compte_don'] = $upsert_champ('spip_asso_comptes', 'justification', '[' . $marqueur . '] Don', 'id_compte', array(
	'id_auteur' => $id_auteur, 'date' => $aujourdhui, 'objet' => 'asso_don', 'id_objet' => $id_don,
	'recette' => 50, 'depense' => 0, 'justification' => '[' . $marqueur . '] Don', 'imputation' => 'RECETTE-756', 'journal' => 'recette', 'vu' => 1,
));
$id_vente = $resultats['vente'] = $upsert_champ('spip_asso_ventes', 'code', 'RECETTE-GUIDE-01', 'id_vente', array(
	'article' => '[' . $marqueur . '] Guide associatif', 'code' => 'RECETTE-GUIDE-01',
	'acheteur' => 'Camille Recette', 'id_acheteur' => $id_auteur, 'quantite' => '2',
	'date_vente' => $aujourdhui, 'prix_vente' => '15.00', 'frais_envoi' => 3.5, 'commentaire' => 'Vente fictive.',
));
$resultats['compte_vente'] = $upsert_champ('spip_asso_comptes', 'justification', '[' . $marqueur . '] Vente', 'id_compte', array(
	'id_auteur' => $id_auteur, 'date' => $aujourdhui, 'objet' => 'asso_vente', 'id_objet' => $id_vente,
	'recette' => 33.5, 'depense' => 0, 'justification' => '[' . $marqueur . '] Vente', 'imputation' => 'RECETTE-756', 'journal' => 'recette', 'vu' => 1,
));
$id_ressource = $resultats['ressource'] = $upsert_champ('spip_asso_ressources', 'code', 'RECETTE-MATERIEL-01', 'id_ressource', array(
	'code' => 'RECETTE-MATERIEL-01', 'intitule' => '[' . $marqueur . '] Videoprojecteur',
	'date_acquisition' => $aujourdhui, 'pu' => 450, 'statut' => 'reserve', 'commentaire' => 'Ressource fictive.',
));
$resultats['pret'] = $upsert_champ('spip_asso_prets', 'id_ressource', $id_ressource, 'id_pret', array(
	'id_ressource' => $id_ressource, 'date_sortie' => $aujourdhui, 'duree' => 7,
	'date_retour' => '0000-00-00', 'id_emprunteur' => $id_auteur, 'statut' => 'reserve',
	'commentaire_sortie' => 'Pret fictif en cours.', 'commentaire_retour' => '',
));

// Communication : abonne non actif et gabarit en preparation, donc aucun envoi possible.
$resultats['abonne'] = $upsert_champ('spip_mailsubscribers', 'email', 'recette-association4@example.invalid', 'id_mailsubscriber', array(
	'email' => 'recette-association4@example.invalid', 'nom' => '[' . $marqueur . '] Camille Recette',
	'optin' => 'non', 'date' => $maintenant, 'statut' => 'prepa', 'lang' => 'fr',
));

// Commerce : panier Produit et commande fictive, sans transaction ni paiement.
$id_panier = $resultats['panier'] = $upsert_champ('spip_paniers', 'cookie', $marqueur, 'id_panier', array(
	'id_auteur' => $id_auteur, 'cookie' => $marqueur, 'statut' => 'encours', 'date' => $maintenant,
));
if (sql_countsel('spip_paniers_liens', 'id_panier=' . $id_panier . " AND objet='produit' AND id_objet=" . $id_produit)) {
	sql_updateq('spip_paniers_liens', array('quantite' => 2), 'id_panier=' . $id_panier . " AND objet='produit' AND id_objet=" . $id_produit);
} else {
	sql_insertq('spip_paniers_liens', array('id_panier' => $id_panier, 'objet' => 'produit', 'id_objet' => $id_produit, 'quantite' => 2, 'reduction' => 0, 'rang' => 1));
}
$id_commande = $resultats['commande'] = $upsert_champ('spip_commandes', 'reference', 'RECETTE-ASSO4-CMD', 'id_commande', array(
	'reference' => 'RECETTE-ASSO4-CMD', 'source' => 'association_recette', 'id_auteur' => $id_auteur,
	'statut' => 'attente', 'date' => $maintenant,
));
$resultats['detail_commande'] = $upsert_champ('spip_commandes_details', 'descriptif', '[' . $marqueur . '] Guide associatif', 'id_commandes_detail', array(
	'id_commande' => $id_commande, 'descriptif' => '[' . $marqueur . '] Guide associatif', 'quantite' => 2,
	'prix_unitaire_ht' => 15, 'taxe' => 0, 'reduction' => 0, 'statut' => 'attente', 'objet' => 'produit', 'id_objet' => $id_produit,
));
include_spip('inc/association_ventes_commandes');
$ventes_commande = association_ventes_commande_synchroniser($id_commande);
$resultats['vente_commande'] = (int) ($ventes_commande[0]['id_vente'] ?? 0);
$ecrire_meta_association('commerce_rubrique', $id_rubrique);
$ecrire_meta_association('commerce_devise', 'EUR');
// Nettoie les deux cles d'une ancienne version du script, jamais utilisees par le formulaire Association.
effacer_config('association/commerce_rubrique');
effacer_config('association/commerce_devise');

// Partenaires et bannieres.
$id_organisation = $resultats['organisation'] = $upsert_champ('spip_organisations', 'nom', '[' . $marqueur . '] Maison des associations', 'id_organisation', array(
	'nom' => '[' . $marqueur . '] Maison des associations', 'statut_juridique' => 'association',
	'activite' => 'Partenaire fictif', 'date_creation' => $aujourdhui, 'descriptif' => 'Organisation de recette.',
	'url_site' => 'https://example.invalid/recette-asso4',
));
$resultats['partenaire'] = $upsert_champ('spip_asso_partenaires', 'titre', '[' . $marqueur . '] Partenaire principal', 'id_partenaire', array(
	'id_organisation' => $id_organisation, 'titre' => '[' . $marqueur . '] Partenaire principal',
	'niveau' => 'or', 'descriptif' => 'Partenaire fictif affiche en recette.', 'url' => 'https://example.invalid/partenaire',
	'ordre' => 10, 'date_debut' => $aujourdhui, 'date_fin' => $dans_un_an, 'statut' => 'publie',
));
$resultats['banniere'] = $upsert_champ('spip_asso_bannieres', 'titre', '[' . $marqueur . '] Bienvenue sur la recette', 'id_banniere', array(
	'titre' => '[' . $marqueur . '] Bienvenue sur la recette',
	'descriptif' => 'Banniere fictive pour verifier le rendu frontal.', 'url' => 'https://example.invalid/recette-asso4',
	'emplacement' => 'principal', 'ordre' => 10, 'date_debut' => $aujourdhui, 'date_fin' => $dans_un_an, 'statut' => 'publie',
));

// Bons plans : un objet publié et expirant dans un an pour les parcours BO/FO.
$resultats['bon_plan'] = $upsert_champ('spip_bons_plans', 'titre', '[' . $marqueur . '] Atelier solidaire', 'id_bon_plan', array(
	'id_rubrique' => $id_rubrique, 'id_secteur' => $id_rubrique,
	'titre' => '[' . $marqueur . '] Atelier solidaire',
	'texte' => 'Bon plan fictif réservé à la recette de la suite Association 4.',
	'url_site_internet' => 'https://example.invalid/bon-plan', 'adresse' => '1 rue de la Recette',
	'telephone' => '+33 1 00 00 00 00', 'email_contact' => 'bon-plan@example.invalid',
	'date' => $maintenant, 'date_depublication' => $dans_un_an, 'statut' => 'publie', 'lang' => 'fr',
));

ksort($resultats);
echo json_encode(array(
	'marqueur' => $marqueur,
	'objets' => $resultats,
	'protections' => array('email_envoye' => false, 'paiement_declenche' => false, 'transaction_statut' => 'attente'),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
