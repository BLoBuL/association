<?php

$racine = dirname(__DIR__);
$erreurs = array();

$paquet = file_get_contents($racine . '/paquet.xml');
foreach (array('prefix="association_adhesions"', 'compatibilite="[4.0.0;4.*]"', 'schema="1.1.0"', 'nom="inscription4"') as $attendu) {
	if (strpos($paquet, $attendu) === false) {
		$erreurs[] = 'paquet.xml: déclaration absente: ' . $attendu;
	}
}

$base = file_get_contents($racine . '/base/association_adhesions.php');
foreach (array('spip_asso_categories_adherents', 'spip_asso_cotisations') as $table) {
	if (strpos($base, $table) === false) {
		$erreurs[] = 'table non déclarée: ' . $table;
	}
}
if (strpos($base, "'devise'") === false) {
	$erreurs[] = 'la devise doit être persistée par le module Adhésions';
}
foreach (array('type_adherent', 'date_debut_validite', 'date_fin_validite', 'validation', 'document_justificatif', 'nombre_enfants', 'mode_paiement', 'eligibilite') as $champ) {
	if (strpos($base, "'" . $champ . "'") === false) {
		$erreurs[] = 'champ de catégorie absent: ' . $champ;
	}
}
if (strpos($base, 'spip_asso_comptes') !== false) {
	$erreurs[] = 'la table comptable ne doit pas appartenir à Adhésions';
}

$administrations = file_get_contents($racine . '/association_adhesions_administrations.php');
if (strpos($administrations, 'sql_drop_table') !== false) {
	$erreurs[] = 'la désinstallation ne doit pas supprimer les données métier';
}
foreach (array('profil.html', 'fiche_adherent.html', 'inscription.html') as $page) {
	if (!is_file($racine . '/squelettes/' . $page)) {
		$erreurs[] = 'page publique absente: ' . $page;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: structure Association Adhésions.\n";
