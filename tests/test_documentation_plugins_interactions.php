<?php
$racine = dirname(__DIR__);
$documentation = file_get_contents($racine . '/docs/reference-plugins-interactions.md');
$erreurs = array();

$paquets = array_merge(array($racine . '/paquet.xml'), glob($racine . '/plugins/association-*/paquet.xml') ?: array());
foreach ($paquets as $paquet) {
	$xml = simplexml_load_file($paquet);
	$prefixe = (string) $xml['prefix'];
	if (!str_contains($documentation, '`' . $prefixe . '`')) {
		$erreurs[] = "préfixe absent de la documentation : $prefixe";
	}
	foreach (array('necessite', 'utilise') as $relation) {
		foreach ($xml->{$relation} as $dependance) {
			$nom = (string) $dependance['nom'];
			if ($nom !== 'association' && stripos($documentation, $nom) === false) {
				$erreurs[] = "dépendance absente de la documentation : $prefixe -> $nom";
			}
		}
	}
}

foreach (glob($racine . '/plugins/association-*/inc/*_installation.php') ?: array() as $inventaire) {
	$source = file_get_contents($inventaire);
	preg_match_all("/'(spip_[a-z0-9_]+)'/", $source, $tables);
	foreach (array_unique($tables[1]) as $table) {
		if (!str_contains($documentation, '`' . $table . '`')) {
			$erreurs[] = "table inventoriée absente de la documentation : $table";
		}
	}
}

foreach (array(
	'association_profil_participant', 'association_contexte_familial',
	'association_demander_contrat', 'association_comptabiliser_operation',
	'association_notifier_metier',
) as $api) {
	if (!str_contains($documentation, '`' . $api . '()`')) {
		$erreurs[] = "API de capacité absente de la documentation : $api";
	}
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "OK: tous les plugins, dépendances, inventaires et contrats communs sont documentés.\n";
