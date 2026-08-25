<?php
$racine = dirname(__DIR__);
$modules = array(
	'association-commerce' => array('association_commerce', array('association', 'paniers', 'commandes', 'prix'), 'commerce'),
	'association-partenaires' => array('association_partenaires', array('association', 'contacts'), 'partenaires'),
	'association-bannieres' => array('association_bannieres', array('association'), 'bannieres'),
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
