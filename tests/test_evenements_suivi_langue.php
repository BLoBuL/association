<?php

$racine = dirname(__DIR__);
$attendus = [
    'plugins/association-evenements/prive/objets/liste/table_suivi_activites.html' => ['libelle_informations'],
    'plugins/association-evenements/prive/objets/liste/table_participation_adherent.html' => ['libelle_message'],
    'plugins/association-evenements/prive/objets/liste/item_inscription_adherent.html' => [
        'activite_entete_transaction', 'export_evenements_mode_paiement', 'paiement_en_attente',
    ],
];
foreach ($attendus as $fichier => $cles) {
    $contenu = file_get_contents($racine . '/' . $fichier);
    foreach ($cles as $cle) {
        if (!str_contains($contenu, "association_evenements:$cle")) {
            fwrite(STDERR, "Clé $cle absente de $fichier\n");
            exit(1);
        }
    }
}
$inscription = file_get_contents($racine . '/plugins/association-evenements/prive/objets/liste/item_inscription_adherent.html');
foreach (['title="En attente de paiement"', 'Transaction :', 'Mode :'] as $historique) {
    if (str_contains($inscription, $historique)) {
        fwrite(STDERR, "Libellé historique encore présent : $historique\n");
        exit(1);
    }
}
echo "OK - suivi et paiements Événements traduisibles\n";
