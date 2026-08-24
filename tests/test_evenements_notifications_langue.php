<?php

$racine = dirname(__DIR__);
$attendus = [
    'plugins/association-evenements/prive/inclure/miniature_evenement.html' => ['evenement_important', 'evenement_repete'],
    'plugins/association-evenements/prive/objets/liste/item_export_activites.html' => ['activite_nombre_inscrits'],
    'plugins/association-evenements/prive/objets/liste/table_destinataires_email_collectif_activite.html' => ['tous_statuts'],
    'plugins/association-evenements/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html' => [
        'notifications_activites_titre', 'notification_filtre_inscription_individuelle',
        'notification_filtre_inscription_multiple', 'notification_filtre_gratuit',
        'notification_filtre_payant', 'notifications_activites_preinscrits',
        'notifications_activites_inscrits', 'notifications_activites_attente',
        'notifications_activites_desinscrits', 'notifications_activites_aucune',
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
$notifications = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html');
if (substr_count($notifications, '#SET{notif_activite_found,1}') !== 4 || !str_contains($notifications, '#SET{notif_activite_found,0}')) {
    fwrite(STDERR, "Le témoin de présence des notifications n'est pas alimenté par les quatre listes\n");
    exit(1);
}
foreach (['#Inscription_individuel', '#Inscription_multiple', '>#gratuit<', '>#payant<', 'Notifications liées aux'] as $historique) {
    if (str_contains($notifications, $historique)) {
        fwrite(STDERR, "Libellé historique encore présent : $historique\n");
        exit(1);
    }
}
echo "OK - notifications et repères Événements traduisibles\n";
