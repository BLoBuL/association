<?php

$racine = dirname(__DIR__);
$tableau = file_get_contents($racine . '/plugins/association-groupes/prive/squelettes/contenu/inc-benevoles/table_benevoles.html');
if (!str_contains($tableau, 'association_groupes:autres_administrateurs_redacteurs')) {
    fwrite(STDERR, "Le groupe résiduel des bénévoles n'est pas traduisible\n");
    exit(1);
}
if (str_contains($tableau, 'Autres admins et rédacteurs') || str_contains($tableau, '<!--[(#SET{roles')) {
    fwrite(STDERR, "Un libellé ou code historique subsiste dans le tableau Bénévoles\n");
    exit(1);
}
echo "OK - tableau Bénévoles traduisible\n";
