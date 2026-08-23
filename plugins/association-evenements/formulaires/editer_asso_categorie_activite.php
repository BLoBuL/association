<?php
    /**
     * Gestion du formulaire d'édition des catégories d'activités
     */

    include_spip('inc/saisies');

    /**
     * Déclaration des saisies du formulaire
     *
     * @return array Tableau des saisies
     */
    function formulaires_editer_asso_categorie_activite_saisies_dist() {
        return [
            [
                'saisie' => 'input',
                'options' => [
                    'nom' => 'valeur',
                    'label' => _T('association_evenements:nom_participation'),
                    'obligatoire' => 'oui',
                    'size' => 40
                ]
            ],
            [
                'saisie' => 'selection',
                'options' => [
                    'nom' => 'statut',
                    'label' => _T('association_evenements:edit_participation_statut'),
                    'datas' => [
                        'ok' => _T('association_evenements:participation_active'),
                        'desactive' => _T('association_evenements:participation_desactive')
                    ],
                    'defaut' => 'ok'
                ]
            ],
            [
                'saisie' => 'selection',
                'options' => [
                    'nom' => 'type_inscrit',
                    'label' => _T('association_evenements:label_type_inscrit_participation'),
                    'explication' => _T('association_evenements:explication_type_inscrit_participation'),
                    'datas' => [
                        'indifferent' => _T('association_evenements:participation_indifferent'),
                        'adherent' => _T('association_evenements:participation_adherent'),
                        'couple' => _T('association_evenements:participation_couple'),
                        'non_adherent' => _T('association_evenements:participation_non_adherent'),
                        'enfant' => _T('association_evenements:participation_enfant'),
                        'invite' => _T('association_evenements:participation_invite'),
                        'special' => _T('association_evenements:participation_special'),
                        'benevole' => _T('association_evenements:participation_benevole')
                    ],
                    'defaut' => 'indifferent'
                ]
            ],
            [
                'saisie' => 'radio',
                'options' => [
                    'nom' => 'type_tarif',
                    'label' => _T('association_evenements:type_tarif_participation'),
                    'datas' => [
                        'individuel' => _T('association_evenements:tarif_individuel_participation'),
                        'groupe' => _T('association_evenements:tarif_groupe_participation')
                    ],
                    'defaut' => 'individuel',
                    // Couple = groupe 2 par définition : inutile de choisir
                    'afficher_si' => '@type_inscrit@ != "couple"'
                ]
            ],
            [
                'saisie' => 'selection',
                'options' => [
                    'nom' => 'quantite',
                    'label' => _T('association_evenements:nombre_de_personne'),
                    'datas' => array_combine(range(1, 25), range(1, 25)),
                    'defaut' => '1',
                    // Visible si groupe ET pas couple (couple force 2 automatiquement)
                    'afficher_si' => '@type_tarif@ == "groupe" && @type_inscrit@ != "couple"'
                ]
            ],
            // TODO: Gérer le quota maximum si nécessaire
/*            [
                'saisie' => 'input',
                'options' => [
                    'nom' => 'quota_max',
                    'label' => _T('association_evenements:quota_maximum'),
                    'explication' => _T('association_evenements:explication_quota_maximum'),
                    'type' => 'number',
                    'min' => 0,
                    'size' => 5
                ]
            ],*/
            [
                'saisie' => 'textarea',
                'options' => [
                    'nom' => 'commentaire',
                    'label' => _T('association_evenements:explication_participation'),
                    'rows' => 3,
                    'cols' => 80
                ]
            ]
        ];
    }

    /**
     * Chargement des valeurs par défaut du formulaire
     */
    function formulaires_editer_asso_categorie_activite_charger_dist($id_categorie = 'new', $retour = '') {
        $valeurs = [];

        // Si on édite une catégorie existante
        if (is_numeric($id_categorie) && $id_categorie > 0) {
            $categorie = sql_fetsel('*', 'spip_asso_categories_activites', 'id_categorie=' . intval($id_categorie));

            if ($categorie) {
                $valeurs = [
                    'id_categorie' => $id_categorie,
                    'valeur' => $categorie['valeur'],
                    'type_inscrit' => $categorie['type_inscrit'] ?: 'indifferent',
                    'type_tarif' => ($categorie['type_inscrit'] === 'couple' || $categorie['quantite'] > 1) ? 'groupe' : 'individuel',
                    'quantite' => isset($categorie['quantite']) ? $categorie['quantite'] : 1,
                    //'quota_max' => $categorie['quota_max'] ?: 0,
                    'commentaire' => $categorie['commentaires'],
                    'statut' => $categorie['statut'] ?: 'ok'
                ];
            }
        } else {
            // Valeurs par défaut pour une nouvelle catégorie
            $valeurs = [
                'id_categorie' => 'new',
                'valeur' => '',
                'type_inscrit' => 'indifferent',
                'type_tarif' => 'individuel',
                'quantite' => 1,
                //'quota_max' => 0,
                'commentaire' => '',
                'statut' => 'ok'
            ];
        }


        return $valeurs;
    }

    /**
     * Traitement des valeurs du formulaire
     */
    function formulaires_editer_asso_categorie_activite_traiter_dist($id_categorie = 'new', $retour = '') {
        $res = ['editable' => true];

        // Validation du valeur
        if (!_request('valeur')) {
            $res['message_erreur'] = _T('association_evenements:erreur_valeur_obligatoire');
            return $res;
        }

        // Couple = groupe de 2 par définition
        $type_inscrit = _request('type_inscrit') ?: 'indifferent';
        $type_tarif = _request('type_tarif') ?: 'individuel';
        $quantite = 1;
        if ($type_inscrit === 'couple') {
            $quantite = 2;
        } elseif ($type_tarif == 'groupe') {
            $quantite = intval(_request('quantite'));
            if ($quantite < 1) {
                $quantite = 1;
            }
        }

        // Préparation des données à insérer/modifier
        $set = [
            'valeur' => _request('valeur'),
            'type_inscrit' => _request('type_inscrit'),
            'quantite' => $quantite,
            //'quota_max' => intval(_request('quota_max')),
            'commentaires' => _request('commentaire'),
            'statut' => _request('statut')
        ];

        // Modification d'une catégorie existante
        if (is_numeric($id_categorie) && $id_categorie > 0) {
            sql_updateq('spip_asso_categories_activites', $set, 'id_categorie=' . intval($id_categorie));
            $res['message_ok'] = _T('association_evenements:categorie_activite_modifiee');

        }
        // Création d'une nouvelle catégorie
        else {
            $id_categorie = sql_insertq('spip_asso_categories_activites', $set);
            $res['message_ok'] = _T('association_evenements:categorie_activite_creee');
        }


        $res['redirect'] = $retour ?: generer_url_ecrire('categories_activites');


        return $res;
    }
