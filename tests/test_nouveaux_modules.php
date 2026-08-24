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
if ($erreurs) { fwrite(STDERR, implode("\n", $erreurs) . "\n"); exit(1); }
echo "OK nouveaux modules autonomes\n";
