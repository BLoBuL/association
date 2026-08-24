<?php

$racine = dirname(__DIR__);
$fichiers = array(
	'plugins/association-paiements/prive/squelettes/contenu/transaction_suppression.html',
	'plugins/association-paiements/prive/squelettes/contenu/transaction_abandon.html',
	'plugins/association-paiements/prive/squelettes/contenu/transaction.html',
	'plugins/association-paiements/prive/objets/liste/transactions.html',
	'plugins/association-paiements/prive/inclure/miniature_transaction.html',
);
$source = '';
foreach ($fichiers as $fichier) $source .= file_get_contents($racine . '/' . $fichier);
$erreurs = array();
foreach (array('Supprimer cette transaction</a>', 'Annuler cette transaction</a>', 'Cet email correspond à l\'auteur', 'Information indisponible</td>', 'Transaction manquante&nbsp;') as $historique) {
	if (strpos($source, $historique) !== false) $erreurs[] = 'Libellé Paiements codé en dur : ' . $historique;
}
foreach (array('bouton_supprimer_transaction', 'bouton_annuler_transaction', 'transaction_manquante_explication', 'adherent_transaction_absent') as $cle) {
	if (strpos($source, 'association_paiements:' . $cle) === false) $erreurs[] = 'Clé Paiements non utilisée : ' . $cle;
}
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK - langue privée Paiements\n";
