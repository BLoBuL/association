<?php

$racine = dirname(__DIR__);
$module = $racine . '/plugins/association-evenements';
$erreurs = array();

$schema = file_get_contents($module . '/base/association_evenements.php');
$administration = file_get_contents($module . '/association_evenements_administrations.php');
$paquet = file_get_contents($module . '/paquet.xml');
$inventaire = file_get_contents($module . '/inc/association_evenements_installation.php');

if (!str_contains($schema, "'tarifs_selectionnes' => 'TEXT NOT NULL'")) {
	$erreurs[] = 'Le schéma Événements ne déclare pas la sélection tarifaire.';
}
if (str_contains($schema, "'transaction' => 'TEXT NOT NULL'")) {
	$erreurs[] = 'Le mot réservé SQLite transaction subsiste dans le schéma.';
}
if (str_contains($schema, "'en_attente' =>") || str_contains($schema, "'valider' =>")) {
	$erreurs[] = 'Le schéma neuf réintroduit des colonnes Événements historiquement supprimées.';
}
if (!str_contains($administration, "['connexions'][0]['type']")
	|| !str_contains($administration, 'RENAME COLUMN "transaction" TO tarifs_selectionnes')
	|| !str_contains($administration, 'CHANGE `transaction` tarifs_selectionnes')) {
	$erreurs[] = 'La migration de sélection tarifaire ne couvre pas SQLite et MySQL.';
}
if (!str_contains($paquet, 'schema="1.2.0"')
	|| !str_contains($inventaire, "'association_evenements_base_version' => '1.2.0'")) {
	$erreurs[] = 'La version 1.2.0 du schéma Événements n’est pas cohérente.';
}

$consommateurs = array(
	'action/ajouter_activites.php',
	'action/modifier_activites.php',
	'formulaires/inc/inscription_evenement.php',
	'inc/association_evenements_rgpd.php',
	'inscriptions_evenement.csv.html',
);
foreach ($consommateurs as $fichier) {
	$contenu = file_get_contents($module . '/' . $fichier);
	if (!str_contains($contenu, 'tarifs_selectionnes') && !str_contains($contenu, 'TARIFS_SELECTIONNES')) {
		$erreurs[] = "Le consommateur $fichier n’utilise pas le nom métier.";
	}
}

$export_compta = file_get_contents($racine . '/plugins/association-compta/export_compta.xml.html');
if (!str_contains($export_compta, '#TARIFS_SELECTIONNES') || str_contains($export_compta, '#TRANSACTION|unserialize')) {
	$erreurs[] = 'L’export comptable utilise encore la colonne réservée.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: le schéma Événements conserve les tarifs et reste compatible SQLite.\n";
