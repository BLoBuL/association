<?php

$racine = dirname(__DIR__);
$formulaire = file_get_contents($racine . '/plugins/association-adhesions/formulaires/supprimer_asso_cotisation.php');
$squelette = file_get_contents($racine . '/plugins/association-adhesions/formulaires/supprimer_asso_cotisation.html');
$compta = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_ecritures.php');
$erreurs = array();

foreach (array(
	"(int) _request('id_compte')",
	"autoriser('modifier', 'asso_compte', \$id_compte)",
	"sql_delete('spip_asso_cotisations'",
	"sql_delete('spip_documents_liens'",
	'association_compta_ecriture_supprimer($id_compte)',
) as $attendu) {
	if (strpos($formulaire, $attendu) === false) {
		$erreurs[] = 'Suppression incomplète ou non protégée : ' . $attendu;
	}
}
if (strpos($compta, "sql_delete('spip_asso_destination_op'") === false
	|| strpos($compta, "sql_delete('spip_asso_comptes'") === false) {
	$erreurs[] = 'L’API Compta ne supprime pas l’écriture et ses ventilations.';
}
if (strpos($formulaire, "'supprimer_transaction' => 'non'") === false
	|| strpos($squelette, 'id="supprimer_transaction_non"') === false) {
	$erreurs[] = 'La transaction destructive n’est pas désactivée par défaut.';
}
if (strpos($formulaire, 'if ($supprimer_transaction)') !== false) {
	$erreurs[] = 'L’ancien contrôle de variable indéfinie est encore présent.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: la suppression de cotisation nettoie métier, comptabilité et liens.\n";
