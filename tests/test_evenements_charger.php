<?php

require_once __DIR__ . '/inc/bootstrap_evenements_cli.php';

include_spip('formulaires/inscription_evenement.php');
include_spip('formulaires/inscription_evenement_multi.php');
include_spip('formulaires/inscription_evenement_public.php');
include_spip('formulaires/inscription_evenement_multi_public.php');

function association_test_collect_saisies_by_name($saisies) {
    $resultat = array();
    $collecter = function ($liste) use (&$collecter, &$resultat) {
        foreach ((array) $liste as $saisie) {
            if (!is_array($saisie)) {
                continue;
            }
            $nom = $saisie['options']['nom'] ?? '';
            if ($nom !== '') {
                $resultat[$nom] = $saisie;
            }
            if (!empty($saisie['saisies'])) {
                $collecter($saisie['saisies']);
            }
        }
    };
    $collecter($saisies);

    return $resultat;
}

function association_test_run_charger_suite() {
    return association_test_run_cases('charger-evenements', array(
        'prive_simple_payant' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
            ));

            $res = formulaires_inscription_evenement_charger_dist();
            $noms = association_test_collect_names($res['_saisies']);

            association_test_assert_true(is_array($res), 'Le charger prive simple doit renvoyer un tableau');
            association_test_assert_true(in_array('select_type_inscrit', $noms, true), 'Le type d inscrit doit etre present');
            association_test_assert_true(
                in_array('categorie[1]', $noms, true)
                || in_array('categorie', $noms, true)
                || in_array('categorie[adherent]', $noms, true),
                'Les tarifs doivent etre proposes'
            );
            association_test_assert_same('oui', $res['creation_inscription'], 'Le charger prive simple doit etre en creation');
        },
        'prive_simple_payant_tarifs_visibles_pour_membre' => function () {
            association_test_reset_env(array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'auteur_session' => array('id_auteur' => 3),
                'events' => array(1 => array('payant' => true, 'accompagnants' => true)),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
            ));

            $res = formulaires_inscription_evenement_charger_dist(1);
            $tarifs = array();
            $collecter_tarifs = function ($saisies) use (&$collecter_tarifs, &$tarifs) {
                foreach ((array) $saisies as $saisie) {
                    if (!is_array($saisie)) {
                        continue;
                    }
                    $nom = $saisie['options']['nom'] ?? '';
                    if (strpos($nom, 'categorie[') === 0) {
                        $tarifs[] = $saisie;
                    }
                    if (!empty($saisie['saisies'])) {
                        $collecter_tarifs($saisie['saisies']);
                    }
                }
            };
            $collecter_tarifs($res['_saisies'] ?? array());

            association_test_assert_true(!empty($tarifs), 'Le BO membre doit exposer les quantites tarifaires');
            foreach ($tarifs as $tarif) {
                association_test_assert_true(
                    empty($tarif['options']['afficher_si']),
                    'Le tarif BO ne doit pas etre masque pour le type membre'
                );
            }
        },
        'prive_simple_matrice_champs_et_conditions_affichage' => function () {
            $types_inscrits = array(
                'membre' => array('membre' => 1),
                'non_membre' => array('non_membre' => 2),
                'membre_reseau_fiafe' => array(),
                'public' => array(),
            );
            $configurations = array(
                'gratuit_sans_accompagnants' => array(false, false),
                'gratuit_avec_accompagnants' => array(false, true),
                'payant_sans_accompagnants' => array(true, false),
                'payant_avec_accompagnants' => array(true, true),
            );

            foreach ($types_inscrits as $type_inscrit => $selection_auteur) {
                foreach ($configurations as $scenario => $configuration) {
                    list($payant, $accompagnants) = $configuration;
                    association_test_reset_env(array(
                        'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                        'auteur_session' => array('id_auteur' => 3),
                        'events' => array(1 => array(
                            'payant' => $payant,
                            'accompagnants' => $accompagnants,
                        )),
                    ));
                    association_test_set_request(array_merge(array(
                        'id_evenement' => 1,
                        'select_type_inscrit' => $type_inscrit,
                    ), $selection_auteur));

                    $res = formulaires_inscription_evenement_charger_dist(1);
                    $saisies = association_test_collect_saisies_by_name($res['_saisies'] ?? array());
                    $contexte = $type_inscrit . ' / ' . $scenario;

                    association_test_assert_true(isset($saisies['select_type_inscrit']), $contexte . ' doit proposer le type d inscrit');
                    association_test_assert_true(isset($saisies['notifier_adherent']), $contexte . ' doit proposer le choix de notification email');
                    association_test_assert_same('on', $saisies['notifier_adherent']['options']['defaut'] ?? '', $contexte . ' doit notifier par defaut');
                    association_test_assert_same(
                        "@select_type_inscrit@=='membre'",
                        $saisies['membre']['options']['afficher_si'] ?? '',
                        $contexte . ' doit conditionner la liste des membres'
                    );
                    association_test_assert_same(
                        "@select_type_inscrit@=='non_membre'",
                        $saisies['non_membre']['options']['afficher_si'] ?? '',
                        $contexte . ' doit conditionner la liste des non membres'
                    );

                    if (in_array($type_inscrit, array('public', 'membre_reseau_fiafe'), true)) {
                        foreach (array('prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit') as $champ_identite) {
                            association_test_assert_true(isset($saisies[$champ_identite]), $contexte . ' doit afficher ' . $champ_identite);
                        }
                    }

                    if (!$payant) {
                        association_test_assert_true(isset($saisies['nb_inscrits']), $contexte . ' doit porter le nombre de participants');
                        association_test_assert_same(
                            $accompagnants ? 'selection' : 'hidden',
                            $saisies['nb_inscrits']['saisie'] ?? '',
                            $contexte . ' doit utiliser le bon type de champ pour le nombre'
                        );
                        association_test_assert_true(!isset($saisies['categorie']), $contexte . ' ne doit pas proposer de tarif');
                    } elseif (!$accompagnants) {
                        association_test_assert_true(isset($saisies['categorie']), $contexte . ' doit proposer un tarif unique');
                        association_test_assert_true(empty($saisies['categorie']['options']['afficher_si']), $contexte . ' ne doit pas masquer le tarif');
                        association_test_assert_same('oui', $saisies['categorie']['options']['obligatoire'] ?? '', $contexte . ' doit rendre le tarif obligatoire');
                    } else {
                        $tarifs_quantites = array_filter($saisies, function ($saisie, $nom) {
                            return strpos($nom, 'categorie[') === 0;
                        }, ARRAY_FILTER_USE_BOTH);
                        association_test_assert_true(!empty($tarifs_quantites), $contexte . ' doit proposer les quantites par tarif');
                        foreach ($tarifs_quantites as $saisie_tarif) {
                            association_test_assert_true(empty($saisie_tarif['options']['afficher_si']), $contexte . ' ne doit masquer aucune quantite tarifaire');
                        }
                    }

                    association_test_assert_same(
                        $accompagnants,
                        isset($saisies['nom_participants']),
                        $contexte . ' doit afficher les noms uniquement avec accompagnants'
                    );
                }
            }
        },
        'prive_simple_matrice_famille_champs_et_conditions_affichage' => function () {
            $configurations = array(
                'gratuit_sans_accompagnants' => array(false, false),
                'gratuit_avec_accompagnants' => array(false, true),
                'payant_sans_accompagnants' => array(true, false),
                'payant_avec_accompagnants' => array(true, true),
            );

            foreach ($configurations as $scenario => $configuration) {
                list($payant, $accompagnants) = $configuration;
                association_test_reset_env(array(
                    'association_metas' => array('meta_cfg_event_config_accompagnants' => 'membre_famille'),
                    'auteur_session' => array('id_auteur' => 3),
                    'events' => array(1 => array(
                        'payant' => $payant,
                        'accompagnants' => $accompagnants,
                    )),
                ));
                association_test_set_request(array(
                    'id_evenement' => 1,
                    'select_type_inscrit' => 'membre',
                    'membre' => 1,
                ));

                $res = formulaires_inscription_evenement_charger_dist(1);
                $saisies = association_test_collect_saisies_by_name($res['_saisies'] ?? array());
                $contexte = 'membre famille / ' . $scenario;

                association_test_assert_same(
                    ($payant && !$accompagnants) || (!$payant && $accompagnants),
                    isset($saisies['famille']),
                    $contexte . ' doit utiliser la selection famille uniquement quand elle porte le choix du participant'
                );

                if (!$payant) {
                    association_test_assert_true(isset($saisies['nb_inscrits']), $contexte . ' doit porter le nombre de participants');
                } elseif (!$accompagnants) {
                    association_test_assert_true(isset($saisies['categorie']), $contexte . ' doit proposer le tarif du participant unique');
                    association_test_assert_true(empty($saisies['categorie']['options']['afficher_si']), $contexte . ' ne doit pas masquer le tarif unique');
                } else {
                    $tarifs_famille = array_filter($saisies, function ($saisie, $nom) {
                        return strpos($nom, 'categorie[') === 0;
                    }, ARRAY_FILTER_USE_BOTH);
                    association_test_assert_true(!empty($tarifs_famille), $contexte . ' doit proposer un tarif par membre de la famille');
                    foreach ($tarifs_famille as $saisie_tarif) {
                        association_test_assert_same(
                            "@select_type_inscrit@=='membre' || @select_type_inscrit@=='non_membre'",
                            $saisie_tarif['options']['afficher_si'] ?? '',
                            $contexte . ' doit limiter le tarif aux types disposant d un compte'
                        );
                    }
                }

                association_test_assert_true(!isset($saisies['nom_participants']), $contexte . ' ne doit pas doubler la selection famille par une saisie libre');
            }
        },
        'prive_multi_saisies' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            association_test_set_request(array(
                'select_type_inscrit' => 'membre',
                'membre' => 1,
                'famille' => array('adherent', 'conjoint'),
            ));

            $saisies = formulaires_inscription_evenement_multi_saisies(1, '');
            $noms = association_test_collect_names($saisies);
            $contexte = formulaires_inscription_evenement_multi_charger_dist(1, '');
            $noms_etapes = array_column($contexte['_saisies_par_etapes'], 'nom');

            association_test_assert_true(
                in_array('fieldset_type_inscrit', $noms, true)
                || in_array('fieldset_infos_generales', $noms, true),
                'Le multi prive doit contenir l etape de selection du type'
            );
            association_test_assert_true(
                in_array('prenom_adherent', $noms, true) && in_array('prenom_conjoint', $noms, true),
                'Le multi prive doit exposer les saisies famille attendues'
            );
            association_test_assert_true(in_array('commentaire', $noms, true), 'Le multi prive doit garder les modalites finales');
            association_test_assert_true(in_array('notifier_adherent', $noms, true), 'Le multi prive doit proposer le choix de notification email');
			$fichier_langue = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/lang/association_evenements_fr.php');
			association_test_assert_contains("'activite_bouton_notification_active' =>", $fichier_langue, 'Le titre de notification BO doit appartenir a Evenements');
			association_test_assert_contains("'activite_bouton_notification_explication' =>", $fichier_langue, 'L explication de notification BO doit appartenir a Evenements');
            association_test_assert_true(in_array('fieldset_info_supplementaire', $noms_etapes, true), 'Les identites BO doivent partager l etape informations supplementaires du FO');
            association_test_assert_true(!in_array('fieldset_adherent', $noms_etapes, true) && !in_array('fieldset_conjoint', $noms_etapes, true), 'Les participants ne doivent plus devenir des etapes BO separees');
        },
        'prive_multi_selection_auteur_recharge_avant_validation' => function () {
            $script = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/javascript/association-script.js');

            association_test_assert_true($script !== false, 'Le script prive doit etre lisible');
            association_test_assert_contains("'#champ_membre, #champ_non_membre'", $script, 'Le script doit intercepter les deux listes d auteurs');
            association_test_assert_contains("destination.searchParams.set('select_type_inscrit', typeInscrit)", $script, 'Le type d auteur doit etre conserve dans l URL rechargee');
            association_test_assert_contains("window.location.assign(destination.toString())", $script, 'Le changement doit recharger les saisies avant validation');
        },
        'prive_multi_reconstruit_trois_participants_depuis_etape_precedente' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'select_type_inscrit' => 'public',
                    'nb_inscrits' => 3,
                    'prenom_inscrit' => 'Recette',
                    'nom_inscrit' => 'Multi Dev',
                    'email_inscrit' => 'recette@example.test',
                ))),
            ));

            $res = formulaires_inscription_evenement_multi_charger_dist(1, '');
            $noms = association_test_collect_names($res['_saisies']);
            $noms_etapes = array_column($res['_saisies_par_etapes'], 'nom');

            association_test_assert_true(in_array('prenom_inscrit_3', $noms, true), 'La quantite CVT precedente doit generer les trois identites');
            association_test_assert_same(1, count(array_filter($noms_etapes, function ($nom) {
                return $nom === 'fieldset_info_supplementaire';
            })), 'Les trois identites doivent rester dans une seule etape BO');
        },
        'parite_second_participant_public_et_responsable' => function () {
            $scenario = array(
                'association_metas' => array('meta_cfg_event_config_accompagnants' => 'tout'),
                'events' => array(1 => array(
                    'payant' => false,
                    'accompagnants' => true,
                )),
            );

            association_test_reset_env($scenario);
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'nb_inscrits' => 2,
            ));
            $public = formulaires_inscription_evenement_multi_public_charger_dist(1, '');
            $noms_public = association_test_collect_names($public['_saisies']);

            association_test_reset_env(array_merge($scenario, array(
                'auteur_session' => array('id_auteur' => 3),
            )));
            association_test_set_request(array(
                'select_type_inscrit' => 'public',
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'select_type_inscrit' => 'public',
                    'nb_inscrits' => 2,
                ))),
            ));
            $responsable = formulaires_inscription_evenement_multi_charger_dist(1, '');
            $noms_responsable = association_test_collect_names($responsable['_saisies']);
            $etapes_responsable = array_column($responsable['_saisies_par_etapes'], 'nom');

            association_test_assert_true(
                in_array('prenom_inscrit_2', $noms_public, true),
                'Le parcours public doit generer le second participant demande'
            );
            association_test_assert_true(
                in_array('prenom_inscrit_2', $noms_responsable, true),
                'Le parcours responsable doit relire le nombre depuis l etape CVT precedente'
            );
            association_test_assert_same(1, count(array_filter($etapes_responsable, function ($nom) {
                return $nom === 'fieldset_info_supplementaire';
            })), 'Les deux identites du parcours responsable doivent partager une seule etape');
        },
        'prive_multi_modification_resout_contexte_cvt' => function () {
            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
                'db' => array(
                    'spip_asso_activites' => array(
                        501 => array(
                            'id_activite' => 501,
                            'id_evenement' => 1,
                            'id_auteur' => 1,
                            'id_transaction' => 0,
                            'statut' => 'ok',
                            'nombre_inscrits' => 2,
                            'nb_invite' => 1,
                            'participants_json' => '{}',
                            'tarifs_selectionnes' => '',
                            'nom_participants' => 'Jean Dupont, Invite Test',
                            'commentaire' => '',
                            'annotation' => '',
                            'association' => '',
                            'email_inscrit' => 'jean@example.test',
                            'tel_inscrit' => '0600000001',
                            'prenom_inscrit' => 'Jean',
                            'nom_inscrit' => 'Dupont',
                        ),
                    ),
                ),
            ));
            association_test_set_request(array(
                'id_activite' => 501,
            ));

            $res = formulaires_inscription_evenement_multi_charger_dist('', 501);
            $noms = association_test_collect_names($res['_saisies']);
            $navigation = file_get_contents(ASSOCIATION_TEST_PLUGIN_ROOT . '/plugins/association-evenements/prive/squelettes/navigation/editer_asso_activite.html');

            association_test_assert_same(1, $res['id_evenement'], 'La modification doit retrouver son evenement');
            association_test_assert_same(501, $res['id_activite'], 'La modification doit conserver son activite');
            association_test_assert_same('oui', $res['modif'], 'La modification doit propager le marqueur CVT');
            association_test_assert_same('oui', _request('modif'), 'La requete CVT doit rester en modification');
            association_test_assert_true(in_array('id_evenement', $noms, true), 'Le contexte evenement doit rester poste');
            association_test_assert_true(in_array('id_activite', $noms, true), 'Le contexte activite doit rester poste');
            association_test_assert_true(in_array('modif', $noms, true), 'Le mode modification doit rester poste');
            $saisies_par_nom = association_test_collect_saisies_by_name($res['_saisies']);
            association_test_assert_same(
                _T('association_evenements:activite_bouton_notifier_modification_adherent'),
                $saisies_par_nom['notifier_adherent']['options']['label_case'] ?? '',
                'La modification BO doit proposer explicitement l envoi de l email de modification'
            );
            association_test_assert_contains('id_evenement_contexte', $navigation, 'La navigation doit resoudre evenement depuis activite');
        },
        'diagnostic_webmestre_aligne_sur_saisies_bo' => function () {
            association_test_reset_env(array(
                'association_metas' => array(
                    'meta_cfg_event_config_accompagnants' => 'membre_famille',
                    'meta_cfg_event_type_quota' => 'strict',
                ),
                'auteur_session' => array('id_auteur' => 3, 'webmestre' => 'oui'),
                'events' => array(1 => array('payant' => true, 'accompagnants' => true)),
            ));
            association_test_set_request(array(
                'id_evenement' => 1,
                'select_type_inscrit' => 'membre',
                'membre' => 1,
            ));

            $res = formulaires_inscription_evenement_charger_dist(1);
            $diagnostic = $res['diagnostic_inscription'] ?? array();
            $noms_reels = association_test_collect_names($res['_saisies'] ?? array());
            $noms_diagnostic = array_keys($diagnostic['contexte']['champs_generes'] ?? array());

            sort($noms_reels, SORT_STRING);
            sort($noms_diagnostic, SORT_STRING);
            association_test_assert_true(!empty($diagnostic), 'Le webmestre doit recevoir le diagnostic commun');
            association_test_assert_same($noms_reels, $noms_diagnostic, 'Le diagnostic doit inventorier exactement les saisies CVT generees');
            association_test_assert_contains('IE1-BO-CR-', $diagnostic['code'] ?? '', 'Le code doit identifier le BO en creation');
            association_test_assert_contains('-FA1-ME0-PA1-AC1-', $diagnostic['code'] ?? '', 'Le code doit suivre famille, parcours, paiement et accompagnants effectifs');
            association_test_assert_contains('-LC05-LE05-PD005-AE002-PS0', $diagnostic['code'] ?? '', 'Le code BO doit distinguer limites configuree et effective sans appliquer le profil operateur');
            association_test_assert_same(false, $diagnostic['regles_effectives']['profil_session_applique'] ?? null, 'Le detail doit confirmer que le profil operateur est sans effet');
            association_test_assert_true(isset($diagnostic['contexte']['conditions_affichage']['membre']), 'Le diagnostic doit conserver afficher_si du membre');

            association_test_reset_env(array(
                'auteur_session' => array('id_auteur' => 3),
            ));
            $res_non_webmestre = formulaires_inscription_evenement_charger_dist(1);
            association_test_assert_true(!isset($res_non_webmestre['diagnostic_inscription']), 'Le diagnostic ne doit pas etre expose aux non webmestres');
        },
        'public_simple_anonyme_payant' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'email' => 'visiteur@example.test',
            ), false);

            $res = formulaires_inscription_evenement_public_charger_dist(1);
            $noms = association_test_collect_names($res['_saisies']);

            association_test_assert_same('oui', $res['creation_inscription'], 'Le formulaire public doit etre en mode creation');
            association_test_assert_true(in_array('prenom_inscrit', $noms, true), 'Le public simple doit demander le prenom');
            association_test_assert_true(in_array('page_cgu', $noms, true), 'Les modalites CGU doivent etre injectees');
            association_test_assert_true(in_array('condition_inscription', $noms, true), 'La condition d inscription doit etre presente');
        },
        'public_simple_tarif_sans_preselection' => function () {
            association_test_reset_env(array(
                'events' => array(
                    1 => array(
                        'payant' => true,
                        'accompagnants' => false,
                    ),
                ),
            ));
            $res = formulaires_inscription_evenement_public_charger_dist(1);
            $trouver_saisie = function ($saisies, $nom) use (&$trouver_saisie) {
                foreach ((array)$saisies as $saisie) {
                    if (!is_array($saisie)) {
                        continue;
                    }
                    if (($saisie['options']['nom'] ?? '') === $nom) {
                        return $saisie;
                    }
                    if (!empty($saisie['saisies'])) {
                        $trouvee = $trouver_saisie($saisie['saisies'], $nom);
                        if ($trouvee) {
                            return $trouvee;
                        }
                    }
                }
                return null;
            };
            $saisie_tarif = $trouver_saisie($res['_saisies'], 'categorie');

            association_test_assert_true(is_array($saisie_tarif), 'Le champ tarif doit etre present');
            association_test_assert_same('', $saisie_tarif['options']['defaut'], 'Le tarif initial doit rester vide');
            association_test_assert_same(0, $saisie_tarif['options']['cacher_option_intro'], 'Le choix vide doit rester visible');
            association_test_assert_same('oui', $saisie_tarif['options']['obligatoire'], 'Le choix du tarif doit etre obligatoire');
        },
        'public_multi_connecte_famille' => function () {
            association_test_reset_env(array(
                'visiteur_session' => association_test_default_scenario()['db']['spip_auteurs'][1],
            ));
            association_test_set_request(array(
                'famille' => array('adherent', 'conjoint'),
            ));

            $res = formulaires_inscription_evenement_multi_public_charger_dist(1, '');
            $saisies = formulaires_inscription_evenement_multi_public_saisies(1, '');
            $noms = association_test_collect_names($saisies);

            association_test_assert_same('oui', $res['creation_inscription'], 'Le multi public doit etre en creation');
            association_test_assert_true(in_array('famille', $noms, true), 'Le multi public connecte doit proposer la famille');
            association_test_assert_true(in_array('commentaire', $noms, true), 'Le multi public doit contenir le commentaire');
        },
        'public_multi_recapitulatif_conserve_les_saisies_dynamiques' => function () {
            association_test_reset_env();
            association_test_set_request(array(
                'cvtm_prev_post' => base64_encode(serialize(array(
                    'nb_inscrits' => 2,
                    'prenom_inscrit_1' => 'Nina',
                    'nom_inscrit_1' => 'Libre',
                    'categorie_inscrit_1' => 3,
                    'prenom_inscrit_2' => 'Lia',
                    'nom_inscrit_2' => 'Libre',
                    'categorie_inscrit_2' => 3,
                ))),
            ));

            $saisies = formulaires_inscription_evenement_multi_public_saisies(1, '');
            $noms = association_test_collect_names($saisies);

            association_test_assert_true(in_array('prenom_inscrit_2', $noms, true), 'Le chargeur du recapitulatif doit redeclarer le second participant');
            association_test_assert_true(in_array('categorie_inscrit_2', $noms, true), 'Le tarif du second participant doit rester dans le prochain jeton CVT');
        },
    ));
}

if (association_test_is_direct_script(__FILE__)) {
    exit(association_test_run_charger_suite());
}
