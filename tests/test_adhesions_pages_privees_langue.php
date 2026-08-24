<?php

$racine = dirname(__DIR__);
$miniature = file_get_contents($racine . '/plugins/association-adhesions/prive/inclure/miniature_adherent.html');
$destinataire = file_get_contents($racine . '/plugins/association-adhesions/prive/objets/liste/item_destinataire_email_collectif.html');
$suppression = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/cotisation_suppression.html');
if (!str_contains($miniature, 'association_adhesions:adherent_supprime')) {
    fwrite(STDERR, "La miniature d'un adhérent supprimé n'est pas traduisible\n");
    exit(1);
}
foreach (['libelle_transaction', 'libelle_mode_paiement', 'paiement_en_attente'] as $cle) {
    if (!str_contains($destinataire, "association_adhesions:$cle")) {
        fwrite(STDERR, "Clé $cle absente du destinataire collectif\n");
        exit(1);
    }
}
if (!str_contains($destinataire, 'rel="noopener noreferrer"')) {
    fwrite(STDERR, "Le lien de paiement externe n'est pas isolé\n");
    exit(1);
}
foreach (['#AUTORISER{modifier,asso_compte,#ENV{id_compte}}', 'cotisation_suppression_titre', 'cotisation_liee_a'] as $garantie) {
    if (!str_contains($suppression, $garantie)) {
        fwrite(STDERR, "Garantie $garantie absente de la page de suppression\n");
        exit(1);
    }
}
if (str_contains($suppression, '<!--[(#AUTORISER')) {
    fwrite(STDERR, "Le garde d'autorisation est encore commenté\n");
    exit(1);
}
echo "OK - pages privées Adhésions traduisibles et protégées\n";
