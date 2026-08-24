<?php

$racine = dirname(__DIR__);
$page = file_get_contents($racine . '/plugins/association-paiements/prive/squelettes/contenu/transaction_remboursement.html');
$formulaire = file_get_contents($racine . '/plugins/association-paiements/formulaires/rembourser_transaction.php');
$comptes = file_get_contents($racine . '/plugins/association-compta/prive/squelettes/contenu/comptes.html');

foreach (['#AUTORISER{rembourser,transaction,#ID_TRANSACTION}', 'titre_remboursement_transaction', 'transaction_liee_a'] as $garantie) {
    if (!str_contains($page, $garantie)) {
        fwrite(STDERR, "Garantie $garantie absente de la page de remboursement\n");
        exit(1);
    }
}
if (substr_count($formulaire, 'association_paiements_remboursement_autorise($id_transaction)') < 3) {
    fwrite(STDERR, "L'autorisation n'est pas rejouée aux trois étapes CVT\n");
    exit(1);
}
foreach (['transaction_remboursee', 'erreur_remboursement_impossible'] as $cle) {
    if (!str_contains($formulaire, "association_paiements:$cle")) {
        fwrite(STDERR, "Clé $cle absente du traitement de remboursement\n");
        exit(1);
    }
}
foreach (['inclure_non_validees_oui', 'inclure_non_validees_non'] as $cle) {
    if (!str_contains($comptes, "association_compta:$cle")) {
        fwrite(STDERR, "Clé $cle absente de la page Comptes\n");
        exit(1);
    }
}
echo "OK - remboursement Paiements sécurisé et Comptes traduisible\n";
