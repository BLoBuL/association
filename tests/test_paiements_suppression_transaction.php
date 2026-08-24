<?php

$racine = dirname(__DIR__) . '/plugins/association-paiements';
$erreurs = array();

$action = file_get_contents($racine . '/action/supprimer_commande.php');
if (strpos($action, "autoriser('supprimer', 'transaction'") === false) {
	$erreurs[] = 'L action ne controle pas l autorisation de suppression.';
}
if (strpos($action, 'association_paiements_transaction_supprimer_non_encaissee(') === false) {
	$erreurs[] = 'L action contourne encore l API Paiements.';
}
if (strpos($action, "sql_delete('spip_transactions'") !== false) {
	$erreurs[] = 'L action supprime encore directement dans la table Bank.';
}

$autoriser = file_get_contents($racine . '/association_paiements_autoriser.php');
if (strpos($autoriser, "=== 'abandon'") === false
	|| strpos($autoriser, "autoriser('regler', 'transaction'") === false) {
	$erreurs[] = 'L autorisation ne limite pas la suppression aux transactions abandonnees et aux operateurs habilites.';
}

$contenu = file_get_contents($racine . '/prive/squelettes/contenu/transaction_suppression.html');
$liste = file_get_contents($racine . '/prive/objets/liste/transactions.html');
if (strpos($contenu, '#AUTORISER{supprimer,transaction,#ID_TRANSACTION}') === false
	|| strpos($liste, '#AUTORISER{supprimer,transaction,#ID_TRANSACTION}') === false) {
	$erreurs[] = 'Les squelettes n appliquent pas la meme autorisation que l action.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: suppression des transactions protegee par autorisation et API Paiements.\n";
