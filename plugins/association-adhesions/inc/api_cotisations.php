<?php
/**
 * API commune pour le traitement des cotisations
 *
 * Centralise la logique de traitement des cotisations pour éviter
 * la duplication entre l'interface publique et privée.
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/comptes');
include_spip('inc/cotisations');
include_spip('inc/cotisations_stockage');
include_spip('inc/bank');
include_spip('inc/cotisations_devises');
include_spip('inc/fonctions/association_validite_calculator');

/**
 * Génère les saisies communes aux formulaires de cotisation (public et privé)
 *
 * @param array $params Paramètres pour générer les saisies
 *   id_auteur       - ID de l'auteur concerné (obligatoire)
 *   id_compte       - ID du compte pour modification, 'new' pour création
 *   inscription     - Type d'inscription ('inscription' ou 'reinscription')
 *   type_adherent   - Type d'adhérent pour filtrer les catégories (optionnel)
 *   origine        - 'public' ou 'prive' pour adapter certains champs
 *   nom_prenom      - Nom et prénom de l'adhérent (pour textes par défaut)
 *
 * @return array Les saisies communes aux formulaires
 */
function api_cotisations_saisies_communes($params)
{
    // Paramètres par défaut et extraction
    $id_auteur = intval($params['id_auteur']);
    $query_auteur = sql_fetsel('nom_famille, prenom,statut_interne', 'spip_auteurs', 'id_auteur=' . $id_auteur);

    $id_compte = isset($params['id_compte']) ? $params['id_compte'] : 'new';
    if (isset($params['reinscription']) && $params['reinscription']) {
        $reinscription = $params['reinscription'];
    }else{
        $reinscription = $query_auteur['statut_interne'] == 'prospect' ? 'inscription' : 'reinscription';
    }
    $type_adherent = isset($params['type_adherent']) ? $params['type_adherent'] : '';
    $origine = isset($params['origine']) ? $params['origine'] : 'prive';

    $label_fieldset = $params['label_fieldset'] ?? '<:association:form_cotisation_fieldset:>';
    $explication_fieldset = $params['explication_fieldset'] ?? '<:association:form_cotisation_explication:>';
    $form_cotisation_categorie_label = $params['form_cotisation_categorie_label'] ?? '<:association:form_cotisation_categorie_label:>';

    // Vérification de l'ID auteur
    if (!$id_auteur || !is_numeric($id_auteur)) {
        return array();
    }
    // Liste des catégories nécessitant un justificatif
    $categories_cotisation_justificatif = identifier_categories_necessite_justificatif(true);
    $liste_categorie_cotisation_justificatif = empty($categories_cotisation_justificatif)
        ? '"0"'
        : '"' . implode(',', $categories_cotisation_justificatif) . '"';

    // Préparation des catégories selon le origine
    // Passer le type_adherent (s'il est fourni) à la fonction de préparation afin
    // de filtrer correctement les catégories (notamment pour exclure l'option
    // 'entreprise' quand le type d'adhérent est connu).
    $categories = preparer_liste_categories($id_auteur, $origine, $reinscription, $type_adherent);

    // Si une seule catégorie est disponible, on la sélectionne par défaut
    $defaut_categorie = ($categories && count($categories) == 1) ? key($categories) : '';

    $categorie_unique_avec_justificatif = (
        $defaut_categorie !== ''
        && count($categories) === 1
        && in_array(intval($defaut_categorie), array_map('intval', $categories_cotisation_justificatif), true)
    );
    $afficher_si_document_justificatif = $categorie_unique_avec_justificatif
        ? ''
        : '@id_categorie@ IN ' . $liste_categorie_cotisation_justificatif;


// Construction des saisies communes
    $saisies = [];
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'form_cotisation_fieldset',
            'label' => $label_fieldset,
            'explication' => $explication_fieldset,
        ),
        'saisies' => array(
            // Champ caché pour l'identifiant du compte (nouveau ou existant)
            array(
                'saisie' => 'hidden',
                'options' => array(
                    'nom' => 'id_compte',
                    'defaut' => $id_compte,
                )
            ),
            // Champ caché pour l'identifiant de l'auteur
            array(
                'saisie' => 'hidden',
                'options' => array(
                    'nom' => 'id_auteur',
                    'defaut' => $id_auteur,
                )
            ),
            array(
                'saisie' => 'radio',
                'options' => array(
                    'label' => $form_cotisation_categorie_label,
                    'nom' => 'id_categorie',
                    'data' => $categories,
                    'cacher_option_intro' => true,
                    'defaut' => $defaut_categorie,
                ),
            ),
        ),
    );

    if ($id_compte === 'new' OR !$id_compte) {
        $options_justificatif = array(
            'nom' => 'document_justificatif',
            'label' => '<:association:form_cotisation_justificatif_label:>',
            'explication' => '<:association:form_cotisation_justificatif_explication:>',
            'nb_fichiers' => 3,
        );
        if ($afficher_si_document_justificatif !== '') {
            $options_justificatif['afficher_si'] = $afficher_si_document_justificatif;
        }
        $saisies[] = array(
            'saisie' => 'fichiers',
            'options' => $options_justificatif,
        );
    }

    return $saisies;
}
/**
 * Traite la création ou la modification d'une cotisation
 *
 * @param array $params Paramètres pour le traitement
 *   id_auteur         - ID de l'auteur (obligatoire)
 *   id_compte         - ID du compte (ou 'new' pour création)
 *   id_categorie      - ID de la catégorie d'adhérent
 *   justification     - Justification de la cotisation
 *   inscription       - Type d'inscription ('inscription' ou 'reinscription')
 *   statut_cotisation  - Statut de la cotisation
 *   notification      - Notification à envoyer (true/false)
 *   montant_don       - Montant du don (si applicable)
 *
 * @return array Résultat du traitement
 */
