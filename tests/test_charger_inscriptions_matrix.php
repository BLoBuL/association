<?php
require_once __DIR__ . '/inc/bootstrap_charger_inscriptions.php';

function association_tester_charger_formulaires_evenement() {
    association_test_charger_bootstrap();

    $tarifs = array(
        array('id_categorie' => 11, 'titre' => 'Adulte', 'montant_symbole' => '12 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
        array('id_categorie' => 12, 'titre' => 'Enfant', 'montant_symbole' => '8 EUR', 'quantite' => 1, 'type_inscrit' => 'indifferent'),
    );

    $scenarios = array(
        array(
            'code' => 'fo_simple_gratuit_sans_accompagnants',
            'description' => 'FO simple gratuit sans accompagnants',
            'callable' => 'formulaires_inscription_evenement_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => false)),
            'request' => array('select_type_inscrit' => 'public'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit', 'nb_inscrits', 'commentaire'),
                'not_contains' => array('categorie', 'famille', 'annotation', 'nom_participants'),
            ),
        ),
        array(
            'code' => 'fo_simple_gratuit_avec_accompagnants',
            'description' => 'FO simple gratuit avec accompagnants',
            'callable' => 'formulaires_inscription_evenement_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true)),
            'request' => array('select_type_inscrit' => 'public'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit', 'nb_inscrits', 'nom_participants', 'commentaire'),
                'not_contains' => array('categorie', 'famille', 'annotation'),
            ),
        ),
        array(
            'code' => 'fo_simple_payant_sans_accompagnants',
            'description' => 'FO simple payant sans accompagnants',
            'callable' => 'formulaires_inscription_evenement_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs)),
            'request' => array('select_type_inscrit' => 'public'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit', 'categorie', 'commentaire'),
                'not_contains' => array('famille', 'annotation', 'nom_participants'),
            ),
        ),
        array(
            'code' => 'fo_simple_payant_avec_accompagnants',
            'description' => 'FO simple payant avec accompagnants',
            'callable' => 'formulaires_inscription_evenement_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs)),
            'request' => array('select_type_inscrit' => 'public'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit', 'categorie[11]', 'categorie[12]', 'nom_participants', 'commentaire'),
                'not_contains' => array('famille', 'annotation'),
            ),
        ),
        array(
            'code' => 'fo_simple_gratuit_famille_connecte',
            'description' => 'FO simple gratuit famille active',
            'callable' => 'formulaires_inscription_evenement_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true)),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok', 'validite' => '2027-12-31 23:59:59', 'radio_type_adherent' => ''),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('nb_inscrits', 'famille', 'commentaire'),
                'not_contains' => array('nom_participants', 'annotation'),
            ),
        ),
        array(
            'code' => 'bo_simple_creation_payant',
            'description' => 'BO simple creation payante',
            'callable' => 'formulaires_inscription_evenement_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => false, 'montant' => $tarifs)),
            'request' => array('select_type_inscrit' => 'membre', 'membre' => 1),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo', 'validite' => '2027-12-31 23:59:59'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'contains' => array('select_type_inscrit', 'membre', 'non_membre', 'categorie', 'annotation', 'commentaire'),
            ),
        ),
        array(
            'code' => 'bo_simple_modification_gratuite_famille',
            'description' => 'BO simple modification gratuite avec famille',
            'callable' => 'formulaires_inscription_evenement_charger_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true), 'eligibilite_modification_evenement' => 'possible'),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'request' => array('modif' => 'oui', 'select_type_inscrit' => 'membre', 'membre' => 1),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo', 'validite' => '2027-12-31 23:59:59'),
            'args' => array(1, 501),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'non',
                'modification_inscription' => 'oui',
                'contains' => array('select_type_inscrit', 'membre', 'non_membre', 'nb_inscrits', 'famille', 'id_activite', 'annotation'),
            ),
        ),
        array(
            'code' => 'bo_multi_creation_payant_public',
            'description' => 'BO multi creation payante pour public',
            'callable' => 'formulaires_inscription_evenement_multi_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs)),
            'request' => array('select_type_inscrit' => 'public', 'nb_inscrits' => 2),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo', 'validite' => '2027-12-31 23:59:59'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'multi' => true,
                'contains' => array('fieldset_infos_generales', 'prenom_inscrit_1', 'nom_inscrit_1', 'prenom_inscrit_2', 'nom_inscrit_2', 'categorie[11]', 'categorie[12]', 'fieldset_modalites'),
            ),
        ),
        array(
            'code' => 'bo_multi_creation_gratuite_membre_deux',
            'description' => 'BO multi CFG-01 membre et accompagnant libre',
            'callable' => 'formulaires_inscription_evenement_multi_charger_dist',
            'fixture' => array('affichage' => array('payant' => false, 'accompagnants' => true)),
            'request' => array('select_type_inscrit' => 'membre', 'membre' => 1, 'nb_inscrits' => 2),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo', 'validite' => '2027-12-31 23:59:59'),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'multi' => true,
                'contains' => array('prenom_inscrit_1', 'nom_inscrit_1', 'prenom_inscrit_2', 'nom_inscrit_2'),
            ),
        ),
        array(
            'code' => 'bo_multi_modification_payante_famille',
            'description' => 'BO multi modification payante famille active',
            'callable' => 'formulaires_inscription_evenement_multi_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs), 'eligibilite_modification_evenement' => 'possible'),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'request' => array('modif' => 'oui', 'select_type_inscrit' => 'membre', 'membre' => 1),
            'session' => array('id_auteur' => 999, 'statut' => '0minirezo', 'validite' => '2027-12-31 23:59:59'),
            'args' => array(1, 601),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'non',
                'modification_inscription' => 'oui',
                'multi' => true,
                'contains' => array('fieldset_infos_generales', 'prenom_adherent', 'nom_adherent', 'prenom_conjoint', 'nom_conjoint', 'categorie_adherent', 'categorie_conjoint', 'fieldset_modalites', 'id_activite'),
            ),
        ),
        array(
            'code' => 'fo_multi_public_payant_anonyme',
            'description' => 'FO multi public payant anonyme avec accompagnants',
            'callable' => 'formulaires_inscription_evenement_multi_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs)),
            'request' => array('nb_inscrits' => 2),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'multi' => true,
                'contains' => array('fieldset_nb_inscrit', 'nb_inscrits', 'fieldset_info_supplementaire', 'prenom_inscrit_1', 'nom_inscrit_1', 'fieldset_modalites'),
            ),
        ),
        array(
            'code' => 'fo_multi_public_payant_famille',
            'description' => 'FO multi public payant famille active',
            'callable' => 'formulaires_inscription_evenement_multi_public_charger_dist',
            'fixture' => array('affichage' => array('payant' => true, 'accompagnants' => true, 'montant' => $tarifs)),
            'metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
            'request' => array('famille' => array('adherent', 'conjoint')),
            'session' => array('id_auteur' => 1, 'statut' => '1comite', 'statut_interne' => 'ok', 'validite' => '2027-12-31 23:59:59', 'radio_type_adherent' => ''),
            'expected' => array(
                'editable' => true,
                'creation_inscription' => 'oui',
                'modification_inscription' => 'non',
                'multi' => true,
                'contains' => array('fieldset_selection_membre_famille', 'famille', 'fieldset_info_supplementaire_famille', 'prenom_adherent', 'nom_adherent', 'fieldset_modalites'),
            ),
        ),
    );

    $rapport = array(
        'succes' => 0,
        'echecs' => 0,
        'scenarios' => array(),
    );

    foreach ($scenarios as $scenario) {
        association_test_charger_appliquer_fixture($scenario);
        $args = $scenario['args'] ?? array(1, null);
        $callable = $scenario['callable'];
        $resultat = call_user_func_array($callable, $args);
        list($erreurs, $noms) = association_test_charger_assertions($scenario, $resultat);

        $ok = empty($erreurs);
        $rapport['scenarios'][] = array(
            'code' => $scenario['code'],
            'description' => $scenario['description'],
            'ok' => $ok,
            'erreurs' => $erreurs,
            'noms' => $noms,
            'creation_inscription' => $resultat['creation_inscription'] ?? null,
            'modification_inscription' => $resultat['modification_inscription'] ?? null,
            'ie_mode' => $resultat['ie_mode'] ?? null,
        );

        if ($ok) {
            $rapport['succes']++;
        } else {
            $rapport['echecs']++;
        }
    }

    return $rapport;
}

function association_test_charger_afficher_rapport(array $rapport) {
    echo "Matrice de tests charger inscriptions evenement\n";
    echo str_repeat('=', 44) . "\n";

    foreach ($rapport['scenarios'] as $scenario) {
        echo ($scenario['ok'] ? '[OK] ' : '[KO] ') . $scenario['code'] . ' - ' . $scenario['description'] . "\n";
        echo '  mode=' . $scenario['ie_mode']
            . ' creation=' . $scenario['creation_inscription']
            . ' modification=' . $scenario['modification_inscription'] . "\n";

        if (!$scenario['ok']) {
            foreach ($scenario['erreurs'] as $erreur) {
                echo '  - ' . $erreur . "\n";
            }
            echo '  champs extraits: ' . implode(', ', $scenario['noms']) . "\n";
        }
    }

    echo str_repeat('-', 44) . "\n";
    echo 'Succes: ' . intval($rapport['succes']) . "\n";
    echo 'Echecs: ' . intval($rapport['echecs']) . "\n";
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $rapport = association_tester_charger_formulaires_evenement();
    association_test_charger_afficher_rapport($rapport);
    exit($rapport['echecs'] > 0 ? 1 : 0);
}


