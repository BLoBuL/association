<?php

define('_ECRIRE_INC_VERSION', 1);
require_once dirname(__DIR__) . '/base/association.php';

$saisies = array(
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'entreprise',
            'afficher_si' => '@type@=="entreprise"',
            'explication' => '[Consulter le document->doc 2381]',
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

if (($resultat[0]['options']['explication'] ?? '') !== '[Consulter le document->doc2381]') {
    fwrite(STDERR, "Les anciens raccourcis doc avec espace doivent être normalisés.\n");
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

function include_spip($fichier) {
}

function saisies_lister_par_nom($saisies) {
    $resultat = array();
    foreach ($saisies as $saisie) {
        if (!empty($saisie['options']['nom'])) {
            $resultat[$saisie['options']['nom']] = $saisie;
        }
        if (!empty($saisie['saisies'])) {
            $resultat += saisies_lister_par_nom($saisie['saisies']);
        }
    }
    return $resultat;
}

require_once dirname(__DIR__) . '/association_pipelines.php';

$vue_partielle = array($saisies[0]['saisies'][0]);
$vue_partielle = association_saisies_afficher_si_saisies($vue_partielle);
if (isset($vue_partielle[0]['options']['afficher_si'])) {
    fwrite(STDERR, "Une condition dont le champ pilote est absent doit être neutralisée.\n");
    exit(1);
}

$formulaire_complet = array(
    array('saisie' => 'radio', 'options' => array('nom' => 'type')),
    $saisies[0]['saisies'][0],
);
$formulaire_complet = association_saisies_afficher_si_saisies($formulaire_complet);
if (($formulaire_complet[1]['options']['afficher_si'] ?? '') !== '@type@=="entreprise"') {
    fwrite(STDERR, "Une condition dont le champ pilote est présent doit être conservée.\n");
    exit(1);
}

echo "OK - conditions orphelines des vues partielles neutralisées\n";
