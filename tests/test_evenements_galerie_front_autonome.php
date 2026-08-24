<?php

$racine = dirname(__DIR__);
$verrouillee = file_get_contents($racine . '/plugins/association-evenements/squelettes/inclure/album_photos_evenement_locked.html');
$ouverte = file_get_contents($racine . '/plugins/association-evenements/squelettes/inclure/album_photos_evenement.html');

foreach (['onclick=', 'jQuery.modalbox', 'target="_blank"'] as $historique) {
    if (str_contains($verrouillee, $historique)) {
        fwrite(STDERR, "Dépendance front historique encore présente : $historique\n");
        exit(1);
    }
}
if (substr_count($verrouillee, 'class="login-galerie"') !== 2 || substr_count($verrouillee, 'parametre_url{url,#SELF}') !== 2) {
    fwrite(STDERR, "Les deux liens de la galerie verrouillée ne préservent pas le retour SPIP\n");
    exit(1);
}
if (substr_count($ouverte, 'class="mediabox"') < 2) {
    fwrite(STDERR, "La galerie ouverte n'active pas explicitement Mediabox\n");
    exit(1);
}
echo "OK - galeries Événements autonomes en front office\n";
