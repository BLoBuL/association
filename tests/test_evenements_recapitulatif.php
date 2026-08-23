<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

include_spip('formulaires/inscription_evenement_multi_public.php');

function association_test_run_recapitulatif_suite() {
    return association_test_run_cases('recapitulatif-evenements', array(
        'jeton_cvt_opaque_accepte_a_etape_finale' => function () {
            association_test_reset_env(array(
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
                'cvtm_prev_post' => 'jeton-signe-opaque-non-decodable-par-le-plugin',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_4_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Le jeton CVT opaque ne doit pas etre interprete comme une inscription a zero au recapitulatif');
            association_test_assert_true(empty($erreurs['message_erreur']), 'La derniere etape deja validee par SPIP doit pouvoir etre soumise');
        },
        'jeton_cvt_opaque_refuse_hors_recapitulatif' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
            ));
            association_test_set_request(array(
                'cvtm_prev_post' => 'jeton-signe-opaque-non-decodable-par-le-plugin',
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_inscrits']), 'Le contournement doit rester strictement limite au recapitulatif');
            association_test_assert_true(!empty($erreurs['message_erreur']), 'Une requete metier vide hors recapitulatif doit rester bloquee');
        },
        'marqueur_recapitulatif_conserve_le_controle_quota' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
                'places' => array(1 => array(
                    'places_limites' => 5,
                    'places_disponibles' => 1,
                    'places_evenement' => 5,
                    'places_en_attentes_disponible' => 0,
                )),
            ));
            association_test_set_request(array(
                'ie_recapitulatif' => 1,
                'nb_inscrits' => 2,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_4_dist(1, '');

            association_test_assert_true(isset($erreurs['nb_inscrits']), 'Le recapitulatif ne doit pas neutraliser le controle des places disponibles');
            association_test_assert_true(!empty($erreurs['message_erreur']), 'Le depassement du quota doit rester visible a la validation finale');
        },
        'marqueur_recapitulatif_accepte_un_participant_disponible' => function () {
            association_test_reset_env(array(
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
                'ie_recapitulatif' => 1,
                'nb_inscrits' => 1,
            ));

            $erreurs = formulaires_inscription_evenement_multi_public_verifier_4_dist(1, '');

            association_test_assert_true(!isset($erreurs['nb_inscrits']), 'Une place demandee pour neuf disponibles doit etre acceptee');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le scenario Zurich doit franchir la validation finale');
        },
        'traitement_recapitulatif_vide_ne_persiste_rien' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                    'places' => true,
                )),
            ));
            association_test_set_request(array(
                'ie_recapitulatif' => 1,
            ));

            $resultat = formulaires_inscription_evenement_multi_public_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $transactions = association_test_db_table('spip_transactions');

            association_test_assert_true(!empty($resultat['message_erreur']), 'Le traitement doit bloquer une inscription reellement vide');
            association_test_assert_same(0, count($activites), 'Aucune activite a zero participant ne doit etre creee');
            association_test_assert_same(0, count($transactions), 'Aucune transaction ne doit etre creee pour une inscription vide');
        },
        'recapitulatif_payant_affiche_categorie_et_montant_serveur' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'nb_inscrits' => 1,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Recap Tarif',
                'email_inscrit_1' => 'nina-recap-tarif@example.test',
                'categorie_inscrit_1' => 3,
            ));

            $recapitulatif = generer_recapitulatif_multi_env($_REQUEST, 1, 'public');

            association_test_assert_contains('Tarif public', $recapitulatif['categorie_tarif']['data'], 'Le recapitulatif doit nommer la categorie retenue');
            association_test_assert_contains('15', $recapitulatif['resultat_montant_total']['data'], 'Le recapitulatif doit afficher le montant serveur');
        },
        'recapitulatif_payant_famille_conserve_tarif_avec_jeton_cvt_opaque' => function () {
            association_test_reset_env(array(
                'visiteur_session' => association_test_default_scenario()['db']['spip_auteurs'][1],
            ));
            $valeurs_recapitulatif = array(
                'famille' => array('adherent'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'categorie_adherent' => 1,
            );

            $recapitulatif = generer_recapitulatif_multi_env($valeurs_recapitulatif, 1, 'public');
            association_test_assert_contains('Tarif adherent', $recapitulatif['categorie_tarif']['data'], 'Le recapitulatif doit afficher le tarif membre selectionne');
            association_test_assert_contains('20', $recapitulatif['resultat_montant_total']['data'], 'Le recapitulatif doit afficher le montant membre recalcule');

            association_test_set_request(array(
                'ie_recapitulatif' => 1,
                'cvtm_prev_post' => 'jeton-signe-opaque-non-decodable-par-le-plugin',
            ));
            $erreurs = formulaires_inscription_evenement_multi_public_verifier_4_dist(1, '');

            association_test_assert_true(!isset($erreurs['categorie']), 'Le tarif deja valide et affiche ne doit pas etre requalifie en selection absente');
            association_test_assert_true(empty($erreurs['message_erreur']), 'Le recapitulatif payant doit pouvoir atteindre le traitement et le paiement');
        },
        'recapitulatif_bo_multi_conserve_deux_participants' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                )),
            ));
            $recapitulatif = generer_recapitulatif_multi(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
                'prenom_inscrit_2' => 'Lea',
                'nom_inscrit_2' => 'Invitee',
                'email_inscrit_2' => 'lea@example.test',
                'telephone_inscrit_2' => '0600000002',
            ), 1, 'prive');

            association_test_assert_same(2, $recapitulatif['nombre_participants']['data'], 'Le recapitulatif CFG-01 doit afficher deux personnes');
            association_test_assert_contains('Jean Dupont', $recapitulatif['nom_participants']['data'], 'Le recapitulatif doit nommer l adherent');
            association_test_assert_contains('Lea Invitee', $recapitulatif['nom_participants']['data'], 'Le recapitulatif doit nommer l accompagnante');
        },
        'gabarits_multi_postent_le_marqueur_recapitulatif' => function () {
            $gabarits = array(
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_multi.html',
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_multi_public.html',
            );

            foreach ($gabarits as $gabarit) {
                $contenu = file_get_contents($gabarit);
                association_test_assert_true($contenu !== false, 'Le gabarit doit etre lisible : ' . basename($gabarit));
                association_test_assert_contains('name="ie_recapitulatif" value="1"', $contenu, 'Le gabarit doit identifier explicitement la derniere etape : ' . basename($gabarit));
            }
        },
        'gabarits_nettoient_les_erreurs_et_ne_fuitent_pas_la_condition_famille' => function () {
            $fonctions_plugin = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/association_evenements_fonctions.php');
            association_test_assert_true($fonctions_plugin !== false, 'Le fichier de fonctions du plugin doit etre lisible');
            association_test_assert_contains(
                'function ie_message_erreur_texte(',
                $fonctions_plugin,
                'Le filtre des gabarits doit etre declare dans le fichier de fonctions du plugin Evenements pour etre disponible au calcul SPIP'
            );

            $gabarits = array(
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement.html',
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_multi.html',
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_public.html',
                ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_multi_public.html',
            );

            foreach ($gabarits as $gabarit) {
                $contenu = file_get_contents($gabarit);
                association_test_assert_true($contenu !== false, 'Le gabarit doit etre lisible : ' . basename($gabarit));
                association_test_assert_contains('|ie_message_erreur_texte', $contenu, 'Les erreurs Saisies doivent etre rendues en texte : ' . basename($gabarit));
                association_test_assert_true(strpos($contenu, '#ENV*{message_erreur}') === false, 'Le HTML d erreur ne doit pas etre injecte brut : ' . basename($gabarit));
            }

            $simple = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement.html');
            association_test_assert_true(
                strpos($simple, '[(#CONFIG{association_metas/meta_cfg_event_config_accompagnants}|=={membre_famille}|oui)') === false,
                'La condition SPIP autour du JavaScript ne doit pas pouvoir fuiter dans la page'
            );
            association_test_assert_contains('attachMemberHandlers();', $simple, 'Le rechargement des membres reste disponible et inoffensif pour tous les modes');
        },
        'navigation_etapes_publique_resiste_aux_styles_saisies' => function () {
            $gabarit = ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/formulaires/inscription_evenement_multi_public.html';
            $contenu = file_get_contents($gabarit);

            association_test_assert_true($contenu !== false, 'Le gabarit public doit etre lisible');
            association_test_assert_contains('.formulaire_inscription_evenement_public .etapes__item:not(:first-child)::before', $contenu, 'La ligne de progression doit neutraliser la fleche ajoutee par Saisies');
            association_test_assert_contains('content: "";', $contenu, 'Le pseudo-element de liaison ne doit afficher aucun caractere parasite');
            association_test_assert_contains('order: -1;', $contenu, 'Le cercle numerote doit preceder visuellement le libelle');
            association_test_assert_contains('.formulaire_inscription_evenement_public .etapes__item[aria-current="step"]', $contenu, 'L etape active doit conserver un etat visuel explicite');
            association_test_assert_contains('grid-template-columns: 2rem minmax(0, 1fr);', $contenu, 'La navigation mobile doit aligner le cercle et son libelle');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_recapitulatif_suite());
}
