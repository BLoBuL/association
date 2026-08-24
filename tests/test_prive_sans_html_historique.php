<?php

$racine = dirname(__DIR__);
$fichiers = [
    'plugins/association-evenements/prive/squelettes/contenu/voir_activites.html',
    'plugins/association-paiements/prive/objets/liste/transactions.html',
];
foreach ($fichiers as $fichier) {
    $contenu = file_get_contents($racine . '/' . $fichier);
    if (preg_match('/<!--\s*<(?:li|th)\b/s', $contenu)) {
        fwrite(STDERR, "Bloc d'interface HTML désactivé encore présent dans $fichier\n");
        exit(1);
    }
}
echo "OK - interfaces privées sans blocs HTML historiques\n";
