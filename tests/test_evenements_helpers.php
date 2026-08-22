<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('formulaires/inc/inscription_evenement');
include_spip('formulaires/inc/inscription_evenement_saisies');

function association_test_run_helpers_suite() {
    return association_test_run_cases('helpers-evenements', array(
        'formater_post_form_prive_payant_famille' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'famille' => array('adherent', 'enfant_1'),
                'categorie' => array(
                    1 => array('adherent'),
                    2 => array('enfant_1'),
                ),
                'commentaire' => 'Test famille',
            ));

            $data = formater_post_form(1, $_REQUEST, affichage_dans_activites(1), 'prive');

            association_test_assert_same(1, $data['id_auteur'], 'L auteur prive doit etre conserve');
            association_test_assert_same(2, $data['nombre_participants'], 'Le nombre de participants famille doit etre calcule');
            association_test_assert_equals(25, $data['montant_total'], 'Le total famille doit etre calcule');
            association_test_assert_true(!empty($data['participants_json']), 'Les participants famille doivent etre serialises');
            association_test_assert_contains('Jean', $data['nom_participants'], 'Le recapitulatif doit inclure l adherent');
        },
        'formater_post_form_public_gratuit_anonyme' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'accompagnants' => true,
                    ),
                ),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600000000',
                'nb_inscrits' => 3,
            ));

            $data = formater_post_form(1, $_REQUEST, affichage_dans_activites(1), 'public');

            association_test_assert_same(false, $data['id_auteur'], 'Un public anonyme doit rester sans auteur');
            association_test_assert_same(3, $data['nombre_participants'], 'Le nombre saisi doit etre conserve');
            association_test_assert_same('Paul', $data['premier_inscrit']['prenom'], 'Le premier inscrit doit venir du formulaire');
        },
        'formater_post_form_multi_famille' => function () {
            association_test_reset_env();
            $post = array(
                'famille' => array('adherent', 'conjoint'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'tel_adherent' => '0102030405',
                'categorie_adherent' => 1,
                'prenom_conjoint' => 'Marie',
                'nom_conjoint' => 'Dupont',
                'email_conjoint' => 'marie@example.test',
                'tel_conjoint' => '0607080910',
                'categorie_conjoint' => 4,
                'commentaire' => 'Multi famille',
                'annotation' => 'BO',
                'notifier' => 'on',
            );

            $data = formater_post_form_multi($post, 'public');

            association_test_assert_same(2, $data['nombre_participants'], 'Le multi famille doit compter les membres selectionnes');
            association_test_assert_same('Jean', $data['premier_inscrit']['prenom'], 'Le premier inscrit multi doit etre l adherent');
            association_test_assert_true(isset($data['array_post_inscrits']['conjoint']), 'Le conjoint doit etre present dans le payload');
            association_test_assert_same(4, $data['categorie_result']['conjoint'], 'La categorie du conjoint doit etre preservee');
        },
        'formater_post_form_multi_bo_identite_sans_categorie' => function () {
            association_test_reset_env();
            $post = array(
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'select_type_inscrit' => 'non_membre',
                    'prenom_inscrit' => 'Nina',
                    'nom_inscrit' => 'Sans Tarif',
                    'email_inscrit' => 'nina@example.test',
                    'tel_inscrit' => '0601020304',
                ))),
                'commentaire' => 'Inscription BO multi sans tarif',
                'annotation' => 'Regression identite',
            );

            $data = formater_post_form_multi($post, 'public');

            association_test_assert_same('Nina', $data['premier_inscrit']['prenom'], 'Le prenom simple de l etape precedente doit etre conserve');
            association_test_assert_same('Sans Tarif', $data['premier_inscrit']['nom'], 'Le nom simple de l etape precedente doit etre conserve');
            association_test_assert_same('nina@example.test', $data['premier_inscrit']['email'], 'L email simple de l etape precedente doit etre conserve');
            association_test_assert_same('0601020304', $data['premier_inscrit']['tel'], 'Le telephone simple de l etape precedente doit etre conserve');
        },
        'formater_post_form_multi_bo_adherent_selectionne' => function () {
            association_test_reset_env();

            $data = formater_post_form_multi(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 1,
                'prenom_inscrit' => '',
                'nom_inscrit' => '',
                'email_inscrit' => '',
            ), 'prive');

            association_test_assert_same(1, $data['id_auteur'], 'Le BO multi doit conserver l auteur selectionne');
            association_test_assert_same('Jean', $data['premier_inscrit']['prenom'], 'Le prenom doit provenir de l adherent selectionne');
            association_test_assert_same('Dupont', $data['premier_inscrit']['nom'], 'Le nom doit provenir de l adherent selectionne');
            association_test_assert_same('jean@example.test', $data['premier_inscrit']['email'], 'L email doit provenir de l adherent selectionne');
            association_test_assert_same(1, $data['nombre_participants'], 'Le participant principal doit compter pour une inscription');
            association_test_assert_same('Jean Dupont', $data['nom_participants'], 'Le recapitulatif doit afficher l adherent selectionne');
        },
        'formater_post_form_multi_bo_preserve_accompagnant' => function () {
            association_test_reset_env();

            $data = formater_post_form_multi(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
                'prenom_inscrit_2' => 'Lea',
                'nom_inscrit_2' => 'Invitee',
                'email_inscrit_2' => 'lea@example.test',
                'telephone_inscrit_2' => '0600000002',
            ), 'prive');

            association_test_assert_same(2, $data['nombre_participants'], 'La quantite BO demandee doit rester materialisee');
            association_test_assert_same(2, count($data['array_post_inscrits']), 'L hydratation ne doit pas supprimer l accompagnant');
            association_test_assert_contains('Jean Dupont', $data['nom_participants'], 'Le recapitulatif doit conserver l adherent');
            association_test_assert_contains('Lea Invitee', $data['nom_participants'], 'Le recapitulatif doit conserver l accompagnante');
            association_test_assert_contains('Lea', ie_construire_participants_json($data), 'Le JSON doit contenir le second participant');
        },
        'recapitulatif_multi_public_semantique' => function () {
            $template = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/formulaires/inscription_evenement_multi_public.html');
            $lang = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/lang/association_fr.php');

            association_test_assert_contains('<table class="inscription-evenement-public__recap-table">', $template, 'Le recapitulatif doit utiliser un tableau semantique');
            association_test_assert_contains('<th scope="row" class="inscription-evenement-public__recap-label">#VALEUR{label}</th>', $template, 'Le libelle du recapitulatif doit etre un en-tete de ligne sans parentheses');
            association_test_assert_contains('<td class="inscription-evenement-public__recap-data">#VALEUR{data}</td>', $template, 'La valeur du recapitulatif doit etre une cellule');
            association_test_assert_true(strpos($template, '<ul class="spip inscription-evenement-public__recap-list">') === false, 'Le recapitulatif ne doit plus heriter des puces SPIP');
            association_test_assert_true(strpos($template, 'class="label inscription-evenement-public__recap-label"') === false, 'Le recapitulatif ne doit plus heriter des parentheses du theme');
            association_test_assert_true(strpos($template, '>(#VALEUR{label})</') === false, 'Le libelle du recapitulatif ne doit contenir aucune parenthese de gabarit');
            association_test_assert_contains("'activite_form_public_nombre_participants' => 'Nombre total de participants'", $lang, 'Le libelle du nombre doit etre correct');
            association_test_assert_contains("'activite_form_public_nom_participants' => 'Nom des participants'", $lang, 'Le libelle des noms doit etre correct');
            association_test_assert_contains("'activite_form_public_statut_inscription' => 'Statut prévisionnel de l\\'inscription'", $lang, 'Le libelle du statut doit etre correct');
        },
        'calculer_montant_total_simple' => function () {
            association_test_reset_env();

            $res = calculer_montant_total(1, array(
                'inscrit_1' => 3,
                'inscrit_2' => 2,
            ));

            association_test_assert_equals(20, $res['montant_total'], 'Le total simple doit sommer public et enfant');
            association_test_assert_same(2, count($res['transaction']), 'Chaque categorie choisie doit etre tracee');
        },
        'calculer_montant_ignore_quantites_nulles' => function () {
            association_test_reset_env();

            $res = calculer_montant_total(1, array(
                1 => 0,
                2 => 0,
                3 => 1,
                4 => 0,
                5 => 0,
            ));

            association_test_assert_true($res['valide'], 'Les tarifs non selectionnes postes a zero ne doivent pas invalider le choix');
            association_test_assert_equals(15, $res['montant_total'], 'Seul le tarif effectivement selectionne doit etre calcule');
            association_test_assert_same(array(), $res['categories_invalides'], 'Aucune quantite nulle ne doit etre signalee comme invalide');
        },
        'tarifs_sans_defaut_et_couple_avec_conjoint' => function () {
            association_test_reset_env();
            $tarifs = affichage_dans_activites(1)['montant'];

            $saisies = champs_saisies_tarifs(
                'adherent',
                'adherent',
                array('adherent', 'indifferent', 'couple'),
                $tarifs,
                1,
                true
            );
            $options = $saisies['adherent']['saisies'][0]['options'];

            association_test_assert_same(false, $options['defaut'], 'Aucun tarif ne doit etre selectionne par defaut');
            association_test_assert_true(isset($options['datas'][4]), 'Le tarif couple doit etre propose quand le conjoint est selectionne');
        },
        'limite_invites_soustrait_les_membres_principaux' => function () {
            $places = array('places_limites' => 2);
            $affichage = array('accompagnants' => true, 'limite_invites' => 5);

            association_test_assert_same(1, ie_limite_invites_effective($places, $affichage, 0, 1), 'Une place invite doit rester apres un participant principal');
            association_test_assert_same(0, ie_limite_invites_effective($places, $affichage, 0, 2), 'Deux membres principaux doivent occuper toute la limite');
        },
        'verifier_spam_formulaire_inscription' => function () {
            association_test_reset_env();

            $spam = verifier_spam_formulaire_inscription(array(
                'email_inscrit' => 'spam@qq.com',
                'prenom_inscrit' => 'Spam',
                'nom_inscrit' => 'Spam',
            ));
            association_test_assert_true(is_array($spam) && isset($spam['message_erreur']), 'Le domaine qq.com doit etre bloque');

            $http = verifier_spam_formulaire_inscription(array(
                'email_inscrit' => 'ok@example.test',
                'prenom_inscrit' => 'Jean',
                'nom_inscrit' => 'Dupont',
                'commentaire' => 'http://spam.test',
            ));
            association_test_assert_true(is_array($http), 'Un lien http doit etre bloque');

            $clean = verifier_spam_formulaire_inscription(array(
                'email_inscrit' => 'clean@example.test',
                'prenom_inscrit' => 'Jean',
                'nom_inscrit' => 'Dupont',
                'commentaire' => 'Bonjour',
            ));
            association_test_assert_same(false, $clean, 'Un message propre ne doit pas etre bloque');
        },
        'activite_enregistrement_calculator_statuts' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'places' => true,
                        'validation' => true,
                    ),
                ),
                'places' => array(
                    1 => array(
                        'places_limites' => 2,
                        'places_disponibles' => 1,
                        'places_evenement' => 2,
                        'places_en_attentes_disponible' => 3,
                        'evenement_date_debut' => '2026-12-25 10:00:00',
                        'evenement_reseau_fiafe' => 'non',
                    ),
                ),
            ));

            $attente = activite_enregistrement_calculator(1, 2);
            association_test_assert_same('liste_attente', $attente['statut'], 'Le calculateur doit basculer en attente si le quota est depasse');

            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'places' => false,
                        'validation' => false,
                    ),
                ),
            ));
            $ok = activite_enregistrement_calculator(1, 1);
            association_test_assert_same('ok', $ok['statut'], 'Un evenement sans validation ni quota doit etre ok');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_helpers_suite());
}
