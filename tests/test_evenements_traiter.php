<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

include_spip('formulaires/inscription_evenement.php');
include_spip('formulaires/inscription_evenement_multi.php');
include_spip('formulaires/inscription_evenement_public.php');
include_spip('formulaires/inscription_evenement_multi_public.php');
include_spip('inc/fonctions/facteur_envoyer_mail_activites');

function association_test_run_traiter_suite() {
    return association_test_run_cases('traiter-evenements', array(
        'public_simple_gratuit_cookie_et_redirection_evenement' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => false,
                        'validation' => false,
                        'type_inscrits_evenement' => 'public',
                    ),
                ),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600000000',
                'nb_inscrits' => 1,
                'commentaire' => 'Bonjour',
            ));

            $res = formulaires_inscription_evenement_public_traiter_dist(1);
            $activites = association_test_db_table('spip_asso_activites');

            association_test_assert_contains('/public/evenement?id_evenement=1', $res['redirect'], 'Un gratuit doit revenir sur la page evenement');
            association_test_assert_same(1, count($activites), 'Une activite doit etre creee');
            association_test_assert_same(1, count($GLOBALS['_TEST_SIDE_EFFECTS']['cookies']), 'Un cookie doit etre pose pour un anonyme public');
        },
        'public_simple_invalide_non_persiste' => function () {
            association_test_reset_env();
            association_test_set_request(array());

            $res = formulaires_inscription_evenement_public_traiter_dist(1);
            $activites = association_test_db_table('spip_asso_activites');
            $transactions = association_test_db_table('spip_transactions');

            association_test_assert_true(!empty($res['message_erreur']), 'Le traitement direct invalide doit retourner une erreur');
            association_test_assert_same(0, count($activites), 'Aucune activite vide ne doit etre creee');
            association_test_assert_same(0, count($transactions), 'Aucune transaction vide ne doit etre creee');
        },
        'payant_sans_tarif_ne_persiste_ni_ne_notifie' => function () {
            association_test_reset_env(array(
                'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Nina',
                'nom_inscrit' => 'Sans Tarif',
                'email_inscrit' => 'nina-sans-tarif@example.test',
            ));

            $res = formulaires_inscription_evenement_public_traiter_dist(1);

            association_test_assert_true(!empty($res['message_erreur']), 'Le traitement final doit refuser le tarif absent');
            association_test_assert_same(0, count(association_test_db_table('spip_asso_activites')), 'Aucune activite ne doit etre creee');
            association_test_assert_same(0, count(association_test_db_table('spip_transactions')), 'Aucune transaction ne doit etre creee');
            association_test_assert_same(0, count($GLOBALS['_TEST_SIDE_EFFECTS']['jobs']), 'Aucune notification ne doit etre planifiee apres refus');
        },
        'payant_tarif_et_montant_falsifies_refuses' => function () {
            foreach (array(
                'tarif' => array('categorie' => 999),
                'montant' => array('categorie' => 3, 'montant_total' => 1),
                'transaction' => array('categorie' => 3, 'id_transaction' => 999),
            ) as $falsification => $champs) {
                association_test_reset_env(array(
                    'events' => array(1 => array('payant' => true, 'accompagnants' => false)),
                ));
                association_test_set_request(array_merge(array(
                    'prenom_inscrit' => 'Nina',
                    'nom_inscrit' => 'Falsification',
                    'email_inscrit' => 'nina-' . $falsification . '@example.test',
                ), $champs));

                $res = formulaires_inscription_evenement_public_traiter_dist(1);

                association_test_assert_true(!empty($res['message_erreur']), 'La falsification ' . $falsification . ' doit etre refusee');
                association_test_assert_same(0, count(association_test_db_table('spip_asso_activites')), 'Aucune activite ne doit etre creee apres falsification ' . $falsification);
                association_test_assert_same(0, count($GLOBALS['_TEST_SIDE_EFFECTS']['jobs']), 'Aucune notification ne doit suivre la falsification ' . $falsification);
            }
        },
        'prive_multi_accompagnants_sans_categorie_non_persiste' => function () {
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

            $res = formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');

            association_test_assert_true(!empty($res['message_erreur']), 'Le traitement multi sans categorie doit etre bloque');
            association_test_assert_same(0, count($activites), 'Aucune activite ne doit etre creee avec zero categorie');
        },
        'public_simple_payant_redirection_paiement' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => true,
                        'validation' => false,
                        'validation_sur_paiement' => 'non',
                        'type_inscrits_evenement' => 'public',
                    ),
                ),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Libre',
                'email_inscrit' => 'paul@example.test',
                'tel_inscrit' => '0600000000',
                'categorie' => 3,
                'commentaire' => 'Paiement',
            ));

            $res = formulaires_inscription_evenement_public_traiter_dist(1);
            $transactions = association_test_db_table('spip_transactions');

            association_test_assert_same(1, count($transactions), 'Une transaction doit etre creee en payant');
            association_test_assert_contains('/public/paiement?id_transaction=', $res['redirect'], 'Le payant doit rediriger vers le paiement');
        },
        'public_simple_payant_sans_paiements_persiste_sans_transaction' => function () {
            association_test_reset_env(array(
                'plugins' => array(
                    'association_paiements' => false,
                    'association_communication' => false,
                ),
                'events' => array(
                    1 => array(
                        'payant' => true,
                        'accompagnants' => false,
                        'validation' => false,
                        'validation_sur_paiement' => 'non',
                        'type_inscrits_evenement' => 'public',
                    ),
                ),
            ));
            association_test_set_request(array(
                'prenom_inscrit' => 'Paul',
                'nom_inscrit' => 'Autonome',
                'email_inscrit' => 'paul-autonome@example.test',
                'tel_inscrit' => '0600000000',
                'categorie' => 3,
                'commentaire' => 'Sans module paiements',
            ));

            $res = formulaires_inscription_evenement_public_traiter_dist(1);
            $activites = association_test_db_table('spip_asso_activites');

            association_test_assert_contains('/public/evenement?id_evenement=1', $res['redirect'], 'Sans Paiements, revenir sur la fiche evenement');
            association_test_assert_same(1, count($activites), 'Le tarif payant doit rester enregistrable sans Paiements');
            association_test_assert_same(0, count(association_test_db_table('spip_transactions')), 'Aucune transaction ne doit etre creee sans Paiements');
            association_test_assert_same(0, count($GLOBALS['_TEST_SIDE_EFFECTS']['jobs']), 'Aucun message ne doit etre planifie sans Communication');
            $activite = reset($activites);
            association_test_assert_same(0, intval($activite['id_transaction'] ?? 0), 'L identifiant de transaction doit rester nullable');
            association_test_assert_true(!empty($activite['tarifs_selectionnes']), 'Le detail du tarif doit rester porte par l inscription');
        },
        'prive_simple_payant_notifie' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => 1,
                'notifier_adherent' => true,
                'annotation' => 'Test BO',
            ));

            $res = formulaires_inscription_evenement_traiter_dist();
            $activites = association_test_db_table('spip_asso_activites');
            $id_activite_creee = intval(array_key_first($activites));
            $notifications = array_values(array_filter(
                $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'],
                function ($job) {
                    return ($job['fonction'] ?? '') === 'facteur_envoyer_mail_activites';
                }
            ));
            list($sujet_notification, $modele_notification) = _determiner_modeles_emails_adherent('inscription_backend');

            association_test_assert_contains('/ecrire/voir_activites?id=1', $res['redirect'], 'Le prive simple doit revenir sur voir_activites');
            association_test_assert_same(1, count($activites), 'Une activite BO doit etre creee');
            association_test_assert_same(1, count($notifications), 'Une seule notification d inscription doit etre capturee');
            association_test_assert_same(1, $notifications[0]['arguments'][0], 'La notification doit viser l evenement synthetique');
            association_test_assert_same('inscription_backend', $notifications[0]['arguments'][1], 'Le declencheur BO doit choisir le type backend');
            association_test_assert_same(array($id_activite_creee), $notifications[0]['arguments'][2], 'La notification doit viser uniquement l activite creee');
            association_test_assert_same('notifications:inscription_activite_mail_sujet_backend', $sujet_notification, 'Le sujet BO doit utiliser le domaine notifications');
            association_test_assert_same('notifications/inscription_activite_backend', $modele_notification, 'Le modele BO doit etre celui de l inscription backend');
            association_test_assert_same('jean@example.test', $activites[$id_activite_creee]['email_inscrit'], 'Le destinataire capture doit rester l adresse synthetique du membre');
        },
        'prive_simple_case_notification_decochee_ne_notifie_pas' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'categorie' => 1,
            ));

            $res = formulaires_inscription_evenement_traiter_dist();
            $activites = association_test_db_table('spip_asso_activites');
            $notifications = array_values(array_filter(
                $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'],
                function ($job) {
                    return ($job['fonction'] ?? '') === 'facteur_envoyer_mail_activites';
                }
            ));

            association_test_assert_contains('/ecrire/voir_activites?id=1', $res['redirect'], 'La creation BO sans notification doit reussir');
            association_test_assert_same(1, count($activites), 'La case decochee ne doit pas bloquer l inscription');
            association_test_assert_same(0, count($notifications), 'La case decochee ne doit planifier aucun email');
        },
        'prive_multi_payant' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
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
                'categorie_conjoint' => 1,
                'notifier' => 'on',
            ));

            $res = formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $transactions = association_test_db_table('spip_transactions');

            association_test_assert_contains('/ecrire/voir_activites?id=1', $res['redirect'], 'Le multi prive doit revenir sur voir_activites');
            association_test_assert_same(1, count($activites), 'Une activite multi doit etre creee');
            association_test_assert_same(1, count($transactions), 'Le multi payant doit creer une transaction');
        },
        'prive_multi_creation_puis_modification_avec_compte' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'famille' => array('adherent', 'conjoint'),
                'prenom_adherent' => 'Jean creation',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean.creation@example.test',
                'telephone_adherent' => '0102030405',
                'date_naissance_adherent' => '1980-01-02',
                'categorie_adherent' => 1,
                'prenom_conjoint' => 'Marie creation',
                'nom_conjoint' => 'Dupont',
                'email_conjoint' => 'marie.creation@example.test',
                'telephone_conjoint' => '0607080910',
                'date_naissance_conjoint' => '1982-03-04',
                'categorie_conjoint' => 1,
                'commentaire' => 'Commentaire cree avec compte',
                'annotation' => 'Annotation creee avec compte',
            ));

            formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $activite = reset($activites);
            $id_activite = intval($activite['id_activite']);

            association_test_assert_same(1, intval($activite['id_auteur']), 'La creation BO multi doit conserver le compte selectionne');
            association_test_assert_same(2, intval($activite['nombre_inscrits']), 'La creation BO multi doit conserver le nombre de participants');
            association_test_assert_contains('Jean creation', $activite['participants_json'], 'La creation doit persister les identites participants');
            association_test_assert_contains('1982-03-04', $activite['participants_json'], 'La creation doit persister les champs supplementaires');

            association_test_set_request(array('modif' => 'oui', 'id_activite' => $id_activite));
            $charge = formulaires_inscription_evenement_multi_charger_dist('', $id_activite);
            association_test_assert_same(1, intval($charge['id_evenement']), 'La modification doit retrouver l evenement depuis l activite');
            association_test_assert_same('Jean creation', _request('prenom_adherent'), 'Le prenom persiste doit etre rehydrate');
            association_test_assert_same('1982-03-04', _request('date_naissance_conjoint'), 'Le champ supplementaire persiste doit etre rehydrate');
            association_test_assert_same('Commentaire cree avec compte', _request('commentaire'), 'Le commentaire doit etre rehydrate');
            association_test_assert_same('Annotation creee avec compte', _request('annotation'), 'L annotation doit etre rehydratee');

            $erreurs = formulaires_inscription_evenement_multi_verifier_1_dist('', $id_activite);
            association_test_assert_same(array(), $erreurs, 'La premiere etape pre-remplie ne doit pas echouer sur un champ rempli');

            set_request('commentaire', 'Commentaire modifie avec compte');
            set_request('annotation', 'Annotation modifiee avec compte');
            set_request('notifier_adherent', 'on');
            $resultat_modification = formulaires_inscription_evenement_multi_traiter_dist('', $id_activite);
            association_test_assert_contains('voir_activites', $resultat_modification['redirect'] ?? '', 'La modification avec compte doit etre traitee');
            $activite_modifiee = association_test_db_table('spip_asso_activites')[$id_activite];

            association_test_assert_same(1, intval($activite_modifiee['id_auteur']), 'La modification doit conserver le compte');
            association_test_assert_same(2, intval($activite_modifiee['nombre_inscrits']), 'La modification doit conserver nombre_inscrits');
            association_test_assert_contains('Jean creation', $activite_modifiee['participants_json'], 'La modification doit conserver participants_json');
            association_test_assert_contains('1982-03-04', $activite_modifiee['participants_json'], 'La modification doit conserver les champs supplementaires');
            association_test_assert_same('Commentaire modifie avec compte', $activite_modifiee['commentaire'], 'La modification doit conserver le commentaire demande');
            association_test_assert_same('Annotation modifiee avec compte', $activite_modifiee['annotation'], 'La modification doit conserver l annotation demandee');
            $notifications = array_values(array_filter(
                $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'],
                function ($job) {
                    return ($job['fonction'] ?? '') === 'facteur_envoyer_mail_activites';
                }
            ));
            association_test_assert_same(1, count($notifications), 'La case cochee doit planifier un seul email de modification');
            association_test_assert_same('modification_backend', $notifications[0]['arguments'][1] ?? '', 'La modification BO doit utiliser le modele email de modification');
            association_test_assert_same(array($id_activite), $notifications[0]['arguments'][2] ?? array(), 'L email de modification doit viser uniquement l inscription modifiee');
        },
        'prive_multi_creation_puis_modification_sans_compte' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'nb_inscrits' => 2,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Libre',
                'email_inscrit_1' => 'nina@example.test',
                'telephone_inscrit_1' => '0600000001',
                'date_naissance_inscrit_1' => '1990-05-06',
                'categorie_inscrit_1' => 3,
                'prenom_inscrit_2' => 'Lia',
                'nom_inscrit_2' => 'Libre',
                'email_inscrit_2' => 'lia@example.test',
                'telephone_inscrit_2' => '0600000002',
                'date_naissance_inscrit_2' => '1992-07-08',
                'categorie_inscrit_2' => 3,
                'commentaire' => 'Commentaire cree sans compte',
                'annotation' => 'Annotation creee sans compte',
            ));

            formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $activite = reset($activites);
            $id_activite = intval($activite['id_activite']);

            association_test_assert_same(0, intval($activite['id_auteur']), 'La creation BO multi sans compte doit conserver id_auteur=0');
            association_test_assert_same(2, intval($activite['nombre_inscrits']), 'La creation sans compte doit conserver le nombre de participants');
            association_test_assert_contains('Nina', $activite['participants_json'], 'La creation sans compte doit persister les identites');
            association_test_assert_contains('1992-07-08', $activite['participants_json'], 'La creation sans compte doit persister les champs supplementaires');

            association_test_set_request(array('modif' => 'oui', 'id_activite' => $id_activite));
            $charge = formulaires_inscription_evenement_multi_charger_dist('', $id_activite);
            association_test_assert_same(1, intval($charge['id_evenement']), 'La modification sans compte doit retrouver l evenement');
            association_test_assert_same('public', _request('select_type_inscrit'), 'Le type sans compte doit etre rehydrate');
            association_test_assert_same('Nina', _request('prenom_inscrit_1'), 'L identite sans compte doit etre rehydratee');
            association_test_assert_same('1992-07-08', _request('date_naissance_inscrit_2'), 'Le champ supplementaire sans compte doit etre rehydrate');
            association_test_assert_same('Commentaire cree sans compte', _request('commentaire'), 'Le commentaire sans compte doit etre rehydrate');
            association_test_assert_same('Annotation creee sans compte', _request('annotation'), 'L annotation sans compte doit etre rehydratee');

            $erreurs = formulaires_inscription_evenement_multi_verifier_1_dist('', $id_activite);
            association_test_assert_same(array(), $erreurs, 'La premiere etape sans compte pre-remplie doit rester valide');

            set_request('commentaire', 'Commentaire modifie sans compte');
            set_request('annotation', 'Annotation modifiee sans compte');
            $resultat_modification = formulaires_inscription_evenement_multi_traiter_dist('', $id_activite);
            association_test_assert_contains('voir_activites', $resultat_modification['redirect'] ?? '', 'La modification sans compte doit etre traitee');
            $activite_modifiee = association_test_db_table('spip_asso_activites')[$id_activite];

            association_test_assert_same(0, intval($activite_modifiee['id_auteur']), 'La modification sans compte doit conserver id_auteur=0');
            association_test_assert_same(2, intval($activite_modifiee['nombre_inscrits']), 'La modification sans compte doit conserver nombre_inscrits');
            association_test_assert_contains('Nina', $activite_modifiee['participants_json'], 'La modification sans compte doit conserver participants_json');
            association_test_assert_contains('1992-07-08', $activite_modifiee['participants_json'], 'La modification sans compte doit conserver les champs supplementaires');
            association_test_assert_same('Commentaire modifie sans compte', $activite_modifiee['commentaire'], 'La modification sans compte doit conserver le commentaire demande');
            association_test_assert_same('Annotation modifiee sans compte', $activite_modifiee['annotation'], 'La modification sans compte doit conserver l annotation demandee');
            $notifications = array_values(array_filter(
                $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'],
                function ($job) {
                    return ($job['fonction'] ?? '') === 'facteur_envoyer_mail_activites';
                }
            ));
            association_test_assert_same(0, count($notifications), 'La modification avec la case decochee ne doit envoyer aucun email');
        },
        'prive_multi_gratuit_cfg01_persiste_deux' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                )),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'nb_inscrits' => 2,
                'prenom_inscrit_2' => 'Lea',
                'nom_inscrit_2' => 'Invitee',
                'email_inscrit_2' => 'lea@example.test',
                'telephone_inscrit_2' => '0600000002',
            ));

            $res = formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $activite = reset($activites);

            association_test_assert_contains('/ecrire/voir_activites?id=1', $res['redirect'], 'CFG-01 doit terminer le parcours BO');
            association_test_assert_same(2, $activite['nombre_inscrits'], 'CFG-01 doit persister deux inscrits');
            association_test_assert_contains('Jean Dupont', $activite['nom_participants'], 'Les noms doivent contenir l adherent');
            association_test_assert_contains('Lea Invitee', $activite['nom_participants'], 'Les noms doivent contenir l accompagnante');
            association_test_assert_contains('Lea', $activite['participants_json'], 'participants_json doit contenir l accompagnante');
        },
        'prive_multi_quantite_incomplete_non_persiste' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
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

            $res = formulaires_inscription_evenement_multi_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');

            association_test_assert_true(!empty($res['message_erreur']), 'Le traitement direct doit refuser la quantite incomplete');
            association_test_assert_same(0, count($activites), 'Aucune activite tronquee ne doit etre persistee');
        },
        'public_multi_payant_paiement' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'nb_inscrits' => 2,
                'prenom_inscrit_1' => 'Nina',
                'nom_inscrit_1' => 'Libre',
                'email_inscrit_1' => 'nina@example.test',
                'telephone_inscrit_1' => '0600000000',
                'categorie_inscrit_1' => 3,
                'prenom_inscrit_2' => 'Lia',
                'nom_inscrit_2' => 'Libre',
                'email_inscrit_2' => 'lia@example.test',
                'telephone_inscrit_2' => '0600000001',
                'categorie_inscrit_2' => 3,
                'commentaire' => 'Multi public',
            ));

            $res = formulaires_inscription_evenement_multi_public_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');

            association_test_assert_contains('/public/paiement?id_transaction=', $res['redirect'], 'Le multi public payant doit rediriger vers paiement');
            association_test_assert_same(1, count($activites), 'Une activite multi publique doit etre creee');
            $activite = reset($activites);
            association_test_assert_contains('Nina', $activite['participants_json'], 'Les participants doivent etre serialises dans l activite');
            association_test_assert_contains('Lia', $activite['participants_json'], 'Le second participant sans compte doit etre serialise');
            association_test_assert_same(2, $activite['nombre_inscrits'], 'Le parcours sans compte doit conserver deux inscrits');
        },
        'public_multi_famille_tarif_membre_paiement_et_notification_unique' => function () {
            association_test_reset_env(array(
                'visiteur_session' => association_test_default_scenario()['db']['spip_auteurs'][1],
            ));
            association_test_set_request(array(
                'famille' => array('adherent'),
                'prenom_adherent' => 'Jean',
                'nom_adherent' => 'Dupont',
                'email_adherent' => 'jean@example.test',
                'categorie_adherent' => 1,
                'ie_recapitulatif' => 1,
            ));

            $res = formulaires_inscription_evenement_multi_public_traiter_dist(1, '');
            $activites = association_test_db_table('spip_asso_activites');
            $transactions = association_test_db_table('spip_transactions');
            $notifications = array_values(array_filter(
                $GLOBALS['_TEST_SIDE_EFFECTS']['jobs'],
                function ($job) {
                    return ($job['fonction'] ?? '') === 'facteur_envoyer_mail_activites';
                }
            ));
            list($sujet_notification, $modele_notification) = _determiner_modeles_emails_adherent('inscription_frontend');

            association_test_assert_contains('/public/paiement?id_transaction=', $res['redirect'], 'Le tarif membre familial doit ouvrir le paiement');
            association_test_assert_same(1, count($activites), 'Une seule activite synthetique doit etre creee');
            association_test_assert_same(1, count($transactions), 'Une seule transaction synthetique doit etre creee');
            association_test_assert_same(1, count($notifications), 'Une seule notification doit etre capturee pour l inscription acceptee');
            association_test_assert_same('inscription_frontend', $notifications[0]['arguments'][1] ?? '', 'Le declencheur doit correspondre a l inscription publique validee');
            association_test_assert_same('notifications:inscription_activite_mail_sujet_frontend', $sujet_notification, 'Le sujet doit utiliser la notification publique d inscription');
            association_test_assert_same('notifications/inscription_activite_frontend', $modele_notification, 'Le modele doit utiliser la notification publique d inscription');
            $activite = reset($activites);
            association_test_assert_same('jean@example.test', $activite['email_inscrit'], 'Le destinataire metier doit rester l adresse synthetique du membre');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_traiter_suite());
}
