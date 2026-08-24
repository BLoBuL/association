<?php

$racine = dirname(__DIR__);
$validation = file_get_contents($racine . '/plugins/association-communication/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_validation.html');
$autres = file_get_contents($racine . '/plugins/association-communication/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_autres.html');

foreach (['notifications_validation_titre', 'notifications_editer'] as $cle) {
    if (!str_contains($validation, "association_communication:$cle")) {
        fwrite(STDERR, "Clé $cle absente des notifications de validation\n");
        exit(1);
    }
}
foreach (['notifications_gis_titre', 'notifications_personnalisations_titre'] as $cle) {
    if (!str_contains($autres, "association_communication:$cle")) {
        fwrite(STDERR, "Clé $cle absente des autres notifications\n");
        exit(1);
    }
}
if (substr_count($validation, 'rel="noopener noreferrer"') !== 2) {
    fwrite(STDERR, "Les liens d'édition externes ne sont pas tous isolés\n");
    exit(1);
}
foreach (['>EDITION<', 'Notifications liées à la création/validation', '<h2>Notifications liées à GIS</h2>', '<h2>Personnalisations</h2>'] as $historique) {
    if (str_contains($validation . $autres, $historique)) {
        fwrite(STDERR, "Libellé historique encore présent : $historique\n");
        exit(1);
    }
}
echo "OK - notifications Communication traduisibles\n";