function api_traiter_cotisation($params) {
    try {
        // Vérification des paramètres obligatoires
        if (!isset($params['id_auteur']) || !intval($params['id_auteur'])) {
            return ['statut' => 'erreur', 'message' => 'ID auteur invalide'];
        }

        // Paramètres par défaut et extraction
        $id_auteur = intval($params['id_auteur']);
        $query_auteur = sql_fetsel('statut_interne', 'spip_auteurs', 'id_auteur=' . $id_auteur);
        $id_compte = isset($params['id_compte']) ? $params['id_compte'] : 'new';
        $id_categorie = intval($params['id_categorie']);
        $justification = trim((string) ($params['justification'] ?? ''));
        if ($justification === '') {
            $justification = _T('association:justification_encaissement_cotisation', array(
                'nom_prenom' => trim((string) ($params['nom_prenom'] ?? '')),
                'id_auteur' => $id_auteur,
            ));
        }
        if (isset($params['reinscription']) && $params['reinscription']) {
            $reinscription = $params['reinscription'];
        }else{
            $reinscription = $query_auteur['statut_interne'] == 'prospect' ? 'inscription' : 'reinscription';
        }
        $montant_don = isset($params['montant_don']) ? floatval($params['montant_don']) : 0;
        $statut_cotisation = isset($params['statut_cotisation']) ? $params['statut_cotisation'] : false;
        $origine = isset($params['origine']) ? $params['origine'] : '';
        $notifier = isset($params['notifier']) ? true : false;
        // Date et paramètres comptables
        $date = date('Y-m-d H:i:s');
        $journal = lire_config('/association_metas/pc_cotisations_creance') ?: '101';
        $imputation = lire_config('/association_metas/pc_cotisations_creance') ?: '101';

        // Récupération des informations de la catégorie
        $query_categories = sql_fetsel('*', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
        if (!$query_categories) {
            return ['statut' => 'erreur', 'message' => 'Catégorie inexistante'];
        }

        // La contrainte documentaire est une règle métier : elle doit être
        // contrôlée ici, y compris pour un appel direct à l'API sans formulaire.
        $fichiers_requete = function_exists('_request') ? _request('_fichiers') : array();
        $fichiers_justificatifs = $params['documents'] ?? ($fichiers_requete ?: ($_FILES ?? array()));
        $controle_documents = cotisation_verifier_documents_justificatifs(
            $id_categorie,
            $fichiers_justificatifs,
            $id_compte
        );
        if (!$controle_documents['valide']) {
            return ['statut' => 'erreur', 'message' => $controle_documents['message']];
        }

        $montant_choisi = floatval($query_categories['cotisation']);
        $devise = association_cotisation_resoudre_devise($query_categories['devise'] ?? '');
        $validation_categorie = $query_categories['validation'];
        $type_adherent_categorie = $query_categories['type_adherent'];

        // Ajout du don si applicable
        if ($montant_choisi < 0 || $montant_don < 0) {
            return ['statut' => 'erreur', 'message' => 'Montant de cotisation invalide'];
        }
        $montant_final = $montant_choisi + $montant_don;
        $cotisation_gratuite = ($montant_final === 0.0);

        // Calcul du montant HT si une taxe est configurée
        $pourcentage_taxe = lire_config('/association_metas/meta_cfg_taxe') ?: false;
        $montant_ht = $pourcentage_taxe
            ? $montant_final - (($montant_final / 100) * $pourcentage_taxe)
            : $montant_final;

        // Logique différente pour ajout ou modification
        if ($id_compte == 'new') {
            // AJOUT D'UNE NOUVELLE COTISATION

            // Attribution du statut de cotisation selon la config de validation de la catégorie (auto,pre-paiement,post-paiement)
            // Si un statut explicite est fourni dans les paramètres, on le respecte.
            // Sinon on calcule automatiquement en se basant sur la configuration de la catégorie,
            // quel que soit le formulaire (public ou privé).
            if (isset($params['statut_cotisation']) && $params['statut_cotisation']) {
                $statut_cotisation = $params['statut_cotisation'];
            } else {
                // Normaliser la valeur de validation pour tolérer plusieurs variantes
                $val = strtolower(trim($validation_categorie));
                $val = str_replace(array('_', ' '), '-', $val);

                // Mapping simple : seules les valeurs 'pre-paiement', 'post-paiement' et 'auto' sont prises en charge
                if ($cotisation_gratuite) {
                    // Sans montant à encaisser, il ne doit jamais rester une étape
                    // de paiement. Le mode auto active directement l’adhésion ; les
                    // modes avec validation restent soumis à une décision humaine.
                    $statut_cotisation = ($val === 'auto') ? 'ok' : 'demande';
                } elseif ($val === 'pre-paiement') {
                    $statut_cotisation = 'demande';
                } elseif ($val === 'post-paiement' || $val === 'auto') {
                    $statut_cotisation = 'attente';
                } else {
                    // Par défaut, on met en attente
                    $statut_cotisation = 'attente';
                }
            }
            $id_transaction = 0;
            if (!$cotisation_gratuite) {
                // Une transaction Bank n’existe que lorsqu’un montant doit être encaissé.
                $inserer_transaction = charger_fonction('inserer_transaction', 'bank');
                $id_transaction = $inserer_transaction($montant_final, [
                    'id_auteur' => $id_auteur,
                    'montant_ht' => $montant_ht,
                    'devise' => $devise,
                    'force' => true
                ]);

                // Si le helper renvoie un tableau (format erreur possible), propager
                if (is_array($id_transaction)) {
                    if (!empty($id_transaction['message'])) {
                        return ['statut' => 'erreur', 'message' => $id_transaction['message']];
                    }
                    if (!empty($id_transaction['statut']) && $id_transaction['statut'] === 'erreur') {
                        return ['statut' => 'erreur', 'message' => $id_transaction['message'] ?? 'Erreur création transaction'];
                    }
                    return ['statut' => 'erreur', 'message' => 'Erreur création transaction (détails: ' . substr(var_export($id_transaction, true), 0, 200) . ')'];
                }
                if (!is_numeric($id_transaction) || intval($id_transaction) <= 0) {
                    return ['statut' => 'erreur', 'message' => 'Erreur création transaction'];
                }
            }

            // Création de la cotisation dans les comptes
            $id_compte = compte_cotisation(
                $date,
                $montant_final,
                $justification,
                $imputation,
                $journal,
                $id_auteur,
                $reinscription,
                $id_categorie,
                $statut_cotisation,
                $id_transaction
            );

            // Propagation d'erreur si le helper renvoie un tableau
            if (is_array($id_compte)) {
                if (!empty($id_compte['message'])) {
                    return ['statut' => 'erreur', 'message' => $id_compte['message']];
                }
                return ['statut' => 'erreur', 'message' => 'Erreur création compte cotisation (détails: ' . substr(var_export($id_compte, true), 0, 200) . ')'];
            }
            if (!is_numeric($id_compte) || intval($id_compte) <= 0) {
                return ['statut' => 'erreur', 'message' => 'Erreur création compte cotisation'];
            }


        } else {
            // MODIFICATION D'UNE COTISATION EXISTANTE
            $query_cotisation = association_cotisation_lire_par_compte($id_compte);
            if($origine== 'encaissement_paiement'){
                if($validation_categorie == 'post-paiement') {
                    $statut_cotisation = 'demande';
                }else{
                    $statut_cotisation = 'ok';
                }
            }else{
                // Si la cotisation n'est pas validée, on la met à jour
                $statut_cotisation = $params['statut_cotisation'];
            }

            // Récupération de l'ID de transaction associée à ce compte
            $id_transaction = intval($query_cotisation['id_transaction'] ?? 0);

            if (!$id_transaction) {
                return ['statut' => 'erreur', 'message' => 'Transaction inexistante'];
            }

            // Mise à jour de la transaction avec le nouveau montant
            sql_updateq('spip_transactions', [
                'montant'     => $montant_final,
                'montant_ht'  => $montant_ht,
                'devise'      => $devise,
            ], "id_transaction=$id_transaction");

            // Modification de la cotisation dans les comptes
            modifier_compte_cotisation(
                $date,
                $montant_final,
                $justification,
                $imputation,
                $journal,
                $reinscription,
                $id_categorie,
                $id_compte,
                $statut_cotisation,
                $id_transaction
            );
        }
        // Traitement des documents si fournis
        $documents = array();
        if (isset($params['documents']) && is_array($params['documents'])) {
            $documents = traiter_upload_justificatif($id_auteur, $id_compte, $params['documents']);
        } elseif (isset($_FILES) && !empty($_FILES)) {
            $documents = traiter_upload_justificatif($id_auteur, $id_compte);
        }
        if (!empty($query_categories['document_justificatif'])
            && $query_categories['document_justificatif'] === 'oui'
            && $id_compte
            && count($documents) < $controle_documents['nouveaux_requis']) {
            association_log('cotisations', 'Echec de liaison des justificatifs au compte ' . intval($id_compte), 'erreur');
            return ['statut' => 'erreur', 'message' => _T('association:erreur_justificatif_enregistrement')];
        }
        // Gestion de la notification et de l'activation des privilèges
        changer_statut_cotisation($id_compte, $origine, $notifier);

        return [
            'statut_cotisation' => $statut_cotisation,
            'id_compte' => $id_compte,
            'id_transaction' => $id_transaction,
            'id_auteur' => $id_auteur,
            'montant' => $montant_final,
            'reinscription' => $reinscription
        ];

    } catch (Throwable $e) {
        association_log('cotisations', 'api_traiter_cotisation: exception: ' . $e->getMessage(), 'erreur');
        return ['statut' => 'erreur', 'message' => 'Erreur interne: ' . $e->getMessage()];
    }
}

/**
 * Normalise les différentes structures d'upload PHP/SPIP en une liste de fichiers.
 */
function cotisation_normaliser_documents_justificatifs($files) {
    $resultat = array();
    $parcourir = function ($noeud) use (&$parcourir, &$resultat) {
        if (!is_array($noeud)) return;
        // cvt-upload conserve les fichiers entre deux passes CVT avec les cles
        // mime/taille et sans cle error. Accepter cette forme au meme titre que
        // la structure native de $_FILES.
        $est_fichier = array_key_exists('name', $noeud)
            && (array_key_exists('error', $noeud) || array_key_exists('tmp_name', $noeud));
        if ($est_fichier) {
            if (is_array($noeud['name'])) {
                foreach (array_keys($noeud['name']) as $i) {
                    $parcourir(array(
                        'name' => $noeud['name'][$i] ?? '',
                        'type' => $noeud['type'][$i] ?? ($noeud['mime'][$i] ?? ''),
                        'tmp_name' => $noeud['tmp_name'][$i] ?? '',
                        'error' => $noeud['error'][$i] ?? UPLOAD_ERR_OK,
                        'size' => $noeud['size'][$i] ?? ($noeud['taille'][$i] ?? 0),
                    ));
                }
            } else {
                $resultat[] = array(
                    'name' => $noeud['name'] ?? '',
                    'type' => $noeud['type'] ?? ($noeud['mime'] ?? ''),
                    'tmp_name' => $noeud['tmp_name'] ?? '',
                    'error' => $noeud['error'] ?? UPLOAD_ERR_OK,
                    'size' => $noeud['size'] ?? ($noeud['taille'] ?? 0),
                );
            }
            return;
        }
        foreach ($noeud as $enfant) $parcourir($enfant);
    };
    $parcourir($files);
    return $resultat;
}

/**
 * Convertit une liste normalisée en structure d'upload PHP acceptée par
 * ajouter_documents(). SPIP peut fournir _fichiers sous forme d'arbre nommé,
 * alors que certains formulaires transmettent directement la liste aplatie.
 */
function cotisation_documents_vers_upload($files) {
    $normalises = cotisation_normaliser_documents_justificatifs($files);
    if (!$normalises) return array();

    // action/ajouter_documents attend une liste de descripteurs, un tableau
    // complet par fichier, et non la structure en colonnes de $_FILES.
    $upload = array();
    foreach ($normalises as $fichier) {
        $upload[] = array(
            'name' => $fichier['name'] ?? '',
            'type' => $fichier['type'] ?? '',
            'tmp_name' => $fichier['tmp_name'] ?? '',
            'error' => $fichier['error'] ?? UPLOAD_ERR_NO_FILE,
            'size' => $fichier['size'] ?? 0,
        );
    }
    return $upload;
}

/**
 * Valide les justificatifs avant toute création de compte ou transaction Bank.
 * La configuration existante reste la source de vérité : deux fichiers sont
 * exigés uniquement lorsque document_justificatif vaut "oui".
 */
function cotisation_verifier_documents_justificatifs($id_categorie, $files = array(), $id_compte = 'new') {
    $categorie = sql_fetsel('document_justificatif', 'spip_asso_categories_adherents', 'id_categorie=' . intval($id_categorie));
    $obligatoire = (($categorie['document_justificatif'] ?? 'non') === 'oui');
    $minimum = $obligatoire ? 2 : 0;
    $existants = 0;
    if ($obligatoire && is_numeric($id_compte) && intval($id_compte) > 0) {
        $existants = intval(sql_countsel('spip_documents_liens', "objet='compte' AND id_objet=" . intval($id_compte)));
    }

    $valides = 0;
    $extensions = array('pdf', 'jpg', 'jpeg', 'png');
    $mimes = array('application/pdf', 'image/jpeg', 'image/png');
    foreach (cotisation_normaliser_documents_justificatifs($files) as $fichier) {
        $erreur = intval($fichier['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($erreur === UPLOAD_ERR_NO_FILE || trim((string)($fichier['name'] ?? '')) === '') continue;
        if ($erreur !== UPLOAD_ERR_OK || intval($fichier['size'] ?? 0) < 1) {
            return array('valide' => false, 'message' => _T('association:erreur_justificatif_upload'), 'nouveaux_requis' => max(0, $minimum - $existants));
        }
        if (intval($fichier['size']) > 10 * 1024 * 1024) {
            return array('valide' => false, 'message' => _T('association:erreur_justificatif_taille'), 'nouveaux_requis' => max(0, $minimum - $existants));
        }
        $extension = strtolower(pathinfo((string)$fichier['name'], PATHINFO_EXTENSION));
        $mime = strtolower(trim((string)($fichier['type'] ?? '')));
        $tmp = (string)($fichier['tmp_name'] ?? '');
        if ($tmp !== '' && is_file($tmp) && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime_detecte = finfo_file($finfo, $tmp);
                finfo_close($finfo);
                if ($mime_detecte) $mime = strtolower($mime_detecte);
            }
        }
        if (!in_array($extension, $extensions, true) || ($mime !== '' && !in_array($mime, $mimes, true))) {
            return array('valide' => false, 'message' => _T('association:erreur_justificatif_format'), 'nouveaux_requis' => max(0, $minimum - $existants));
        }
        $valides++;
    }

    $nouveaux_requis = max(0, $minimum - $existants);
    if ($valides < $nouveaux_requis) {
        return array('valide' => false, 'message' => _T('association:erreur_justificatif_minimum', array('nb' => $minimum)), 'nouveaux_requis' => $nouveaux_requis);
    }
    return array('valide' => true, 'message' => '', 'nouveaux_requis' => $nouveaux_requis, 'valides' => $valides, 'existants' => $existants);
}

/**
 * Prépare la liste des catégories d'adhérents selon l'origine
 *
 * @param string $type_adherent Type d'adhérent pour filtrer
 * @param string $origine 'public' ou 'prive'
 * @return array Liste des catégories formatées
 */
function preparer_liste_categories($id_auteur, $origine = 'prive', $inscription = 'inscription', $type_adherent = '') {
    include_spip('inc/bank');
    $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
    $type_adherent_auteur = $query_auteur['type_adherent'] ?? $type_adherent;
    // Critère de sélection différent selon l'origine
    $where = $origine == 'public' ? 'statut="ok"' : 'statut!="supprime"';

    $query_categories = sql_select('*', 'spip_asso_categories_adherents', $where, '', 'cotisation DESC');
    $categories = $categories_tous = array();
    $found_exact_match = false;

    while ($row = sql_fetch($query_categories)) {
        $id_categorie = $row['id_categorie'];
        $commentaire = $row['commentaires'] ? '&nbsp;<br>' . $row['commentaires'] : '';
        $devise = association_cotisation_resoudre_devise($row['devise'] ?? '');
        $valeur = '<b>' .$row['cotisation'] . '&nbsp;' . $devise . '&nbsp;-&nbsp;' . $row['valeur'] . '</b>' . $commentaire;

        // Continuer seulement si l'inscription correspond à l'éligibilité de la catégorie
        if (!($inscription == $row['eligibilite'] || $row['eligibilite'] == 'tout' || $row['eligibilite'] == '')) {
            continue;
        }

        // On garde uniquement les catégories qui correspondent au nombre d'enfants
        if (
            ($query_auteur['radio_type_adherent'] ?? '') === 'enfant'
            && ($row['type_adherent'] ?? '') === 'enfant'
            && intval($query_auteur['nb_enfants'] ?? 0) > 0
        ) {
            // Cas oÃ¹ une catégorie correspond exactement au nombre d'enfants
            if ($row['nombre_enfants'] == $query_auteur['nb_enfants']) {
                if (!$found_exact_match) {
                    $found_exact_match = true;
                }
                // Réinitialiser les catégories pour ne garder que celle-ci
                $categories = array($id_categorie => $valeur);

                // On a trouvé la correspondance exacte, on peut sortir de la boucle
                break;
            }
            // Si on n'a pas encore trouvé de correspondance exacte, on conserve les catégories compatibles
            elseif ($row['nombre_enfants'] > 0 && $row['nombre_enfants'] <= $query_auteur['nb_enfants'] || $row['nombre_enfants'] == 0) {
                $categories[$id_categorie] = $valeur;
            }
        }

        // Si un type d'adhérent est spécifié, ne garder que les catégories correspondantes
        if (!$type_adherent_auteur || $type_adherent_auteur == $row['type_adherent']) {
            $categories[$id_categorie] = $valeur;
        }

        $categories_tous[$id_categorie] = $valeur;
    }

    return !empty($categories) ? $categories : $categories_tous;
}
/**
 * Identifie les catégories d'adhérents nécessitant un justificatif.
 *
 * Cette fonction interroge la base de données pour récupérer les identifiants
 * des catégories d'adhérents qui nécessitent un document justificatif.
 * Elle retourne ces identifiants sous forme de chaÃ®ne formatée pour Ãªtre utilisée
 * dans des conditions d'affichage (afficher_si).
 *
 * @param bool $as_array Si true, retourne un tableau d'IDs au lieu d'une chaÃ®ne
 * @return mixed Une chaÃ®ne formatée pour afficher_si ou un tableau d'IDs
 */
function identifier_categories_necessite_justificatif($as_array = false) {
    // Récupération des catégories nécessitant un justificatif
    $categories = sql_allfetsel(
        'id_categorie',
        'spip_asso_categories_adherents',
        'document_justificatif = "oui"'
    );

    // Extraction des IDs
    $ids = array_column($categories, 'id_categorie');

    // Retourne soit un tableau, soit une chaÃ®ne formatée pour afficher_si
    if ($as_array) {
        return $ids;
    } else {
        return empty($ids) ? '"0"' : '"' . implode(',', $ids) . '"';
    }
}
/**
 * Traite les fichiers justificatifs téléchargés
 *
 * Cette fonction gère l'ajout des documents justificatifs et leur association
 * avec un compte de cotisation spécifique.
 *
 * @param int $id_auteur ID de l'auteur
 * @param int $id_compte ID du compte association
 * @param array|null $files Fichiers spécifiques à traiter (optionnel)
 * @return array Informations sur les documents créés
 */
function traiter_upload_justificatif($id_auteur, $id_compte, $files = null) {
    // Journalisation

    // Si le compte est 'new', on ne peut pas encore lier de documents
    if ($id_compte == 'new') {
        // On pourrait enregistrer temporairement les IDs des documents pour les lier plus tard
        return array();
    }

    // Récupération des informations de l'auteur pour le titre
    $query_auteur = sql_fetsel('*', 'spip_auteurs', "id_auteur=$id_auteur");
    $nom_prenom = ($query_auteur['nom_famille']) ? $query_auteur['nom_famille']. ' ' . $query_auteur['prenom'] : $query_auteur['nom'];

    $date_format = affdate(date('Y-m-d'), 'j/m/Y');

    // Récupération des fichiers (soit depuis les paramètres, soit depuis la requÃªte)
    $fichiers = $files ?: (_request('_fichiers') ?: ($_FILES ?? array()));

    $documents = array();

    // _fichiers peut être nommé (SPIP) ou déjà aplati par un formulaire.
    // Reconstituer systématiquement une structure PHP multi-fichiers afin que
    // les deux parcours public et privé passent par le même traitement.
    $fichiers_upload = cotisation_documents_vers_upload($fichiers);

    if (!empty($fichiers_upload)) {
        // Charger la fonction d'ajout de documents
        $ajouter_documents = charger_fonction('ajouter_documents', 'action');

        // Ajout des documents
        $id_document = $ajouter_documents('new', $fichiers_upload, '', '', 'auto');

        // Si des documents ont été créés
        if (is_array($id_document) && !empty($id_document)) {
            foreach ($id_document as $id) {
                if ($id > 0) {
                    // Préparation du titre du document
                    $titre = "Justificatif cotisation - $nom_prenom - $date_format";

                    // Mise à jour du titre et de la description du document
                    sql_updateq(
                        'spip_documents',
                        array(
                            'titre' => $titre,
                            'descriptif' => "Document justificatif pour la cotisation ID $id_compte"
                        ),
                        "id_document=$id"
                    );

                    // Liaison du document au compte uniquement si $id_compte est numérique
                    if (is_numeric($id_compte)) {
                        if (!sql_getfetsel(
                            'id_document',
                            'spip_documents_liens',
                            "id_objet=$id_compte AND objet='compte' AND id_document=$id"
                        )) {
                            sql_insertq(
                                'spip_documents_liens',
                                array(
                                    'id_document' => $id,
                                    'id_objet' => $id_compte,
                                    'objet' => 'compte'
                                )
                            );
                        }
                    }
                    $documents[] = $id;
                }
            }
        }
    }
    return $documents;
}
