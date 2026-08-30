<?php

$racine = dirname(__DIR__);
$modules = array(
	'association_adhesions',
	'association_communication',
	'association_compta',
	'association_dons',
	'association_evenements',
	'association_groupes',
	'association_paiements',
	'association_prets',
	'association_ventes',
	'association_commerce',
	'association_partenaires',
	'association_bannieres',
	'association_bons_plans',
);
$erreurs = array();
$metiers = array_flip($modules);

$socle = simplexml_load_file($racine . '/paquet.xml');
foreach ($socle->necessite as $dependance) {
	$nom = (string) $dependance['nom'];
	if (isset($metiers[$nom]) || $nom === 'inscription4') {
		$erreurs[] = "Le socle exige encore $nom.";
	}
}

foreach ($modules as $module) {
	$dossier = str_replace('_', '-', $module);
	$paquet = simplexml_load_file($racine . '/plugins/' . $dossier . '/paquet.xml');
	$necessites = array();
	foreach ($paquet->necessite as $dependance) {
		$necessites[] = (string) $dependance['nom'];
	}
	if (!in_array('association', $necessites, true)) {
		$erreurs[] = "$module ne nécessite pas le socle.";
	}
	foreach ($necessites as $nom) {
		if ($nom !== $module && isset($metiers[$nom])) {
			$erreurs[] = "$module exige encore le module métier $nom.";
		}
	}
}

$facade_evenements = file_get_contents(
	$racine . '/plugins/association-evenements/inc/association_evenements_paiements.php'
);
foreach (array(
	'association_evenements_paiements_actifs',
	'association_evenements_transaction_creer',
	'association_evenements_transaction_lire',
	'association_evenements_transactions_lire',
	'association_evenements_transaction_modifier',
	'association_evenements_transaction_supprimer_non_encaissee',
) as $fonction) {
	if (!str_contains($facade_evenements, 'function ' . $fonction . '(')) {
		$erreurs[] = "La façade Événements ne fournit pas $fonction.";
	}
}

$schema_cotisations = file_get_contents(
	$racine . '/plugins/association-adhesions/base/association_adhesions.php'
);
if (!preg_match("/'id_compte'\s*=>\s*'BIGINT NULL DEFAULT NULL'/", $schema_cotisations)
	|| preg_match("/'KEY id_compte'\s*=>\s*'UNIQUE KEY/", $schema_cotisations)) {
	$erreurs[] = 'Le lien comptable des cotisations doit être nullable et non unique.';
}

$compilateur = file_get_contents($racine . '/tests/spip/compiler_suite_squelettes.php');
if (str_contains($compilateur, 'Plugin actif sans constante de chemin')) {
	$erreurs[] = 'Le compilateur ne doit pas traiter un complément désactivé comme une erreur.';
}

if ($erreurs) {
	fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL);
	exit(1);
}

echo "OK: dépendances internes autonomes, façades optionnelles et cotisations découplées.\n";
