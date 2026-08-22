<?php

define('_ECRIRE_INC_VERSION', 1);
require_once dirname(__DIR__) . '/base/association.php';

$saisies = array(
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'entreprise',
            'afficher_si' => '@type@=="entreprise"',
        ),
        'saisies' => array(
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'raison_sociale',
                    'afficher_si' => '@type@=="entreprise"',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'forme_juridique',
                    'afficher_si' => '@pays@=="fr"',
                ),
            ),
        ),
    ),
);

$resultat = association_champs_extras_dedoublonner_afficher_si($saisies);

if (($resultat[0]['options']['afficher_si'] ?? '') !== '@type@=="entreprise"') {
    fwrite(STDERR, "La condition du fieldset doit être conservée.\n");
    exit(1);
}

if (isset($resultat[0]['saisies'][0]['options']['afficher_si'])) {
    fwrite(STDERR, "La condition enfant redondante doit être supprimée.\n");
    exit(1);
}

if (($resultat[0]['saisies'][1]['options']['afficher_si'] ?? '') !== '@pays@=="fr"') {
    fwrite(STDERR, "Une condition enfant distincte doit être conservée.\n");
    exit(1);
}

echo "OK - conditions afficher_si imbriquées normalisées\n";
