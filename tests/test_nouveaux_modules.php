<?php
$racine = dirname(__DIR__);
$modules = array(
	'association-commerce' => array('association_commerce', array('association', 'paniers', 'commandes', 'prix', 'produits'), 'commerce'),
	'association-partenaires' => array('association_partenaires', array('association', 'contacts'), 'partenaires'),
	'association-bannieres' => array('association_bannieres', array('association'), 'bannieres'),
	'association-bons-plans' => array('association_bons_plans', array('association'), 'bons_plans'),
);
$erreurs = array();
foreach ($modules as $dossier => [$prefixe, $dependances, $page]) {
	$base = "$racine/plugins/$dossier";
	$paquet = is_file("$base/paquet.xml") ? file_get_contents("$base/paquet.xml") : '';
	foreach ($dependances as $dependance) {
		if (!str_contains($paquet, '<necessite nom="' . $dependance . '"')) {
			$erreurs[] = "$dossier ne déclare pas $dependance";
		}
	}
	if (!str_contains($paquet, 'association_menu_entrees') || !str_contains($paquet, 'association_configuration_navigation')) {
		$erreurs[] = "$dossier ne possède pas son menu ou sa configuration";
	}
	if (!is_file("$base/prive/squelettes/contenu/$page.html")) {
		$erreurs[] = "$dossier ne possède pas sa page privée $page";
	}
}
if (!is_file("$racine/plugins/association-commerce/squelettes/boutique.html")) { $erreurs[] = 'page publique commerce absente'; }
foreach (array('panier', 'commande') as $page_commerce) {
	if (!is_file("$racine/plugins/association-commerce/squelettes/$page_commerce.html")) { $erreurs[] = "page publique commerce $page_commerce absente"; }
}
$catalogue_commerce = file_get_contents("$racine/plugins/association-commerce/inclure/association-commerce-catalogue.html");
if (!str_contains($catalogue_commerce, '(PRODUITS)') || !str_contains($catalogue_commerce, '#FORMULAIRE_REMPLIR_PANIER{produit,#ID_PRODUIT}')) { $erreurs[] = 'le catalogue commerce ne fournit pas les produits au formulaire natif d ajout au panier'; }
if (str_contains($catalogue_commerce, '(ARTICLES)') || str_contains($catalogue_commerce, '{article,#ID_ARTICLE}')) { $erreurs[] = 'le catalogue commerce utilise encore les articles SPIP'; }
if (str_contains($catalogue_commerce, '#FORMULAIRE_PANIER{article,')) { $erreurs[] = 'le formulaire d affichage du panier est utilise a tort pour ajouter un produit'; }
if (!str_contains(file_get_contents("$racine/plugins/association-commerce/paquet.xml"), '<pipeline nom="taxes" action=""')) { $erreurs[] = 'la compatibilite du pipeline taxes de Prix 2.0.0 est absente'; }
$page_panier = file_get_contents("$racine/plugins/association-commerce/squelettes/panier.html");
$page_commande = file_get_contents("$racine/plugins/association-commerce/squelettes/commande.html");
if (!str_contains($page_panier, '#FORMULAIRE_PANIER') || !str_contains($page_panier, 'commandes_paniers')) { $erreurs[] = 'la page panier ne permet pas de creer une commande native'; }
if (!str_contains($page_commande, '(COMMANDES)') || !str_contains($page_commande, 'inclure/commande')) { $erreurs[] = 'la page commande ne fournit pas le recapitulatif natif'; }
if (!str_contains($page_commande, '#ENV{id_commande,#SESSION{id_commande}}')) { $erreurs[] = 'la page commande ne relit pas la commande creee en session'; }
if (!is_file("$racine/plugins/association-partenaires/squelettes/partenaires.html")) { $erreurs[] = 'page publique partenaires absente'; }
if (!is_file("$racine/plugins/association-bannieres/modeles/asso_bannieres.html")) { $erreurs[] = 'modèle public bannières absent'; }
$modele_bannieres = file_get_contents("$racine/plugins/association-bannieres/modeles/asso_bannieres.html");
$modele_partenaires = file_get_contents("$racine/plugins/association-partenaires/modeles/asso_partenaires.html");
if (!str_contains($modele_bannieres, 'association_bannieres_ids_actives')) { $erreurs[] = 'les périodes de diffusion des bannières ne sont pas appliquées au public'; }
if (!str_contains($modele_partenaires, 'association_partenaires_ids_actifs')) { $erreurs[] = 'les périodes des partenariats ne sont pas appliquées au public'; }
$form_partenaire = file_get_contents("$racine/plugins/association-partenaires/formulaires/editer_asso_partenaire.php");
$form_banniere = file_get_contents("$racine/plugins/association-bannieres/formulaires/editer_asso_banniere.php");
if (!str_contains($form_partenaire, "formulaires_editer_objet_charger('partenaire'")) { $erreurs[] = 'CVT partenaire relié au mauvais objet SPIP'; }
if (!str_contains($form_banniere, "formulaires_editer_objet_charger('banniere'")) { $erreurs[] = 'CVT bannière relié au mauvais objet SPIP'; }
foreach (array('association-partenaires' => 'partenaires', 'association-bannieres' => 'bannieres') as $module => $objet) {
	$administration = file_get_contents("$racine/plugins/$module/association_{$objet}_administrations.php");
	if (!str_contains($administration, "'1.0.0' =>")) { $erreurs[] = "$module ne reprend pas une installation interrompue à init"; }
}
if ($erreurs) { fwrite(STDERR, implode("\n", $erreurs) . "\n"); exit(1); }
echo "OK nouveaux modules autonomes\n";
