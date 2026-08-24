<?php

$racine = dirname(__DIR__);
$php = file_get_contents($racine . '/plugins/association-compta/formulaires/editer_asso_comptes.php');
$page = file_get_contents($racine . '/plugins/association-compta/prive/squelettes/contenu/editer_asso_comptes.html');
$langue = file_get_contents($racine . '/plugins/association-compta/lang/association_compta_fr.php');

if (str_contains($php, "_T('asso_compte:")) {
    fwrite(STDERR, "Le formulaire utilise encore le domaine de langue inexistant asso_compte\n");
    exit(1);
}
if (!str_contains($php, "_T('association_compta:operation_enregistree')") || !str_contains($langue, "'operation_enregistree'")) {
    fwrite(STDERR, "Le message de succès Comptabilité n'est pas correctement déclaré\n");
    exit(1);
}
foreach (['form_operation_modification_title', 'form_operation_ajout_title'] as $cle) {
    if (!str_contains($page, "association_compta:$cle")) {
        fwrite(STDERR, "Titre $cle absent de la page d'édition\n");
        exit(1);
    }
}
echo "OK - formulaire Comptabilité dans son domaine de langue\n";
