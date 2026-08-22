<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

include_spip('formulaires/inscription_evenement.php');
include_spip('formulaires/inscription_evenement_multi.php');
include_spip('formulaires/inscription_evenement_public.php');
include_spip('formulaires/inscription_evenement_multi_public.php');

function association_test_run_verifier_suite() {
    return association_test_run_cases('verifier-evenements', array(
        'message_erreur_saisies_normalise_en_texte' => function () {
            association_test_assert_same(
                'Votre choix dépasse le nombre de place disponible !',
                ie_message_erreur_texte('<span role="alert">Votre choix dépasse le nombre de place disponible !</span>'),
                'Une enveloppe HTML Saisies doit etre retiree du bandeau global'
            );
            association_test_assert_same(
                'Vous devez sélectionner au moins une catégorie !',
                ie_message_erreur_texte('&lt;span role=&#039;alert&#039;&gt;Vous devez sélectionner au moins une catégorie !&lt;/span&gt;'),
                'Une enveloppe HTML Saisies encodee doit etre retiree du bandeau global'
            );
            association_test_assert_same(
                array(
                    'nb_accompagnants' => 'Votre choix dépasse le nombre de place disponible !',
                    'categorie' => 'Vous devez sélectionner au moins une catégorie !',
                ),
                ie_normaliser_erreur_affichage(array(
                    'nb_accompagnants' => '<span role="alert">Votre choix dépasse le nombre de place disponible !</span>',
                    'categorie' => '&lt;span role=&#039;alert&#039;&gt;Vous devez sélectionner au moins une catégorie !&lt;/span&gt;',
                )),
                'Les erreurs rattachees aux champs doivent aussi etre rendues sans balises litterales'
            );
            association_test_assert_same(
                'QVID-ND02-NR01-NV02-PL05-PD010-AE005',
                ie_diagnostic_validation_code(array(
                    'motifs' => array('ID'),
                    'quantite_demandee' => 2,
                    'participants_reels' => 1,
                    'nombre_a_verifier' => 2,
                    'places_limites' => 5,
                    'places_disponibles' => 10,
                    'places_attente' => 5,
                )),
                'Le diagnostic doit distinguer une identite manquante d un quota plein'
            );
        },
        'prive_simple_membre_manquant' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'categorie' => 3,
            ));

            $erreurs = formulaires_inscription_evenement_verifier_dist();
            association_test_assert_true(isset($erreurs['membre']), 'Le prive simple doit exiger un membre');
        },
        'prive_simple_nom_participants_obligatoire' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => true,
                    ),
                ),
                'places' => array(
                    1 => array(
                        'places_limites' => 5,
                        'places_disponibles' => 1,
                        'places_evenement' => 5,
                        'places_en_attentes_disponible' => 0,
                        'evenement_date_debut' => '2026-12-25 10:00:00',
                        'evenement_reseau_fiafe' => 'non',
                    ),
                ),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'email_inscrit' => 'paul@example.test',
                'nb_inscrits' => 3,
            ));

            $erreurs = formulaires_inscription_evenement_verifier_dist();
            association_test_assert_true(isset($erreurs['nom_participants']), 'Les noms des accompagnants doivent etre exiges');
        },
        'prive_multi_etape_selection_adherent_autorisee' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => true,
                    ),
                ),
                'places' => array(
                    1 => array(
                        'places_limites' => 5,
                        'places_disponibles' => 9,
                        'places_evenement' => 12,
                        'places_en_attentes_disponible' => 5,
                    ),
                ),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 1,
                // Les afficher_si de Saisies peuvent laisser ces champs
                // techniques dans le POST bien qu'ils soient masqués.
                'prenom_inscrit' => '',
                'nom_inscrit' => '',
                'email_inscrit' => '',
                'tel_inscrit' => '',
            ));

            $erreurs = ie_verifier_commons('multi_prive', 1, null, null, 1);

            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'Le BO multi ne doit pas exiger le nom avant l etape participant');
            association_test_assert_true(empty($erreurs['message_erreur']), 'La selection d un adherent et d une place disponible doit passer l etape 1');
        },
        'prive_multi_payant_tarif_etape_suivante_autorise' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => true, 'accompagnants' => true)),
                'places' => array(
                    1 => array(
                        'places_limites' => 5,
                        'places_disponibles' => 7,
                        'places_evenement' => 10,
                        'places_en_attentes_disponible' => 3,
                    ),
                ),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'prenom_inscrit' => '',
                'nom_inscrit' => '',
                'email_inscrit' => '',
                'tel_inscrit' => '',
            ));

            $erreurs = ie_verifier_commons('multi_prive', 1, null, null, 1);

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Le tarif de l etape suivante ne doit pas produire une inscription a zero a l etape 1');
            association_test_assert_true(!isset($erreurs['categorie']), 'La categorie tarifaire ne doit etre controlee qu a son etape');
            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'Les champs masques vides ne doivent pas declencher le controle d identite a l etape 1');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le BO multi payant doit atteindre l etape tarif');
        },
        'prive_multi_categorie_courante_avec_compteur_etape_precedente' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => true,
                    'accompagnants' => true,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 5,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 0,
                )),
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'cvtm_prev_post' => array(
                    'select_type_inscrit' => 'membre',
                    'membre' => 1,
                    'nb_inscrits' => 1,
                ),
                'categorie_inscrit_1' => 1,
            ));

            $erreurs = ie_verifier_commons('multi_prive', 1, null, null, 2);

            association_test_assert_true(!isset($erreurs['categorie']), 'La categorie choisie a l etape courante doit etre conservee');
            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Une place demandee sur cinq disponibles ne doit pas produire une erreur de quota');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le BO multi synthetique complet doit atteindre le recapitulatif');
        },
        'prive_multi_selection_adherent_obligatoire' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => false, 'accompagnants' => true)),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'nb_inscrits' => 1,
            ));

            $erreurs = ie_verifier_commons('multi_prive', 1, null, null, 1);

            association_test_assert_true(isset($erreurs['membre']), 'Le BO multi doit conserver l obligation de selectionner un adherent');
        },
        'public_simple_email_invalide' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'email_inscrit' => 'not-an-email',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'categorie' => 3,
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);
            association_test_assert_true(isset($erreurs['email_inscrit']), 'Le public simple doit refuser un email invalide');
        },
        'prive_simple_payant_sans_tarif_refuse' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_verifier_dist(1);

            association_test_assert_true(isset($erreurs['categorie']), 'Le BO simple payant doit refuser un tarif absent');
        },
        'prive_simple_payant_accompagnants_sans_tarif_sans_faux_quota' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => true, 'accompagnants' => true, 'places' => true)),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => array(3 => ''),
            ));

            $erreurs = formulaires_inscription_evenement_verifier_dist(1);

            association_test_assert_true(isset($erreurs['categorie']), 'Le BO simple avec accompagnants doit demander un tarif');
            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'L absence de tarif ne doit pas etre presentee comme un depassement de quota');
        },
        'public_simple_payant_sans_tarif_refuse_anonyme_et_connecte' => function () {
            foreach (array('anonyme' => array(), 'connecte' => array('id_auteur' => 1)) as $contexte => $session) {
                association_test_reset_env(array(
                    'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
                    'visiteur_session' => $session,
                ));
                association_test_set_request(array(
                    'prenom_inscrit' => 'Nina',
                    'nom_inscrit' => 'Sans Tarif',
                    'email_inscrit' => 'nina-' . $contexte . '@example.test',
                ));

                $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);
                association_test_assert_true(isset($erreurs['categorie']), 'Le FO ' . $contexte . ' doit refuser un tarif absent');
            }
        },
        'public_simple_payant_tarif_invalide_ou_falsifie_refuse' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Nina',
                'nom_inscrit' => 'Tarif Falsifie',
                'email_inscrit' => 'nina-falsifie@example.test',
                'categorie' => 999,
                'montant_total' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);

            association_test_assert_true(isset($erreurs['categorie']), 'Un tarif non lie a l evenement doit etre refuse');
        },
        'public_simple_payant_tarif_zero_valide' => function () {
            association_test_reset_env(array(
                'db' => array(
                    'spip_asso_categories_activites' => array(2 => array(
                        'montant' => 0,
                        'montant_symbole' => '0 EUR',
                        'type_inscrit' => 'indifferent',
                        'statut' => 'ok',
                    )),
                    'spip_asso_categories_activites_liens' => array(1 => array('montant' => 0)),
                ),
                'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Nina',
                'nom_inscrit' => 'Tarif Zero',
                'email_inscrit' => 'nina-zero@example.test',
                'categorie' => 2,
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);

            association_test_assert_true(!isset($erreurs['categorie']), 'Un tarif actif a zero euro reste un tarif valide');
        },
        'public_simple_tarif_adherent_forge_refuse' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'email_inscrit' => 'public@example.test',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'categorie' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);
            association_test_assert_true(isset($erreurs['categorie']), 'Un public anonyme ne doit pas pouvoir forger un tarif adherent');
        },
        'prive_simple_public_ignore_statut_operateur' => function () {
            association_test_reset_env(array(
                'db' => array(
                    'spip_asso_categories_activites' => array(5 => array(
                        'id_categorie' => 5,
                        'montant' => 10,
                        'type_inscrit' => 'non_adherent',
                        'statut' => 'ok',
                    )),
                    'spip_asso_categories_activites_liens' => array(
                        array('id_evenement' => 1, 'id_categorie' => 5, 'montant' => 10),
                    ),
                ),
            ));
            $erreurs = ie_verifier_tarifs_compatibles(
                1,
                'multi_prive',
                array(
                    'categorie' => array(5 => 1),
                    'cvtm_prev_post' => array('select_type_inscrit' => 'public'),
                ),
                array(
                    'id_auteur' => 1,
                    'array_post_inscrits' => array('inscrit_1' => array('prenom' => 'Recette')),
                    'categorie_result' => array(5 => 1),
                )
            );

            association_test_assert_same(array(), $erreurs, 'Le statut du BO ne doit pas invalider le tarif non-membre saisi pour une personne sans compte');
        },
        'public_simple_tarif_non_lie_refuse' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'email_inscrit' => 'public@example.test',
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'categorie' => 999,
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);
            association_test_assert_true(isset($erreurs['categorie']), 'Un tarif non lie a l evenement doit etre refuse');
        },
        'public_multi_tarif_couple_famille_accepte' => function () {
            association_test_reset_env(array(
                'visiteur_session' => association_test_default_scenario()['db']['spip_auteurs'][1],
            ));
            association_test_set_request(array(
                'famille' => array('adherent', 'conjoint'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'categorie_adherent' => 4,
                'prenom_conjoint' => 'Marie',
                'nom_conjoint' => 'Dupont',
                'email_conjoint' => 'marie@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');
            association_test_assert_true(!isset($erreurs['categorie_adherent']), 'Le tarif couple doit etre accepte pour adherent et conjoint');
        },
        'public_multi_tarif_couple_enfant_refuse' => function () {
            association_test_reset_env(array(
                'visiteur_session' => association_test_default_scenario()['db']['spip_auteurs'][1],
            ));
            association_test_set_request(array(
                'famille' => array('adherent', 'enfant_1'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'categorie_adherent' => 1,
                'prenom_enfant_1' => 'Alice',
                'nom_enfant_1' => 'Dupont',
                'categorie_enfant_1' => 4,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');
            association_test_assert_true(isset($erreurs['categorie_enfant_1']), 'Un enfant ne doit pas pouvoir forger un tarif couple');
        },
        'public_simple_vide_refuse' => function () {
            association_test_reset_env();
            association_test_set_request(array());

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);

            association_test_assert_true(!empty($erreurs['message_erreur']), 'Une inscription vide doit etre bloquee');
            association_test_assert_true(isset($erreurs['nom_inscrit']), 'Une inscription sans nom doit etre refusee');
        },
        'public_multi_nom_premier_inscrit_obligatoire' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'email_inscrit_1' => 'nina@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(isset($erreurs['nom_inscrit_1']), 'Le premier participant multi doit avoir un nom');
        },
        'public_multi_etape_nombre_participants_autorisee' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'nb_inscrits' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'L etape nombre de participants ne doit pas exiger un champ de l etape suivante');
            association_test_assert_true(!isset($erreurs['categorie']), 'L etape nombre de participants ne doit pas exiger une categorie de l etape suivante');
            association_test_assert_true(empty($erreurs['message_erreur']), 'L etape nombre de participants valide doit permettre de continuer');
        },
        'public_multi_etape_nombre_matrice_evenements' => function () {
            $configurations = array(
                'gratuit_avec_accompagnants_anonyme' => array(false, true, array()),
                'payant_avec_accompagnants_anonyme' => array(true, true, array()),
                'gratuit_avec_accompagnants_connecte' => array(false, true, array('id_auteur' => 1, 'radio_type_adherent' => 'famille')),
                'payant_avec_accompagnants_connecte' => array(true, true, array('id_auteur' => 1, 'radio_type_adherent' => 'famille')),
            );

            foreach ($configurations as $libelle => $configuration) {
                association_test_reset_env(array(
                    'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                    'visiteur_session' => $configuration[2],
                    'events' => array(1 => array(
                        'payant' => $configuration[0],
                        'accompagnants' => $configuration[1],
                    )),
                ));
                association_test_set_request(array('nb_inscrits' => 1));

                $erreurs = formulaires_inscription_evenement_multi_public_verifier_1_dist(1, '');

                association_test_assert_true(empty($erreurs['message_erreur']), $libelle . ' doit franchir l etape nombre');
                association_test_assert_true(!isset($erreurs['nom_inscrit_1']), $libelle . ' ne doit pas valider le nom masque');
                association_test_assert_true(!isset($erreurs['categorie']), $libelle . ' ne doit pas valider la categorie masquee');
            }
        },
        'public_multi_etape_identite_controles_conserves' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
            ));
            association_test_set_request(array(
                'cvtm_prev_post' => base64_encode(serialize(array('nb_inscrits' => 1))),
                'prenom_inscrit_1' => 'Nina',
                'email_inscrit_1' => 'nina@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_2_dist(1, '');

            association_test_assert_true(isset($erreurs['nom_inscrit_1']), 'L etape identite doit toujours exiger le nom');
            association_test_assert_true(isset($erreurs['categorie']), 'L etape identite payante doit toujours exiger une categorie');
        },
        'public_multi_sans_limitation_places_accepte' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => false,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 0,
                    'places_evenement' => 1,
                    'places_en_attentes_disponible' => 0,
                )),
            ));
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Sans Limite',
                'email_inscrit_1' => 'nina-sans-limite@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Un evenement sans gestion des places ne doit pas etre bloque par une disponibilite a zero');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Une inscription valide sans limitation de places doit passer');
        },
        'public_multi_avec_limitation_places_refuse' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 0,
                    'places_evenement' => 1,
                    'places_en_attentes_disponible' => 0,
                )),
            ));
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Quota Actif',
                'email_inscrit_1' => 'nina-quota@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_inscrits']), 'Un evenement complet avec gestion des places doit rester bloque');
            association_test_assert_true(!empty($erreurs['message_erreur']), 'Le blocage de capacite doit rester visible');
        },
        'public_multi_hors_quota_illimite_accepte' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                    'attentes_illimite' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 0,
                    'places_evenement' => 1,
                    'places_en_attentes_disponible' => 0,
                )),
            ));
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Liste Attente',
                'email_inscrit_1' => 'nina-attente@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');
            $statut = activite_enregistrement_calculator(1, 1);

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Une attente hors quota illimitee ne doit pas bloquer le formulaire');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Une inscription hors quota autorisee doit passer la verification');
            association_test_assert_same('liste_attente', $statut['statut'], 'Une inscription hors quota doit conserver le statut liste d attente');
        },
        'public_multi_recapitulatif_conserve_nombre_inscrits' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 9,
                    'places_evenement' => 12,
                    'places_en_attentes_disponible' => 10,
                )),
            ));
            association_test_set_request(array(
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'nb_inscrits' => 1,
                    'prenom_inscrit_1' => 'Nina',
                    'nom_inscrit_1' => 'Recapitulatif',
                    'email_inscrit_1' => 'nina-recap@example.test',
                    'condition_inscription' => 'on',
                ))),
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Le recapitulatif doit relire le nombre des etapes precedentes');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Une place demandee pour neuf places disponibles doit etre validee');
        },
        'public_multi_etapes_precedentes_conservees' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
            ));
            association_test_set_request(array(
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'nb_inscrits' => 1,
                    'prenom_inscrit_1' => 'Nina',
                    'nom_inscrit_1' => 'Parcours',
                    'email_inscrit_1' => 'nina-parcours@example.test',
                    'telephone_inscrit_1' => '0600000000',
                    'categorie_inscrit_1' => 3,
                ))),
                'condition_inscription' => 'on',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_3_dist(1, '');

            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'Le nom de l etape precedente doit etre conserve');
            association_test_assert_true(!isset($erreurs['categorie']), 'La categorie de l etape precedente doit etre conservee');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Les etapes precedentes completes doivent permettre la validation des modalites');
        },
        'public_multi_famille_selection_sans_identite_cachee' => function () {
            association_test_reset_env(array(
                'visiteur_session' => array('id_auteur' => 1, 'radio_type_adherent' => 'famille'),
            ));
            association_test_set_request(array(
                'famille' => array('adherent'),
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_1_dist(1, '');

            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'La selection famille ne doit pas exiger une identite numerotee masquee');
            association_test_assert_true(!isset($erreurs['categorie']), 'La selection famille ne doit pas exiger une categorie d une etape suivante');
        },
        'public_multi_premier_inscrit_sans_compteur' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => false,
                    ),
                ),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit_1' => 'Test',
                'nom_inscrit_1' => 'Victorinox',
                'email_inscrit_1' => 'test-victorinox@example.test',
                'telephone_inscrit_1' => '0000000000',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nom_inscrit_1']), 'Le nom du premier participant doit etre lu meme sans nb_inscrits poste');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Une identite synthetique complete ne doit pas produire une erreur globale');
        },
        'public_multi_famille_un_invite_dans_limite_totale' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'invites' => true,
                    'limite_invites' => 5,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 2,
                    'places_disponibles' => 10,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 0,
                )),
                'visiteur_session' => array('id_auteur' => 1, 'radio_type_adherent' => 'famille'),
            ));
            association_test_set_request(array(
                'famille' => array('adherent'),
                'nb_invite' => 1,
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'prenom_inscrit_1' => 'Invite',
                'nom_inscrit_1' => 'Autorise',
                'email_inscrit_1' => 'invite-autorise@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['famille']), 'Le principal et son invite ne doivent pas etre comptes trois fois');
            association_test_assert_true(!isset($erreurs['nb_invite']), 'Un invite doit etre accepte quand une place reste');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le quota exact ne doit pas produire d erreur globale');
        },
        'public_multi_famille_invite_depasse_limite_totale' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'invites' => true,
                    'limite_invites' => 5,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 2,
                    'places_disponibles' => 10,
                    'places_evenement' => 10,
                    'places_en_attentes_disponible' => 0,
                )),
                'visiteur_session' => array('id_auteur' => 1, 'radio_type_adherent' => 'famille'),
            ));
            association_test_set_request(array(
                'famille' => array('adherent'),
                'nb_invite' => 2,
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'prenom_inscrit_1' => 'Invite',
                'nom_inscrit_1' => 'Un',
                'email_inscrit_1' => 'invite-un@example.test',
                'prenom_inscrit_2' => 'Invite',
                'nom_inscrit_2' => 'Deux',
                'email_inscrit_2' => 'invite-deux@example.test',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_invite']), 'Deux invites doivent etre refuses quand une seule place reste');
        },
        'public_multi_premier_inscrit_compteur_cache_obsolete' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => true,
                        'places' => true,
                    ),
                ),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 9,
                    'places_evenement' => 12,
                    'places_en_attentes_disponible' => 5,
                )),
            ));
            association_test_set_request(array(
                'nb_inscrits' => 0,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Compteur Obsolete',
                'email_inscrit_1' => 'nina-compteur@example.test',
                'telephone_inscrit_1' => '0000000000',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Une identite complete doit primer sur un compteur cache obsolete a zero');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le recapitulatif ne doit pas refuser le premier participant deja identifie');
        },
        'public_multi_recapitulatif_requete_metier_vide' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => true,
                        'places' => true,
                    ),
                ),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 9,
                    'places_evenement' => 12,
                    'places_en_attentes_disponible' => 5,
                )),
            ));
            association_test_set_request(array(
                'ie_recapitulatif' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Le recapitulatif CVT ne doit pas requalifier une requete metier vide en inscription a zero');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Les etapes deja validees ne doivent pas etre rejetees au recapitulatif');
        },
        'prive_multi_accompagnants_sans_categorie_refuse' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => true,
                        'accompagnants' => true,
                    ),
                ),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'prenom_inscrit' => 'Nina',
                'nom_inscrit' => 'Sans Categorie',
                'email_inscrit' => 'nina@example.test',
            ));

            $erreurs = ie_verifier_commons('multi_prive', 1, null, null);

            association_test_assert_true(isset($erreurs['categorie']) || !empty($erreurs['message_erreur']), 'Avec accompagnants, zero categorie doit bloquer le multi BO');
            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Une selection tarifaire vide ne doit pas produire un faux message de quota');
        },
        'prive_multi_quantite_tronquee_refusee' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                )),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
            ));

            $erreurs = formulaires_inscription_evenement_multi_verifier_2_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_inscrits']), 'Deux demandes pour une seule identite doivent etre refusees avant recapitulatif');
            association_test_assert_true(!empty($erreurs['message_erreur']), 'La divergence de quantite doit etre explicite');
        },
        'prive_multi_gratuit_individuel_deux_accompagnants_autorises' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'visiteur_session' => array(
                    'id_auteur' => 2,
                    'statut' => '0minirezo',
                    'webmestre' => 'oui',
                    'radio_type_adherent' => 'individuel',
                ),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
                'places' => array(
                    'places_evenement' => 20,
                    'places_disponibles' => 10,
                    'places_limites' => 5,
                    'places_en_attentes_disponible' => 5,
                ),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
            ));

            $erreurs = formulaires_inscription_evenement_multi_verifier_1_dist(1, '');

            association_test_assert_true(
                !isset($erreurs['nb_inscrits']) && !isset($erreurs['nb_accompagnants']),
                'Hors mode famille, le type individuel ne doit pas remplacer la limite evenementielle de cinq places'
            );
        },
        'prive_multi_sans_accompagnants_refuse_deux' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => false,
                )),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
            ));

            $erreurs = formulaires_inscription_evenement_multi_verifier_1_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_inscrits']), 'Un evenement sans accompagnants doit refuser explicitement deux personnes');
        },
        'public_simple_spam_http' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'email_inscrit' => 'paul@example.test',
                'prenom_inscrit_1' => 'Paul',
                'nom_inscrit_1' => 'Libre',
                'categorie' => 3,
                'commentaire' => 'http://spam.test',
            ));

            $erreurs = formulaires_inscription_evenement_public_verifier_dist(1);
            association_test_assert_true(isset($erreurs['message_erreur']), 'Le spam http doit etre remonte par le verifier public');
        },
        'public_multi_doublon_anonyme_redirection' => function () {
            association_test_reset_env(array(
                'db' => array(
                    'spip_asso_activites' => array(
                        50 => array(
                            'id_activite' => 50,
                            'id_evenement' => 1,
                            'id_auteur' => 0,
                            'email_inscrit' => 'doublon@example.test',
                            'statut' => 'ok',
                            'nombre_inscrits' => 1,
                        ),
                    ),
                ),
            ));
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Libre',
                'email_inscrit_1' => 'doublon@example.test',
                'categorie_inscrit_1' => 3,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');
            association_test_assert_true(isset($erreurs['email_inscrit_1']) || isset($erreurs['message_erreur']), 'Le multi public doit signaler le doublon anonyme');
        },
        'public_multi_email_adherent_interdit' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nora',
                'nom_inscrit_1' => 'Martin',
                'email_inscrit_1' => 'nora@example.test',
                'categorie_inscrit_1' => 3,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');
            association_test_assert_true(isset($erreurs['email_inscrit_1']) || isset($erreurs['message_erreur']), 'Le multi public doit bloquer un email rattache a un adherent');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_verifier_suite());
}
