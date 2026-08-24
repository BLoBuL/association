<?php

$racine = dirname(__DIR__);
$catalogue = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html');
$hierarchie = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/hierarchie/adherents.html');

if (!str_contains($hierarchie, '<:info_racine_site:>') || str_contains($hierarchie, '>Racine du site<')) {
    fwrite(STDERR, "Le fil d'Ariane Adhérents n'utilise pas le libellé natif SPIP\n");
    exit(1);
}
if (substr_count($catalogue, 'association_communication:notifications_editer') !== 2) {
    fwrite(STDERR, "Les deux actions d'édition ne sont pas traduites par Communication\n");
    exit(1);
}
if (substr_count($catalogue, 'rel="noopener noreferrer"') !== 2) {
    fwrite(STDERR, "Les deux liens d'édition externes ne sont pas isolés\n");
    exit(1);
}
if (substr_count($catalogue, 'association_adhesions:test_notification_argument') !== 2 || str_contains($catalogue, '>EDITION<')) {
    fwrite(STDERR, "Les repères du catalogue Adhésions ne sont pas tous traduisibles\n");
    exit(1);
}
echo "OK - catalogue privé Adhésions normalisé\n";
