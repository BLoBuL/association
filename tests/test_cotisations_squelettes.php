<?php

$racine = dirname(__DIR__);
$fichiers = array(
	'export_cotisations.csv.html',
	'prive/objets/liste/item_inscription_adherent.html',
	'prive/objets/liste/item_destinataire_email_collectif.html',
	'prive/objets/liste/transactions.html',
);
$erreurs = array();
foreach ($fichiers as $fichier) {
	$contenu = file_get_contents($racine . '/' . $fichier);
	if (preg_match('/ASSO_COMPTES[^\n]*(?:cotisation|id_transaction)/i', $contenu)) {
		$erreurs[] = $fichier . ' interroge encore le journal pour une cotisation';
	}
	foreach (array('#STATUT_COTISATION', '#REINSCRIPTION') as $balise) {
		if (strpos($contenu, $balise) !== false) {
			$erreurs[] = $fichier . ' utilise encore la balise supprimée ' . $balise;
		}
	}
}

$suppression = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/cotisation_suppression.html');
if (!str_contains($suppression, '(ASSO_COTISATIONS)') || str_contains($suppression, '(ASSO_COMPTES)')) {
	$erreurs[] = 'La page de suppression doit charger la cotisation depuis ASSO_COTISATIONS.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: les squelettes de cotisation utilisent leur table métier.\n";
