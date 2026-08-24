<?php

$racine = dirname(__DIR__);
$fichiers = [
    'plugins/association-evenements/prive/objets/liste/table_comptabilite_activites.html',
    'plugins/association-evenements/prive/squelettes/contenu/analyse_compta_activites.html',
];

foreach ($fichiers as $fichier) {
    $contenu = file_get_contents($racine . '/' . $fichier);
    if (str_contains($contenu, 'Inclure non validées')) {
        fwrite(STDERR, "Libellé comptable codé en dur dans $fichier\n");
        exit(1);
    }
    foreach (['inclure_non_validees_oui', 'inclure_non_validees_non'] as $cle) {
        if (!str_contains($contenu, "association_compta:$cle")) {
            fwrite(STDERR, "Clé $cle absente de $fichier\n");
            exit(1);
        }
    }
}

$fonctions = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/analyse_compta_activites_fonctions.php');
foreach (['filtre_recettes_uniquement', 'filtre_depenses_uniquement', 'filtre_toutes_operations'] as $cle) {
    if (!str_contains($fonctions, "_T('association_compta:$cle')")) {
        fwrite(STDERR, "Traduction PHP $cle absente\n");
        exit(1);
    }
}

$lightbox = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/lightbox_lien_inscription_vip.html');
foreach (['onclick=', 'myFunction', 'alert('] as $antiPattern) {
    if (str_contains($lightbox, $antiPattern)) {
        fwrite(STDERR, "JavaScript historique $antiPattern encore présent\n");
        exit(1);
    }
}
foreach (['copier_lien_inscription', 'lien_inscription_copie'] as $cle) {
    if (!str_contains($lightbox, "association_evenements:$cle")) {
        fwrite(STDERR, "Clé $cle absente de la lightbox\n");
        exit(1);
    }
}

echo "OK - comptabilité Événements et lightbox VIP traduisibles\n";
