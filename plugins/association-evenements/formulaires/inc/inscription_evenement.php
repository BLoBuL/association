<?php
if (!defined('_ECRIRE_INC_VERSION')) return;


include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('inc/association_evenements_paiements');

/**
 * Journal de diagnostic des inscriptions, désactivé par défaut.
 *
 * Le contexte doit rester strictement technique : identifiants, compteurs et
 * indicateurs, jamais les valeurs saisies ni les coordonnées des participants.
 */
function association_evenements_inscription_debug($code, $contexte = array()) {
    if (!function_exists('association_log')) {
        include_spip('inc/association_log');
    }
    // Les tests CLI unitaires chargent volontairement un SPIP minimal sans
    // l'API de configuration. Un diagnostic ne doit jamais rendre le métier
    // dépendant de cette API facultative dans ce contexte.
    if (function_exists('association_log') && function_exists('lire_config')) {
        association_log('inscriptions', (string) $code, 'debug', (array) $contexte, false);
    }
}

/**
 * Pont temporaire pour les anciens messages de diagnostic déjà anonymisés.
 */
function association_evenements_inscription_debug_message($message, $canal = '') {
    association_evenements_inscription_debug('diagnostic', array('message' => (string) $message));
}

/**
 * Prépare les informations d'un auteur pour un événement.
 *
 * Cette fonction récupère et formate les informations d'un auteur en fonction de son ID
 * et de l'ID de l'événement. Elle détermine également si l'auteur est à jour de sa cotisation
 * à la date de l'événement et gère les données relatives à sa famille.
 *
 * @param int $id_auteur L'ID de l'auteur.
 * @param int $id_evenement L'ID de l'événement.
 *
 * @return array Un tableau contenant les informations de l'auteur, incluant :
 *               - 'id_auteur' : L'ID de l'auteur.
 *               - 'statut_auteur' : Le statut de l'auteur.
 *               - 'statut_interne_auteur' : Le statut interne de l'auteur.
 *               - 'radio_type_adherent' : Le type d'adhérent (si défini).
 *               - 'statut_interne_auteur_connecte' : Indique si l'auteur est à jour de sa cotisation.
 *               - 'data_famille' : Les informations de la famille de l'auteur.
 *               - 'saisie_famille_active' : Indique si la saisie des informations de la famille est active.
 */
function preparer_info_auteur($id_auteur, $id_evenement) {
    // Récupère la configuration des accompagnants
    $config_accompagnants = isset($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']) ? $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] : 'tout';

    // Récupère les paramètres d'affichage pour l'événement
    $affichage_dans_activites = affichage_dans_activites($id_evenement);

    // Récupère les informations de l'auteur depuis la base de données
    $query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur =' . $id_auteur);
    $res['id_auteur'] = ($query_auteur['id_auteur']);
    $res['statut_auteur'] = $query_auteur['statut'];
    $profil = association_evenements_profil_participant(array('id_auteur' => (int) $id_auteur));
    $res['statut_interne_auteur'] = !empty($profil['est_membre']) ? 'ok' : '';
    $res['radio_type_adherent'] = isset($query_auteur['radio_type_adherent']) ? $query_auteur['radio_type_adherent'] : false;

    // Vérifie si l'auteur est à jour de sa cotisation à la date de l'événement
    $date_validite_auteur_connecte = !empty($query_auteur['validite'])
		? affdate($query_auteur['validite'], 'Y-m-d 23:59:59')
		: '';
    if (!empty($profil['est_membre']) && $date_validite_auteur_connecte >= $affichage_dans_activites['date_fermeture_inscription']) {
        $res['statut_interne_auteur_connecte'] = 'ok';
    } else {
        $res['statut_interne_auteur_connecte'] = false;
    }

    // Génère les informations de la famille de l'auteur
    $res['data_famille'] = association_evenements_integration_active('association_adhesions')
		? generer_famille_adherent($res['id_auteur'])
		: array();

    // Détermine si la saisie des informations de la famille est active
    if ($config_accompagnants == 'membre_famille') {
        $res['saisie_famille_active'] = 'oui';
    } else {
        $res['saisie_famille_active'] = 'non';
    }

    return $res;
}
/**
 * Génère un tableau des catégories de participation en fonction des critères donnés.
 *
 * Cette fonction parcourt un tableau de catégories et filtre les données en fonction
 * du format demandé, des types de participants, et du rang. Elle peut retourner soit
 * les données formatées pour l'affichage, soit les saisies brutes.
 *
 * @param array|object $tableau_categories Les catégories à traiter, sous forme de tableau ou d'objet.
 * @param string $format Le format de sortie ('datas' pour les données formatées, 'saisies' pour les saisies brutes).
 * @param array $types Les types de participants à inclure (par défaut : ['adherent']).
 * @param int $rang Le rang à partir duquel les catégories sont incluses (par défaut : 1).
 *
 * @return array|false Un tableau contenant les catégories formatées ou les saisies brutes,
 *                     ou false si le format n'est pas reconnu.
 */
function generer_array_categories_participation($tableau_categories, $format = '', $types = array('adherent'), $rang = 1, $contexte = array()) {

    // Initialisation des tableaux pour stocker les résultats
    $datas_categories = $saisie_categories = $datas_categories_tous = $saisie_categories_tous = array();

    // Contexte du membre courant (utilisé pour filtrer les tarifs couple/enfant)
    $role        = $contexte['role'] ?? '';          // ex: 'adherent', 'enfant_1', 'conjoint'
    $has_conjoint = !empty($contexte['has_conjoint']); // vrai si conjoint sélectionné en même temps

    // Vérifie si l'entrée est un tableau ou un objet
    if (is_array($tableau_categories) || is_object($tableau_categories)) {
        foreach ($tableau_categories as $categorie => $value) {
            $id_categorie  = $value['id_categorie'];
            $type_inscrit  = $value['type_inscrit'] ?? 'indifferent';
            $choix_montant = $value['titre'] . ' ' . $value['montant_symbole'];

            // Fix C : ignorer les catégories désactivées
            if (($value['statut'] ?? 'ok') !== 'ok') {
                continue;
            }

            // Ajoute les catégories en fonction du rang
            if ($rang == 1 || $rang > $value['quantite']) {
                // $datas_categories_tous += [ "$id_categorie" => "$choix_montant" ];
            } else {
                // $datas_categories_tous += [ "$id_categorie" => 'Gratuit inclus tarif groupe' ];
            }


            $saisie_categories_tous[] = $value;

            // --- Tarif "couple" : réservé au premier inscrit ET uniquement si le conjoint est sélectionné
            if ($type_inscrit === 'couple') {
                $quantite_couple = 2;
                if ($has_conjoint && in_array('couple', $types) && ($rang == 1 || $rang > $quantite_couple)) {
                    $datas_categories += ["$id_categorie" => "$choix_montant"];
                }
                continue;
            }

            // --- Tarif "enfant" : si contexte fourni, restreindre aux enfant_* ; sinon filtre standard ---
            if ($type_inscrit === 'enfant') {
                if ($role === '') {
                    if (in_array($type_inscrit, $types)) {
                        if ($rang == 1 || $rang > intval($value['quantite'])) {
                            $datas_categories += ["$id_categorie" => "$choix_montant"];
                        }
                    }
                } elseif (strpos($role, 'enfant_') === 0) {
                    if ($rang == 1 || $rang > intval($value['quantite'])) {
                        $datas_categories += ["$id_categorie" => "$choix_montant"];
                    }
                }
                continue;
            }

            // --- Filtre standard par type de participant ---
            if (in_array($type_inscrit, $types)) {
                if ($rang == 1 || $rang > intval($value['quantite'])) {
                    $datas_categories += ["$id_categorie" => "$choix_montant"];
                } else {
                    $___noop = true; // tarif groupe : géré ailleurs
                }
            }
        }
    }

    // Retourne les résultats en fonction du format demandé
    if ($format == 'datas') {
        $res = $datas_categories;
    } elseif ($format == 'saisies') {
        $res = $saisie_categories;
    } else {
        $res = false;
    }

    return $res;
}
/**
  * Prépare les données pour la modification d'une inscription à une activité.
  *
  * Cette fonction récupère les informations nécessaires pour modifier une inscription
  * à une activité, en fonction de l'ID de l'activité. Elle traite les données de l'activité,
  * de l'auteur, des transactions, et des participants, tout en tenant compte des paramètres
  * d'affichage et de configuration des accompagnants.
  *
  * @param int $id_activite L'ID de l'activité à modifier.
  *
  * @return array|false Un tableau contenant les données préparées pour la modification
  *                     de l'inscription, ou false si l'activité n'est pas trouvée.
  */
 function preparer_chargement_modification_inscription($id_activite){

     // Récupère les données de l'activité depuis la base de données
     $query_activite = sql_fetsel("*", 'spip_asso_activites', "id_activite=$id_activite");
     if (empty($query_activite)){
         return false;
     }

     // Récupère les données de l'auteur et de la transaction associée
     $query_auteur = sql_fetsel('id_auteur,statut_interne','spip_auteurs','id_auteur ='. $query_activite['id_auteur']);
	 $query_transaction = association_evenements_integration_active('association_paiements')
		? association_evenements_transaction_lire((int) $query_activite['id_transaction'])
		: array();
     $affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);
     $config_accompagnants = isset($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']) ? $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] : 'tout';

     // Initialisation par défaut (évitent les usages de variables non définies).
     $data_famille = false;
     $saisie_famille_active = 'non';

     // Détermine le type d'inscription et les données associées
     if(($query_activite['id_auteur'] >= '1') AND ($query_auteur['statut_interne'] == 'ok')){
         $data_activite['select_type_inscrit'] = 'membre';
         $data_activite['membre'] = $query_activite['id_auteur'];
         if($config_accompagnants == 'membre_famille'){
             $data_famille = generer_famille_adherent($query_activite['id_auteur']);
             $saisie_famille_active = 'oui';
         }
     }elseif(($query_activite['id_auteur'] >= '1') AND ($query_auteur['statut_interne'] != 'ok')){
         $data_activite['select_type_inscrit'] = 'non_membre';
         $data_activite['non_membre'] = $query_activite['id_auteur'];
         if($config_accompagnants == 'membre_famille'){
             $data_famille = generer_famille_adherent($query_activite['id_auteur']);
             $saisie_famille_active = 'oui';
         }
     }elseif($query_activite['id_auteur'] == '0' AND !empty($query_activite['association']) ){
         // Délégation FIAFE : conserver la valeur brute et laisser le plugin
         // client normaliser l'affichage (association connue ou autre association).
         $data_activite['association'] = $query_activite['association'];
         $data_activite['select_type_inscrit'] = 'membre_reseau_fiafe';
         $data_activite['email_inscrit'] = $query_activite['email_inscrit'];
         $data_activite['tel_inscrit'] = $query_activite['tel_inscrit'];
         $data_activite['prenom_inscrit'] = $query_activite['prenom_inscrit'];
         $data_activite['nom_inscrit'] = $query_activite['nom_inscrit'];
     }else{
         $data_activite['select_type_inscrit'] = 'public';
         $data_activite['email_inscrit'] = $query_activite['email_inscrit'];
         $data_activite['tel_inscrit'] = $query_activite['tel_inscrit'];
         $data_activite['prenom_inscrit'] = $query_activite['prenom_inscrit'];
         $data_activite['nom_inscrit'] = $query_activite['nom_inscrit'];
     }

     // Gère les données des participants et des catégories en fonction des paramètres d'affichage
     $data_activite['nombre_inscrits'] = $query_activite['nombre_inscrits'];
     if(($affichage_dans_activites['payant'] == false) && ($saisie_famille_active != 'oui')) {
         $data_activite['nb_inscrits'] = $query_activite['nombre_inscrits'];
     }elseif(($affichage_dans_activites['payant'] == false) && ($saisie_famille_active == 'oui')){
         $participants_json  = array_keys(json_decode($query_activite['participants_json'],true));
         if($affichage_dans_activites['accompagnants'] == false){
             $data_activite['famille']  = $participants_json[0];
         }else{
             $data_activite['famille']  = $participants_json;
         }
     }elseif(($affichage_dans_activites['payant'] == true) && ($affichage_dans_activites['accompagnants'] == false) && ($saisie_famille_active == 'non')) {
         $transaction = array_keys(unserialize($query_activite['tarifs_selectionnes']));
         $transaction = array_shift($transaction);
         $data_activite['categorie'] =$transaction;
     }elseif(($affichage_dans_activites['payant'] == true) && ($affichage_dans_activites['accompagnants'] == false) && ($saisie_famille_active == 'oui')) {
         $transaction=unserialize($query_activite['tarifs_selectionnes']);
         foreach( $transaction AS $categorie => $value){
             $data_activite['categorie'][$categorie] = $value['id_participants'];
         }
     }elseif(($affichage_dans_activites['payant'] == true) && ($affichage_dans_activites['accompagnants'] == true) && ($saisie_famille_active == 'non')) {
         $transaction=unserialize($query_activite['tarifs_selectionnes']);
         foreach( $transaction AS $categorie => $value){
             $data_activite['categorie'][$categorie] = $value['nombre'];
         }
     }elseif(($affichage_dans_activites['payant'] == true) && ($saisie_famille_active == 'oui')) {
         $transaction=unserialize($query_activite['tarifs_selectionnes']);
         foreach( $transaction AS $categorie => $value){
             $data_activite['categorie'][$categorie] = $value['id_participants'];
         }
     }

     // Ajoute les informations supplémentaires à l'activité
     $data_activite['nom_participants'] = $query_activite['nom_participants'];
     $data_activite['commentaire'] = $query_activite['commentaire'];
     $data_activite['annotation'] = $query_activite['annotation'];
     $data_activite['id_auteur_activite'] = $query_activite['id_auteur'];
     $data_activite['query_transaction_statut'] = $query_transaction['statut'];
     $data_activite['saisie_famille_active'] = $saisie_famille_active;

     return $data_activite;
 }
/**
 * Prépare les données pour la modification d'une inscription à une activité.
 *
 * @param int $id_activite L'ID de l'activité à modifier.
 * @param string $public_or_prive Optionnel. Spécifie si le contexte est 'public' ou 'privé'. Par défaut, c'est ''.
 * @return array|false Un tableau de données préparées pour la modification de l'activité, ou false si l'activité n'est pas trouvée.
 */
function preparer_chargement_modification_inscription_multi($id_activite, $public_or_prive = '') {
    $res = array();

    // Récupère les données de l'activité depuis la base de données
    $query_activite = sql_fetsel("*", 'spip_asso_activites', "id_activite=$id_activite");
    if (empty($query_activite)) {
        return false;
    }

    // Récupère le statut de la transaction liée à l'activité
	$query_transaction = association_evenements_integration_active('association_paiements')
		? association_evenements_transaction_lire((int) $query_activite['id_transaction'])
		: array();

    // Obtient les paramètres d'affichage pour l'activité
    $affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);


    // Initialise les tableaux pour stocker les données de l'activité
    $data_activite = array(
        'nb_inscrits' => $query_activite['nombre_inscrits'],
        'nb_invite'   => intval($query_activite['nb_invite'] ?? 0),
        'email_inscrit' => $query_activite['email_inscrit'],
        'tel_inscrit' => $query_activite['tel_inscrit'],
        'prenom_inscrit' => $query_activite['prenom_inscrit'],
        'nom_inscrit' => $query_activite['nom_inscrit'],
        'commentaire' => $query_activite['commentaire'],
        'id_auteur_activite' => $query_activite['id_auteur'],
        'query_transaction_statut' => is_array($query_transaction) ? ($query_transaction['statut'] ?? '') : ''
    );

    // Traite le JSON des participants si disponible
    if ($query_activite['participants_json']) {
        $participants_json = json_decode($query_activite['participants_json'], true);
        $identifiants_famille = array(
            'adherent', 'conjoint',
            'enfant_1', 'enfant_2', 'enfant_3', 'enfant_4', 'enfant_5',
            'invite_1', 'invite_2',
        );
        foreach ($participants_json as $id_participant => $info_participant) {
            if (in_array($id_participant, $identifiants_famille, true)) {
                $data_activite['famille'][] = $id_participant;
            }
            foreach ($info_participant as $key => $value) {
                $data_activite[$key . '_' . $id_participant] = $value;
            }
        }
    }

    // Traite les données de l'auteur si disponibles
    if ($query_activite['id_auteur'] >= 1) {
        $query_auteur = sql_fetsel('id_auteur,statut_interne', 'spip_auteurs', 'id_auteur =' . $query_activite['id_auteur']);
        if ($query_auteur['statut_interne'] == 'ok') {
            $data_activite_prive = array(
                'select_type_inscrit' => 'membre',
                'membre' => $query_activite['id_auteur']
            );
        } else {
            $data_activite_prive = array(
                'select_type_inscrit' => 'non_membre',
                'non_membre' => $query_activite['id_auteur']
            );
        }
    } else {
        $data_activite_prive = array(
            'select_type_inscrit' => $query_activite['association'] ? 'membre_reseau_fiafe' : 'public'
        );

        if ($query_activite['association']) {
            // Délégation FIAFE: valeur brute, normalisation dans le pipeline client.
            $data_activite['association'] = $query_activite['association'];
        }
    }

    // Ajoute les annotations privées si le contexte est privé
    if ($public_or_prive == 'prive') {
        $data_activite_prive['annotation'] = $query_activite['annotation'];
        $res = array_merge($data_activite, $data_activite_prive);
    } else {
        $res = $data_activite;
    }

    return $res;
}
/**
 * Génère les informations de la famille d'un adhérent.
 *
 * @param int $id_auteur L'ID de l'auteur.
 * @return array Les informations de la famille.
 */
function generer_famille_adherent($id_auteur) {
    $query_auteur_famille = sql_fetsel("*", "spip_auteurs", "id_auteur = $id_auteur");

    if (!$query_auteur_famille || !is_array($query_auteur_famille)) {
        return array();
    }

    // Libellé minimaliste : le prénom seul suffit pour la sélection famille.
    $label_membre = function($prenom) {
        $prenom = trim((string)($prenom ?? ''));
        return $prenom !== '' ? $prenom : null;
    };

    $famille = array(
        'adherent' => $label_membre($query_auteur_famille['prenom'] ?? null),
        'conjoint' => $label_membre($query_auteur_famille['prenom_conjoint'] ?? null),
        'enfant_1' => $label_membre($query_auteur_famille['prenom_enfant_1'] ?? null),
        'enfant_2' => $label_membre($query_auteur_famille['prenom_enfant_2'] ?? null),
        'enfant_3' => $label_membre($query_auteur_famille['prenom_enfant_3'] ?? null),
        'enfant_4' => $label_membre($query_auteur_famille['prenom_enfant_4'] ?? null),
        'enfant_5' => $label_membre($query_auteur_famille['prenom_enfant_5'] ?? null),
        'invite_1' => $label_membre($query_auteur_famille['invite_1'] ?? null),
        'invite_2' => $label_membre($query_auteur_famille['invite_2'] ?? null),
    );

    return array_filter($famille, function($label) {
        return !empty($label);
    });
}
/**
 * Génère les champs de saisie pour les informations publiques d'un formulaire.
 *
 * Cette fonction crée un tableau de champs de saisie pour un formulaire d'inscription,
 * en fonction du contexte (public ou privé). Les champs incluent le prénom, le nom,
 * l'email et le téléphone, avec des options de validation et d'affichage conditionnel.
 *
 * @param string $formulaire Indique le contexte du formulaire ('prive' ou autre).
 *                           Si 'prive', certains champs sont affichés conditionnellement.
 *
 * @return array Un tableau contenant les définitions des champs de saisie.
 */
function generer_saisies_info_public($formulaire){
    // Détermine la condition d'affichage des champs en fonction du contexte
    if($formulaire == 'prive'){
        $afficher_si = "@select_type_inscrit@=='public' || @select_type_inscrit@=='membre_reseau_fiafe'";
    }else{
        $afficher_si = '';
    }

    // Champ pour le prénom
    $saisies[]= array(
        'saisie' => 'input',
        'options' => array(
            'nom' => 'prenom_inscrit',
            'label' => _T('association_evenements:activite_form_public_prenom_inscrit'),
            'defaut' => !empty(_request('prenom_inscrit')) ? _request('prenom_inscrit') : '',
            'obligatoire' => 'oui',
            'afficher_si' => $afficher_si,
        )
    );

    // Champ pour le nom
    $saisies[]= array(
        'saisie' => 'input',
        'options' => array(
            'nom' => 'nom_inscrit',
            'label' => _T('association_evenements:activite_form_public_nom_inscrit'),
            'defaut' => !empty(_request('nom_inscrit')) ? _request('nom_inscrit') : '',
            'obligatoire' => 'oui',
            'afficher_si' => $afficher_si,
        )
    );

    // Champ pour l'email
    $saisies[]= array(
        'saisie' => 'input',
        'options' => array(
            'nom' => 'email_inscrit',
            'label' => _T('association_evenements:activite_form_public_email_inscrit'),
            'defaut' => !empty(_request('email_inscrit')) ? _request('email_inscrit') : '',
            'obligatoire' => 'oui',
            'afficher_si' => $afficher_si,
        ),
        'verifier' => array(
            'type' => 'email',
            'options' => array(
                'mode' => 'normal',
            ),
        ),
    );

    // Champ pour le téléphone
    $saisies[]= array(
        'saisie' => 'input',
        'options' => array(
            'nom' => 'tel_inscrit',
            'label' => _T('association_evenements:activite_form_public_tel_inscrit'),
            'defaut' => !empty(_request('tel_inscrit')) ? _request('tel_inscrit') : '',
            'afficher_si' => $afficher_si,
            //'obligatoire' => ''
        )
    );

    // Retourne le tableau des champs de saisie
    return $saisies;
}

/**
 * Génère les détails des participants à une activité.
 *
 * Cette fonction parcourt une liste d'identifiants de participants et extrait
 * les informations correspondantes à partir des données de l'auteur. Elle
 * génère un tableau contenant les détails des participants et une chaîne
 * JSON pour les stocker.
 *
 * @param array $id_participants Une liste des identifiants des participants (e.g., 'adherent', 'conjoint', etc.).
 * @param array $query_auteur Les données de l'auteur, incluant les informations des participants.
 *
 * @return array Un tableau contenant :
 *               - 'participants_json' : Une chaîne JSON des détails des participants.
 *               - 'nom_participants' : Une chaîne contenant les noms des participants séparés par des virgules.
 */
function generer_detail_participants($id_participants, $query_auteur) {
    // Validation des paramètres d'entrée
    if (!is_array($id_participants)) {
        spip_log("Erreur: id_participants n'est pas un tableau", 'association'._LOG_ERREUR);
        return [
            'participants_json' => '{}',
            'nom_participants' => ''
        ];
    }

    if (!is_array($query_auteur)) {
        spip_log("Erreur: query_auteur n'est pas un tableau", 'association'._LOG_ERREUR);
        return [
            'participants_json' => '{}',
            'nom_participants' => ''
        ];
    }

    // Mapping des champs pour chaque type de participant
    $participants_mapping = [
        'adherent' => ['nom' => ['prenom', 'nom_famille'], 'id_auteur' => 'id_auteur'],
        'conjoint' => ['nom' => ['prenom_conjoint', 'nom_conjoint'], 'email_conjoint' => 'email_conjoint'],
        'enfant_1' => ['nom' => ['prenom_enfant_1'], 'date_naissance' => 'date_naissance_enfant_1'],
        'enfant_2' => ['nom' => ['prenom_enfant_2'], 'date_naissance' => 'date_naissance_enfant_2'],
        'enfant_3' => ['nom' => ['prenom_enfant_3'], 'date_naissance' => 'date_naissance_enfant_3'],
        'enfant_4' => ['nom' => ['prenom_enfant_4'], 'date_naissance' => 'date_naissance_enfant_4'],
        'enfant_5' => ['nom' => ['prenom_enfant_5'], 'date_naissance' => 'date_naissance_enfant_5'],
        'invite_1' => ['nom' => ['invite_1']],
        'invite_2' => ['nom' => ['invite_2']]
    ];

    $nom_participant = [];
    $array_participant = [];

    try {
        foreach ($id_participants as $id_participant) {
            if (!is_scalar($id_participant)) {
                continue;
            }
            $id_participant = trim((string) $id_participant);
            if ($id_participant === '') {
                continue;
            }
            // Vérifier si le type de participant est valide
            if (!isset($participants_mapping[$id_participant])) {
                continue;
            }

            $fields = $participants_mapping[$id_participant];

            // Extraire et vérifier les composants du nom
            $nom_valide = true;
            $nom_components = [];

            foreach ($fields['nom'] as $field) {
                if (!isset($query_auteur[$field])) {
                    $nom_valide = false;
                    break;
                }
                $nom_components[] = $query_auteur[$field];
            }

            if (!$nom_valide) {
                continue;
            }

            // Générer le nom et les données du participant
            $nom_participant[$id_participant] = implode(' ', array_filter($nom_components));

            // Créer le tableau de données du participant
            $participant_data = ['nom' => $nom_participant[$id_participant]];

            // Ajouter les champs supplémentaires
            foreach ($fields as $key => $field) {
                if ($key !== 'nom' && isset($query_auteur[$field])) {
                    $participant_data[$key] = $query_auteur[$field];
                }
            }

            if (!empty($participant_data['nom'])) {
                $array_participant[$id_participant] = $participant_data;
            }
        }

        // Encoder en JSON avec gestion des erreurs
        $participants_json = json_encode(array_filter($array_participant));
        if ($participants_json === false) {
            spip_log("Erreur d'encodage JSON: " . json_last_error_msg(), 'association'._LOG_ERREUR);
            $participants_json = '{}';
        }

    } catch (Exception $e) {
        spip_log("Exception dans generer_detail_participants: " . $e->getMessage(), 'association'._LOG_ERREUR);
        return [
            'participants_json' => '{}',
            'nom_participants' => ''
        ];
    }

    return [
        'participants_json' => $participants_json,
        'nom_participants' => implode(", ", $nom_participant)
    ];
}

// TODO : Fonction de comparaison de la modification d'une inscription
function comparer_modification($query_activite,$donnees_saisies,$ancienne_transaction,$nouvelle_transaction){

    $entree_journal = '';
    /*CHANGEMENT DU NOMBRE D'INSCRIT*/
    if($query_activite['nombre_inscrits'] < $donnees_saisies['nombre_inscrits']){
        $difference = $donnees_saisies['nombre_inscrits']  - $query_activite['nombre_inscrits'];
        $entree_journal .= 'Modification du nombre d\'inscrit (' . $difference . '). ';
        $modification['nombre_inscrits'] = array('difference' => $difference);
    }elseif($query_activite['nombre_inscrits'] > $donnees_saisies['nombre_inscrits']){
        $difference = $donnees_saisies['nombre_inscrits']  - $query_activite['nombre_inscrits'];
        $entree_journal .= 'Modification du nombre d\'inscrit (+' . $difference . '). ';
        $modification['nombre_inscrits'] = array('difference' => $difference);
    }
    /*CHANGEMENT DES PARTICIPANTS*/
    if($query_activite['nom_participants'] <> $donnees_saisies['nom_participants']){
        //$difference = xdiff_string_diff($donnees_saisies['nom_participants'], $query_activite['nom_participants']);
        $entree_journal .= 'Modification des participants. ';
        $modification['participants'] = array('difference' => $difference);
    }
    /*CHANGEMENT DES INFOS PERSONNELLES*/
    if($query_activite['prenom_inscrit'] <> $donnees_saisies['prenom_inscrit']){
        $entree_journal .= 'Mise à jour du prénom. ';
    }
    if($query_activite['nom_inscrit'] <> $donnees_saisies['nom_inscrit']){
        $entree_journal .= 'Mise à jour du nom de famille. ';
    }
    if($query_activite['email_inscrit'] <> $donnees_saisies['email_inscrit']){
        $entree_journal .= 'Mise à jour de l\'email. ';
    }
    if($query_activite['tel_inscrit'] <> $donnees_saisies['tel_inscrit']){
        $entree_journal .= 'Mise à jour du téléphone. ';
    }

    // $modification['info_perso'] = array('difference' => $difference);
    /*CHANGEMENT transaction*/
    // Comparaison de transaction : logique de différence désactivée (commentée).
    // Si l'on souhaite tracer les différences de transaction, décommenter et adapter ci-dessous.
    /*
    if($ancienne_transaction == $nouvelle_transaction AND is_array($ancienne_transaction) AND is_array($nouvelle_transaction)){
            //$difference['transaction'] = $query_activite['transaction']['montant'] - $nouvelle_transaction['montant'];
            //$entree_journal = 'Modification du nombre d\'inscrit (' . $difference['nombre_inscrits'] . ')' ;
            //$modification['montant_transaction'] = array('difference' =>$difference, 'entree_journal' => $entree_journal);

        }
    */
    /*CHANGEMENT PARTICIPANTS*/
/*    if($query_activite['participants_json'] <> $donnees_saisies['participants_json']){
        $modification = 'participants_json';
        $difference = xdiff_string_diff($query_activite['participants_json'], $donnees_saisies['participants_json']);
        $entree_journal= '';
    }    */
    $modification['entree_journal'] = $entree_journal;
    return $modification;
};
/**
 * Formate les données postées pour un formulaire d'inscription.
 *
 * Cette fonction traite les données soumises via un formulaire d'inscription
 * pour un événement. Elle gère les informations générales, les participants,
 * les catégories sélectionnées, et calcule les montants associés.
 *
 * @param int $id_evenement L'ID de l'événement concerné.
 * @param array $valeurs_post Les données postées du formulaire.
 * @param array $affichage_dans_activites Les paramètres d'affichage pour l'activité.
 * @param string $public_or_prive Indique si le contexte est 'public' ou 'privé' (par défaut : '').
 *
 * @return array Un tableau contenant les données formatées, incluant :
 *               - 'id_auteur' : L'ID de l'auteur (participant principal).
 *               - 'premier_inscrit' : Les informations du premier inscrit.
 *               - 'nombre_participants' : Le nombre total de participants.
 *               - 'categorie_result' : Les catégories sélectionnées.
 *               - 'transaction' : Les détails des transactions.
 *               - 'montant_total' : Le montant total calculé.
 *               - 'participants_json' : Les détails des participants en JSON.
 *               - 'nom_participants' : Les noms des participants.
 */
function formater_post_form($id_evenement, $valeurs_post, $affichage_dans_activites, $public_or_prive = '') {
        $trace_id = _request('ie_trace_id');
        if (!$trace_id) {
            $trace_id = 'ie_formater_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
            set_request('ie_trace_id', $trace_id);
        }
        $config_accompagnants = isset($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants']) ? $GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] : false;

        $data_form = $premier_inscrit = array();
        $categorie_result = array();
        $montant_total = 0; // Initialisation
        $transaction = array(); // Initialisation
        // Liste des participants (ids) utilisée en mode "membre_famille".
        // Toujours initialiser pour éviter les notices PHP lorsque aucun membre
        // n'est sélectionné dans l'UI.
        $id_participants = array();

        $liste_args_desirees = ['select_type_inscrit', 'membre', 'non_membre', 'famille', 'prenom_inscrit', 'nom_inscrit', 'email_inscrit', 'tel_inscrit', 'nb_inscrits', 'categorie', 'nom_participants', 'commentaire', 'annotation', 'notifier_adherent', 'association', 'autre_association'];

        foreach ($valeurs_post as $key => $value) {
            if (in_array($key, $liste_args_desirees)) {
                $data_form[$key] = is_array($value) ? (!empty($value) ? $value : []) : (!empty($value) ? $value : false);
            }
        }

        association_evenements_inscription_debug('formater_input', array(
            'trace' => $trace_id,
            'id_evenement' => intval($id_evenement),
            'public_or_prive' => $public_or_prive,
            'payant' => !empty($affichage_dans_activites['payant']) ? 'oui' : 'non',
            'accompagnants' => !empty($affichage_dans_activites['accompagnants']) ? 'oui' : 'non',
            'categorie_brut_type' => isset($data_form['categorie']) ? gettype($data_form['categorie']) : 'absent',
            'categorie_brut_count' => is_array($data_form['categorie'] ?? null) ? count($data_form['categorie']) : 0,
            'nom_participants_longueur' => strlen(trim((string)($data_form['nom_participants'] ?? ''))),
        ));

        if (isset($data_form['association']) && $data_form['association'] == 'autre_association') {
            $data_form['association'] = $data_form['autre_association'];
        }

        if ($public_or_prive == 'public') {
            $id_auteur = isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : false;
        } elseif (isset($data_form['select_type_inscrit']) && in_array($data_form['select_type_inscrit'], ["membre", "non_membre"])) {
            $id_auteur = !empty($data_form['membre']) ? $data_form['membre'] : ($data_form['non_membre'] ?? 0);
        } else {
            $id_auteur = 0;
        }

        if ($id_auteur) {
            $query_auteur = sql_fetsel("*", "spip_auteurs", "id_auteur= $id_auteur");
            $premier_inscrit['nom'] = !empty($query_auteur['nom_famille']) ? $query_auteur['nom_famille'] : '';
            $premier_inscrit['prenom'] = !empty($query_auteur['prenom']) ? $query_auteur['prenom'] : '';
            $premier_inscrit['email'] = !empty($query_auteur['email']) ? $query_auteur['email'] : '';
            $premier_inscrit['tel'] = !empty($query_auteur['mobile']) ? $query_auteur['mobile'] : '';
        } else {
            $premier_inscrit['nom'] = !empty($data_form['nom_inscrit']) ? $data_form['nom_inscrit'] : '';
            $premier_inscrit['prenom'] = !empty($data_form['prenom_inscrit']) ? $data_form['prenom_inscrit'] : '';
            $premier_inscrit['email'] = !empty($data_form['email_inscrit']) ? $data_form['email_inscrit'] : '';
            $premier_inscrit['tel'] = !empty($data_form['tel_inscrit']) ? $data_form['tel_inscrit'] : '';
        }

        $saisie_famille_active = ($config_accompagnants == 'membre_famille' && $id_auteur) ? 'oui' : 'non';

        // Détecter si le payload POST contient des sélections par participant
        // (ex: categorie[5][]=adherent). Dans ce cas, même si la config
        // n'indique pas 'membre_famille', on considère qu'il s'agit d'une
        // saisie famille effective et on adapte le formatage en conséquence.
        $saisie_famille_effective = false;

        if ($affichage_dans_activites['payant'] == false) {
            if ($saisie_famille_active == 'oui') {
                $request_famille = isset($data_form['famille']) && is_array($data_form['famille']) ? $data_form['famille'] : array($data_form['famille']);
                $nombre_participants = count($request_famille);
                $id_participants = $request_famille;
            } elseif (empty($affichage_dans_activites['accompagnants'])) {
                // Sans accompagnants, une identite valide correspond toujours
                // a exactement une personne. Accepter les representations SPIP
                // usuelles: false, 0, '0', valeur vide.
                $nombre_participants = 1;
            } else {
                $nombre_participants = isset($data_form['nb_inscrits']) ? intval($data_form['nb_inscrits']) : 0;
            }
            $transaction = '';
        } elseif ($affichage_dans_activites['payant'] == true) {
            $categorie_result = isset($data_form['categorie']) ? $data_form['categorie'] : false;

            // Clu00E9s connues de membres famille u2014 utilisu00E9es pour du00E9tecter le nouveau format.
            static $membre_keys = ['adherent', 'conjoint', 'enfant_1', 'enfant_2', 'enfant_3', 'enfant_4', 'enfant_5', 'invite_1', 'invite_2'];

            // Si une des valeurs de categorie_result est elle-même un tableau,
            // on est probablement dans l'ancien mode "famille" (participants cochés par tarif).
            if (is_array($categorie_result) && count($categorie_result) > 0) {
                $first = reset($categorie_result);
                if (is_array($first)) {
                    $saisie_famille_effective = true;
                }
            }

            // Nouveau format famille+accompagnants : categorie[membre_key] = id_categorie
            // Les clés sont des noms de membres (non-numériques), les valeurs sont des ids de tarif.
            // On inverse la structure pour retrouver l'ancien format attendu par le reste du traitement.
            if (is_array($categorie_result) && !$saisie_famille_effective && count($categorie_result) > 0) {
                $first_key = array_key_first($categorie_result);
                if (in_array($first_key, $membre_keys, true)) {
                    // Inversion : [membre => id_tarif] -> [id_tarif => [membres]]
                    $inverted = array();
                    foreach ($categorie_result as $cle_membre => $id_tarif) {
                        if (empty($id_tarif)) {
                            continue;
                        }
                        $id_tarif = intval($id_tarif);
                        if (!isset($inverted[$id_tarif])) {
                            $inverted[$id_tarif] = array();
                        }
                        $inverted[$id_tarif][] = $cle_membre;
                    }
                    $categorie_result = $inverted;
                    $saisie_famille_effective = true;
                }
            }

            // Normaliser categorie: si string (cas simple sans accompagnants), le convertir en array
            // Pour cohérence: categorie simple "5" -> categorie_array = [5 => 1]
            if (!is_array($categorie_result) && !empty($categorie_result)) {
                $categorie_result = array(intval($categorie_result) => 1);
            } elseif (empty($categorie_result)) {
                $categorie_result = false;
            }

            association_evenements_inscription_debug('formater_categorie_normalisee', array(
                'trace' => $trace_id,
                'categorie_result_type' => gettype($categorie_result),
                'categorie_result_count' => is_array($categorie_result) ? count($categorie_result) : 0,
            ));

            if (is_array($categorie_result) && !empty($categorie_result)) {
                $querie_categorie_activite = sql_select("*", "spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a ON(a.id_categorie=b.id_categorie)", "a.id_evenement=$id_evenement AND b.statut='ok' AND a.montant!=''", '', "montant DESC");
                $nombre_inscrits = $nombre_participants = 0;
                $montant_total = 0;
                $id_participants = $categorie_participants = array();

                while ($categories_activite = sql_fetch($querie_categorie_activite)) {
                    $id_categorie = $categories_activite['id_categorie'];
                    if (isset($categorie_result[$id_categorie])) {
                        if ($saisie_famille_active == 'oui' || $saisie_famille_effective) {
                            // Cas famille : la valeur peut être un tableau (plusieurs membres)
                            // ou une chaîne (sélection unique par select en BO). Gérer les deux.
                            if (is_array($categorie_result[$id_categorie])) {
                                $nombre_inscrits = count($categorie_result[$id_categorie]);
                                $categorie_participants = $categorie_result[$id_categorie];
                            } elseif (!empty($categorie_result[$id_categorie])) {
                                // valeur unique (ex: 'adherent')
                                $nombre_inscrits = 1;
                                $categorie_participants = array($categorie_result[$id_categorie]);
                            } else {
                                $nombre_inscrits = 0;
                                $categorie_participants = array();
                            }
                            $id_participants = array_merge_recursive($id_participants, $categorie_participants);
                        } else {
                            $nombre_inscrits = intval($categorie_result[$id_categorie]);
                        }

                        $nombre_participants += intval($nombre_inscrits);
                        $montant_prepa = floatval($categories_activite['montant']);

                        if (intval($categories_activite['quantite']) > 1) {
                            $quotient_nb_tarif_groupe = intval($nombre_inscrits) / intval($categories_activite['quantite']);
                            $arrondi_quotient_nb_tarif_groupe = ceil($quotient_nb_tarif_groupe);
                            $montant_total += $montant_prepa * $arrondi_quotient_nb_tarif_groupe;
                        } else {
                            $montant_total += $montant_prepa * $nombre_inscrits;
                        }

                        association_evenements_inscription_debug_message('[IE_FORMATER_CATEGORIE_CALCUL][' . $trace_id . '] ' . json_encode(array(
                            'id_categorie' => intval($id_categorie),
                            'quantite_tarif' => intval($categories_activite['quantite'] ?? 0),
                            'nombre_inscrits_categorie' => intval($nombre_inscrits),
                            'nombre_participants_cumule' => intval($nombre_participants),
                            'montant_unitaire' => floatval($montant_prepa),
                            'montant_total_cumule' => floatval($montant_total),
                        )), 'association' . _LOG_DEBUG);

                        $transaction[$id_categorie] = array(
                            'nombre' => $nombre_inscrits,
                            'montant' => $montant_prepa,
                            'id_participants' => $categorie_participants,
                        );
                    }
                }
                sql_free($querie_categorie_activite);

                // Fallback: if the SQL query returned no rows (test environment or missing links),
                // attempt to build participants/transaction from the posted arrays when
                // family mode is active/effective. This ensures that checkboxes
                // categorie[<id>][]=<member_key> are respected even without DB lookup.
                if (empty($transaction) && ( $saisie_famille_active == 'oui' || $saisie_famille_effective )) {
                    foreach ($categorie_result as $id_categorie => $vals) {
                        if (is_array($vals)) {
                            $nb = count($vals);
                            $nombre_participants += $nb;
                            $id_participants = array_merge_recursive($id_participants, $vals);
                            $transaction[$id_categorie] = array(
                                'nombre' => $nb,
                                'montant' => 0,
                                'id_participants' => $vals,
                            );
                            association_evenements_inscription_debug_message('[IE_FORMATER_CATEGORIE_CALCUL_FALLBACK][' . $trace_id . '] ' . json_encode(array(
                                'id_categorie' => intval($id_categorie),
                                'nombre_inscrits_categorie' => intval($nb),
                                'nombre_participants_cumule' => intval($nombre_participants),
                            )), 'association' . _LOG_DEBUG);
                        } else {
                            $nb = intval($vals);
                            $nombre_participants += $nb;
                            $transaction[$id_categorie] = array('nombre' => $nb, 'montant' => 0, 'id_participants' => array());
                        }
                    }
                }
            } else {
                // Pas de categorie sélectionnée: cas par défaut
                $nombre_participants = 1;
                $transaction = array();
                association_evenements_inscription_debug_message('[IE_FORMATER_CATEGORIE_VIDE][' . $trace_id . '] ' . json_encode(array(
                    'id_evenement' => intval($id_evenement),
                    'categorie_result' => $categorie_result,
                )), 'association' . _LOG_DEBUG);
            }
        }

        // Assurer que $id_participants est un tableau même si aucune catégorie
        // n'a construit de participants (évite notice non définie).
        $id_participants = is_array($id_participants) ? $id_participants : array();

        if ($saisie_famille_active == 'oui') {
            // Si aucun membre n'a été sélectionné, marquer le flag pour que la
            // vérification remonte une erreur dédiée (voir ie_verifier_commons).
            if (empty($id_participants)) {
                $data_form['famille_selection_vide'] = true;
            }
            $detail_participants = generer_detail_participants($id_participants, $query_auteur);
            $data_form['participants_json'] = $detail_participants['participants_json'];
            $data_form['nom_participants'] = $detail_participants['nom_participants'];
            // Exposer l'identifiant des participants sélectionnés
            $data_form['id_participants'] = $id_participants;
        } else {
            $data_form['participants_json'] = '';
        }

        $data_form += [
            'id_auteur' => $id_auteur,
            'premier_inscrit' => $premier_inscrit,
            'nombre_participants' => $nombre_participants,
            'categorie_result' => $categorie_result,
            'transaction' => $transaction,
            'montant_total' => $montant_total
        ];
        association_evenements_inscription_debug('formater_output', array(
            'trace' => $trace_id,
            'id_auteur' => intval($id_auteur),
            'nombre_participants' => intval($nombre_participants),
            'categorie_count' => is_array($categorie_result) ? count($categorie_result) : 0,
            'montant_total' => floatval($montant_total),
            'transaction_count' => is_array($transaction) ? count($transaction) : 0,
        ));
        return $data_form;
    }
/**
 * Aplatit une liste potentiellement imbriquee (ex: [["adherent"]]).
 *
 * @param mixed $valeur
 * @return array
 */
function ie_aplatir_liste_valeurs($valeur) {
    if (!is_array($valeur)) {
        return ($valeur === '' || $valeur === null) ? array() : array($valeur);
    }

    $res = array();
    foreach ($valeur as $item) {
        if (is_array($item)) {
            $res = array_merge($res, ie_aplatir_liste_valeurs($item));
        } elseif ($item !== '' && $item !== null) {
            $res[] = $item;
        }
    }

    return array_values(array_unique($res));
}

/**
 * Formate les données postées pour un formulaire multi-participants.
 *
 * Cette fonction regroupe les données des participants en fonction de leur type
 * (adhérent, conjoint, enfants, invités, etc.) et extrait les informations pertinentes
 * pour chaque participant. Elle gère également les catégories sélectionnées et les
 * données supplémentaires comme les commentaires ou annotations.
 *
 * @param array $valeurs_post Les données postées du formulaire.
 * @param string $public_or_prive Indique si le contexte est 'public' ou 'privé' (par défaut : '').
 *
 * @return array Un tableau contenant les données formatées, incluant :
 *               - 'nombre_participants' : Le nombre total de participants.
 *               - 'nom_participants' : Une chaîne contenant les noms des participants.
 *               - 'premier_inscrit' : Les informations du premier participant.
 *               - 'array_post_inscrits' : Les données regroupées des participants.
 *               - 'categorie_result' : Les catégories sélectionnées par participant.
 *               - 'id_auteur' : L'ID de l'auteur si applicable.
 */
function formater_post_form_multi($valeurs_post, $public_or_prive = '') {
    // Initialisation des variables pour stocker les données formatées
    $res = array();
    $array_post_inscrits = array();
    $nom_participant = array();
    $categorie_result = array();

    // Normaliser le payload multi-etapes (meme logique que le backend):
    // fusionner les valeurs precedentes, avec priorite aux valeurs courantes.
    if (!empty($valeurs_post['cvtm_prev_post']) && !is_array($valeurs_post['cvtm_prev_post'])) {
        $decoded_prev = @base64_decode($valeurs_post['cvtm_prev_post'], true);
        if ($decoded_prev !== false && $decoded_prev !== '') {
            $decoded_prev = @unserialize($decoded_prev);
        }
        if (is_array($decoded_prev)) {
            $valeurs_post = array_merge($decoded_prev, $valeurs_post);
        }
    } elseif (!empty($valeurs_post['cvtm_prev_post']) && is_array($valeurs_post['cvtm_prev_post'])) {
        $valeurs_post = array_merge($valeurs_post['cvtm_prev_post'], $valeurs_post);
    }
    unset($valeurs_post['cvtm_prev_post']);

    // Guard : valeurs_post doit être un tableau
    if (!is_array($valeurs_post)) {
        return $res;
    }

    // En BO multi, le participant principal public peut être saisi avec les
    // champs simples de l'étape 1. Le formateur multi attend la nomenclature
    // *_inscrit_1 : la normaliser après fusion des étapes pour ne pas perdre
    // l'identité au traitement final.
    $champs_premier_inscrit = array(
        'prenom_inscrit' => 'prenom_inscrit_1',
        'nom_inscrit' => 'nom_inscrit_1',
        'email_inscrit' => 'email_inscrit_1',
        'tel_inscrit' => 'tel_inscrit_1',
    );
    $premier_inscrit_simple_present = false;
    foreach ($champs_premier_inscrit as $champ_simple => $champ_multi) {
        if (!empty($valeurs_post[$champ_simple])) {
            $premier_inscrit_simple_present = true;
            if (empty($valeurs_post[$champ_multi])) {
                $valeurs_post[$champ_multi] = $valeurs_post[$champ_simple];
            }
        }
    }
    // Liste des types de membres de la famille
    $membres_famille = array('adherent', 'conjoint', 'enfant_1', 'enfant_2', 'enfant_3', 'enfant_4', 'enfant_5', 'invite_1', 'invite_2');

    // Contexte courant pour éviter de garder des données obsolètes d'une étape précédente.
    $valeur_post_famille = array();
    $nb_inscrits_courant = isset($valeurs_post['nb_inscrits']) ? intval($valeurs_post['nb_inscrits']) : 0;
    $nb_invite_courant   = isset($valeurs_post['nb_invite'])   ? intval($valeurs_post['nb_invite'])   : 0;
    // En mode famille, les invités sont des inscrit_N. Le plafond maximal à conserver
    // est le plus grand entre nb_inscrits (mode public) et nb_invite (mode famille).
    $nb_inscrit_max = max($nb_inscrits_courant, $nb_invite_courant);

    // Détecter si on est en mode famille (champ 'famille' posté)
    // En mode famille, les inscrit_N correspondent aux invités externes.
    // Quand nb_invite = 0, TOUS les inscrit_N doivent être ignorés même si
    // leurs champs sont soumis (masqués par afficher_si JS mais présents dans le DOM).
    $mode_famille = !empty($valeurs_post['famille']);

    // Traitement des données pour les membres de la famille
    if (!empty($valeurs_post['famille'])) {
        $valeur_post_famille = ie_aplatir_liste_valeurs($valeurs_post['famille']);
        $valeurs_post['famille'] = $valeur_post_famille;
        foreach ($valeur_post_famille as $membre_famille) {
            if (!in_array($membre_famille, $membres_famille)) {
                continue;
            }
            foreach ($valeurs_post as $cle_post => $valeur_post) {
                if (strpos($cle_post, '_' . $membre_famille) !== false && strpos($cle_post, 'fieldset') == false) {
                    // Regroupe les données par type de membre
                    if (!isset($array_post_inscrits[$membre_famille])) {
                        $array_post_inscrits[$membre_famille] = array();
                    }
                    // Stocke les données dans le tableau regroupé
                    $new_key = str_replace("_$membre_famille", '', $cle_post);
                    $array_post_inscrits[$membre_famille][$new_key] = $valeur_post;
                    $nom_participant[$membre_famille] = ($array_post_inscrits[$membre_famille]['prenom'] ?? '') . ' ' . ($array_post_inscrits[$membre_famille]['nom'] ?? '');
                    if (strpos($cle_post, 'categorie_') !== false) {
                        $categorie_result[$membre_famille] = $valeur_post;
                    }
                    unset($valeurs_post[$cle_post]);
                }
            }
        }
    }

    // Les invités externes utilisent la nomenclature inscrit_N (même que les inscrits publics).
    // Ils sont capturés par le loop générique _inscrit_N ci-dessous.
    // nb_invite est conservé pour la sauvegarde BDD via $res['nb_invite'] plus bas.

    // Traitement des données pour les autres participants
    foreach ($valeurs_post as $cle_post => $valeur_post) {
        if (strpos($cle_post, '_inscrit_') !== false && strpos($cle_post, 'fieldset') == false) {
            // Extrait l'ID du participant à partir de la clé
            $id_inscrit = intval(substr($cle_post, strrpos($cle_post, '_') + 1));
            // En mode famille : inscrit_N = invités, ignorer si nb_invite = 0 ou dépassé
            if ($mode_famille) {
                if ($id_inscrit > $nb_invite_courant) {
                    continue;
                }
            } elseif ($nb_inscrit_max > 0 && $id_inscrit > $nb_inscrit_max) {
                // En mode public : ignorer les inscrits obsolètes d'une étape précédente
                continue;
            }
            // Regroupe les données par ID de participant
            if (!isset($array_post_inscrits["inscrit_$id_inscrit"])) {
                $array_post_inscrits["inscrit_$id_inscrit"] = array();
            }
            // Stocke les données dans le tableau regroupé
            $new_key = str_replace("_inscrit_$id_inscrit", '', $cle_post);
            $array_post_inscrits["inscrit_$id_inscrit"][$new_key] = $valeur_post;
            $nom_participant["inscrit_$id_inscrit"] = ($array_post_inscrits["inscrit_$id_inscrit"]['prenom'] ?? '') . ' ' . ($array_post_inscrits["inscrit_$id_inscrit"]['nom'] ?? '');
            if (strpos($cle_post, "categorie_") !== false) {
                $categorie_result["inscrit_$id_inscrit"] = $valeur_post;
            }
        } elseif (in_array($cle_post, array('commentaire', 'annotation', 'notifier'))) {
            // Stocke les données supplémentaires comme les commentaires
            $res[$cle_post] = !empty($valeur_post) ? $valeur_post : false;
        }
    }

    // Préparation des résultats finaux
    if (!empty($array_post_inscrits)) {
        $premier_inscrit = array_values(array_slice($array_post_inscrits, 0, 1));
        $res['nombre_participants'] = count($array_post_inscrits);
        $res['nom_participants'] = implode(", ", $nom_participant);
        $res['premier_inscrit'] = $premier_inscrit[0];
        $res['array_post_inscrits'] = $array_post_inscrits;
    }
    if (!empty($categorie_result)) {
        $res['categorie_result'] = is_array($categorie_result) ? $categorie_result : array($categorie_result);
    }
    // Support direct POST format where categories are sent as `categorie[<id_categorie>]`
    // (common when Saisies posts arrays). Normalize into categorie_result expected shape.
    // For family-mode postings, Saisies sends category => array(participant_ids). We need
    // to convert that into participant_id => category_id mapping for the multi form logic.
    if (empty($res['categorie_result']) && isset($valeurs_post['categorie']) && !empty($valeurs_post['categorie'])) {
        $posted_cat = $valeurs_post['categorie'];
        if (is_array($posted_cat)) {
            $map = array();
            $nb = 0;
            $filtre_famille = !empty($valeur_post_famille) ? array_flip($valeur_post_famille) : array();
            foreach ($posted_cat as $id_categorie => $v) {
                if (is_array($v)) {
                    foreach ($v as $participant_id) {
                        $participant_id = (string)$participant_id;
                        // En mode famille, ne conserver que les membres actuellement cochés.
                        if (!empty($filtre_famille) && !isset($filtre_famille[$participant_id])) {
                            continue;
                        }
                        $map[$participant_id] = $id_categorie;
                        $nb++;
                    }
                } else {
                    $nb += intval($v);
                }
            }
            if (!empty($map)) {
                $res['categorie_result'] = $map;
            } else {
                // fallback: keep category->value mapping if we couldn't map participants
                $res['categorie_result'] = $posted_cat;
            }
            if (empty($res['nombre_participants'])) {
                $res['nombre_participants'] = $nb > 0 ? $nb : 1;
            }
        } else {
            // scalar
            $res['categorie_result'] = array($posted_cat);
            if (empty($res['nombre_participants'])) {
                $res['nombre_participants'] = 1;
            }
        }
    }
    // Associer l'auteur selon le contexte:
    // - FO: auteur connecté (ou 0)
    // - BO: auteur sélectionné (ou 0)
    if ($public_or_prive == 'public') {
        $res['id_auteur'] = isset($GLOBALS['visiteur_session']['id_auteur']) ? intval($GLOBALS['visiteur_session']['id_auteur']) : 0;
    } else {
        if (($valeurs_post['select_type_inscrit'] ?? '') == 'membre') {
            $res['id_auteur'] = intval($valeurs_post['membre'] ?? 0);
        } elseif (($valeurs_post['select_type_inscrit'] ?? '') == 'non_membre') {
            $res['id_auteur'] = intval($valeurs_post['non_membre'] ?? 0);
        } else {
            $res['id_auteur'] = 0;
        }
    }

    // En BO multi, certains événements n'ont aucune étape d'identité dédiée
    // (par exemple un événement gratuit sans accompagnant). Le sélecteur
    // d'adhérent est alors la source canonique du participant principal.
    // Hydrater les données depuis spip_auteurs afin que le récapitulatif et la
    // barrière de traiter() disposent d'une identité réelle, sans demander à
    // l'administrateur de ressaisir un champ masqué.
    if ($public_or_prive !== 'public'
        && intval($res['id_auteur'] ?? 0) > 0
        && !$mode_famille
    ) {
        $auteur_principal = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($res['id_auteur']));
        if (is_array($auteur_principal) && !empty($auteur_principal['id_auteur'])) {
            $participant_principal = array(
                'prenom' => trim((string) ($auteur_principal['prenom'] ?? '')),
                'nom' => trim((string) ($auteur_principal['nom_famille'] ?? '')),
                'email' => trim((string) ($auteur_principal['email'] ?? '')),
                'tel' => trim((string) ($auteur_principal['mobile'] ?? '')),
            );
            $inscrits_bo = is_array($res['array_post_inscrits'] ?? null)
                ? $res['array_post_inscrits']
                : array();
            $inscrit_principal_existant = is_array($inscrits_bo['inscrit_1'] ?? null)
                ? $inscrits_bo['inscrit_1']
                : array();
            // Le membre sélectionné est la source canonique du participant 1,
            // mais les accompagnants inscrit_2..N déjà saisis doivent rester intacts.
            $inscrits_bo['inscrit_1'] = array_merge($inscrit_principal_existant, $participant_principal);
            uksort($inscrits_bo, 'strnatcmp');

            $noms_bo = array();
            foreach ($inscrits_bo as $cle_inscrit => $infos_inscrit) {
                if (!is_array($infos_inscrit)) {
                    continue;
                }
                $nom_complet = trim((string) ($infos_inscrit['prenom'] ?? '') . ' ' . (string) ($infos_inscrit['nom'] ?? ''));
                if ($nom_complet !== '') {
                    $noms_bo[$cle_inscrit] = $nom_complet;
                }
            }

            $res['premier_inscrit'] = $inscrits_bo['inscrit_1'];
            $res['array_post_inscrits'] = $inscrits_bo;
            $res['nombre_participants'] = count($inscrits_bo);
            $res['nom_participants'] = implode(', ', $noms_bo);
        }
    }

    // Garder séparément la quantité demandée et la quantité réellement décrite.
    // verifier()/traiter() peuvent ainsi refuser une réduction silencieuse 2 -> 1.
    $res['nombre_participants_demandes'] = $nb_inscrits_courant;
    $res['nombre_participants_reels'] = count((array) ($res['array_post_inscrits'] ?? array()));
    $res['mode_famille'] = $mode_famille;

    // Nombre d'invités hors famille
    $res['nb_invite'] = max(0, intval($valeurs_post['nb_invite'] ?? 0));

    return $res;
}
/**
 * Calcule le montant total pour un événement en fonction des catégories sélectionnées.
 *
 * Cette fonction parcourt les catégories disponibles pour un événement et calcule
 * le montant total à payer en fonction du nombre de participants et des tarifs
 * associés aux catégories. Elle gère également les tarifs de groupe.
 *
 * @param int $id_evenement L'ID de l'événement pour lequel le calcul est effectué.
 * @param array $categorie_result Un tableau associatif contenant les participants et leurs catégories sélectionnées.
 * @param int $nombre_participants Le nombre total de participants (par défaut : 0).
 *
 * @return array Un tableau contenant :
 *               - 'montant_total' : Le montant total calculé.
 *               - 'transaction' : Les détails des transactions par catégorie.
 */
function calculer_montant_total($id_evenement, $categorie_result, $nombre_participants = 0) {
    return analyser_selection_tarifs_evenement($id_evenement, $categorie_result);
}

/**
 * Analyse une sélection tarifaire uniquement à partir des catégories actives
 * liées à l'événement. Les montants soumis par le navigateur ne sont jamais lus.
 *
 * @param int $id_evenement
 * @param mixed $categorie_result Sélection normalisée catégorie=>quantité ou participant=>catégorie.
 * @return array
 */
function analyser_selection_tarifs_evenement($id_evenement, $categorie_result) {
    $tarifs = array();
    $query = sql_select(
        '*',
        'spip_asso_categories_activites AS b JOIN spip_asso_categories_activites_liens as a ON(a.id_categorie=b.id_categorie)',
        'a.id_evenement=' . intval($id_evenement) . " AND b.statut='ok' AND a.montant!=''"
    );
    while ($row = sql_fetch($query)) {
        $tarifs[intval($row['id_categorie'])] = $row;
    }
    sql_free($query);

    // Le chargeur d'inscription expose deja la meme liste filtree. Ce repli
    // conserve la validation dans les contextes sans ressource SQL directe
    // (notamment les harnais CLI) sans accepter une categorie hors evenement.
    if (empty($tarifs)) {
        $affichage = affichage_dans_activites($id_evenement);
        foreach ((array) ($affichage['montant'] ?? array()) as $tarif) {
            $id_categorie = intval($tarif['id_categorie'] ?? 0);
            if ($id_categorie <= 0) {
                continue;
            }
            if (!array_key_exists('montant', $tarif)) {
                $montant_texte = str_replace(',', '.', (string) ($tarif['montant_symbole'] ?? '0'));
                $tarif['montant'] = preg_match('/-?\d+(?:\.\d+)?/', $montant_texte, $match)
                    ? floatval($match[0])
                    : 0.0;
            }
            $tarifs[$id_categorie] = $tarif;
        }
    }

    $selection = is_array($categorie_result) ? $categorie_result : array();
    $transaction = array();
    $invalides = array();

    foreach ($selection as $cle => $valeur) {
        $id_categorie = 0;
        $nombre = 0;
        $participants = array();
        $cle_numerique = (string) intval($cle) === (string) $cle || is_int($cle);

        if ($cle_numerique) {
            if (isset($tarifs[intval($cle)])) {
                $id_categorie = intval($cle);
                if (is_array($valeur)) {
                    $participants = array_values(array_filter($valeur, function ($participant) {
                        return $participant !== '' && $participant !== null;
                    }));
                    $nombre = count($participants);
                } else {
                    $nombre = max(0, intval($valeur));
                }
            } elseif (intval($cle) === 0 && is_scalar($valeur) && isset($tarifs[intval($valeur)])) {
                $id_categorie = intval($valeur);
                $nombre = 1;
                $participants = array((string) $cle);
            } else {
                $invalides[] = intval($cle);
                continue;
            }
        } elseif (is_scalar($valeur) && isset($tarifs[intval($valeur)])) {
            $id_categorie = intval($valeur);
            $nombre = 1;
            $participants = array((string) $cle);
        } else {
            $invalides[] = is_scalar($valeur) ? intval($valeur) : intval($cle);
            continue;
        }

        if ($nombre <= 0) {
            // Les champs quantitatifs du BO postent aussi les tarifs non
            // selectionnes avec la valeur 0. Ils ne constituent pas une
            // tentative de choisir une categorie invalide.
            continue;
        }
        if (!isset($transaction[$id_categorie])) {
            $transaction[$id_categorie] = array(
                'nombre' => 0,
                'montant' => floatval($tarifs[$id_categorie]['montant']),
                'id_participants' => array(),
            );
        }
        $transaction[$id_categorie]['nombre'] += $nombre;
        $transaction[$id_categorie]['id_participants'] = array_merge(
            $transaction[$id_categorie]['id_participants'],
            $participants
        );
    }

    $montant_total = 0.0;
    $nombre_total = 0;
    $categories = array();
    foreach ($transaction as $id_categorie => $detail) {
        $nombre = intval($detail['nombre']);
        $quantite_tarif = max(1, intval($tarifs[$id_categorie]['quantite'] ?? 1));
        $nombre_lots = $quantite_tarif > 1 ? ceil($nombre / $quantite_tarif) : $nombre;
        $montant_total += floatval($detail['montant']) * $nombre_lots;
        $nombre_total += $nombre;
        $categories[] = array(
            'id_categorie' => intval($id_categorie),
            'titre' => trim((string) ($tarifs[$id_categorie]['titre'] ?? $tarifs[$id_categorie]['valeur'] ?? $id_categorie)),
            'nombre' => $nombre,
            'montant' => floatval($detail['montant']),
        );
    }

    return array(
        'valide' => !empty($transaction) && empty($invalides),
        'categories_invalides' => array_values(array_unique($invalides)),
        'nombre_participants' => $nombre_total,
        'montant_total' => $montant_total,
        'transaction' => $transaction,
        'categories' => $categories,
    );
}
/**
 * Génère un récapitulatif des informations d'inscription pour une activité.
 *
 * Cette fonction prépare un tableau contenant les détails de l'inscription,
 * tels que le nombre de participants, les noms des participants, le statut
 * de l'inscription, le montant total à régler, et les commentaires éventuels.
 *
 * @param array $valeur_post Les données postées du formulaire d'inscription.
 * @param int $id_evenement L'ID de l'événement associé à l'inscription.
 * @param string $public_or_prive Indique si le contexte est 'public' ou 'privé' (par défaut : '').
 *
 * @return array Un tableau contenant les informations récapitulatives de l'inscription.
 */
function generer_recapitulatif_multi($valeur_post, $id_evenement, $public_or_prive = '') {
    // Initialisation du tableau de résultats
    $res = array();

    // Guard : valeur_post doit être un tableau (unserialize peut retourner false/null)
    if (!is_array($valeur_post) || empty($valeur_post)) {
        return $res;
    }

    // Formatage des données postées pour le contexte privé
    $data_form = formater_post_form_multi($valeur_post, 'prive');

    // Ajout du nombre de participants au récapitulatif
    $res['nombre_participants'] = array(
        'label' => _T('association_evenements:activite_form_public_nombre_participants'),
        'data' => $data_form['nombre_participants']
    );

    // Ajout des noms des participants au récapitulatif
    $res['nom_participants'] = array(
        'label' => _T('association_evenements:activite_form_public_nom_participants'),
        'data' => $data_form['nom_participants']
    );

    // Calcul du statut de l'inscription
    $activite_enregistrement_calculator = activite_enregistrement_calculator(
        $id_evenement,
        $data_form['nombre_participants'],
        '',
        '',
        $data_form['id_activite'] ?? null
    );

    // Ajout du statut de l'inscription au récapitulatif
    if ($activite_enregistrement_calculator['statut'] == 'ok') {
        $res['statut'] = array(
            'label' => _T('association_evenements:activite_form_public_statut_inscription'),
            'data' => _T('association_evenements:statut_ok')
        );
    } elseif ($activite_enregistrement_calculator['statut'] == 'preinscrit') {
        $res['statut'] = array(
            'label' => _T('association_evenements:activite_form_public_statut_inscription'),
            'data' => _T('association_evenements:statut_preinscrit')
        );
    } elseif ($activite_enregistrement_calculator['statut'] == 'liste_attente') {
        $res['statut'] = array(
            'label' => _T('association_evenements:activite_form_public_statut_inscription'),
            'data' => _T('association_evenements:statut_attente')
        );
    }

    $affichage = affichage_dans_activites($id_evenement);
    // Pour un événement payant, le récapitulatif expose toujours le tarif et
    // le montant recalculés côté serveur.
    if (!empty($affichage['payant'])) {
		$paiements_actifs = association_evenements_integration_active('association_paiements');
		if ($paiements_actifs) {
			include_spip('inc/bank');
		}
		$devise_defaut = $paiements_actifs
			? bank_devise_defaut()
			: array('code' => 'EUR', 'symbole' => '€');
        $calculer_montant_total = analyser_selection_tarifs_evenement(
            $id_evenement,
            $data_form['categorie_result'] ?? array()
        );

        $libelles_categories = array();
        foreach ($calculer_montant_total['categories'] as $categorie) {
            $libelles_categories[] = $categorie['titre'] . ' × ' . intval($categorie['nombre']);
        }
        $res['categorie_tarif'] = array(
            'label' => _T('association_evenements:activite_recapitulatif_categorie_tarif'),
            'data' => !empty($libelles_categories)
                ? implode(', ', $libelles_categories)
                : _T('association_evenements:activite_recapitulatif_categorie_absente')
        );

        // Ajout du montant total au récapitulatif
        $montant_total = $calculer_montant_total['montant_total'];
        $res['resultat_montant_total'] = array(
            'label' => _T('association_evenements:activite_entete_montant_a_regler'),
            'data' => $montant_total . ' ' . $devise_defaut['symbole']
        );
    }

    // Ajout des commentaires au récapitulatif, s'ils existent
    if (!empty($data_form['commentaire'])) {
        $res['commentaire'] = array(
            'label' => _T('association_evenements:activite_entete_commentaire'),
            'data' => $data_form['commentaire']
        );
    }

    // Retourne le tableau récapitulatif
    return $res;
}
/**
 * Génère le récapitulatif multi à partir de l'environnement de formulaire courant.
 * Utilise la même normalisation que le backend pour éviter les écarts de contexte
 * entre l'étape récap et la validation finale.
 */
function generer_recapitulatif_multi_env($env_form, $id_evenement, $public_or_prive = 'prive') {
    if (!is_array($env_form)) {
        $tmp = @unserialize($env_form);
        $env_form = is_array($tmp) ? $tmp : array();
    }

    include_spip('formulaires/inc/inscription_evenement_backend');
    $mode = ($public_or_prive == 'public') ? 'multi_public' : 'multi_prive';
    $id_activite = intval(_request('id_activite')) ?: null;

    $payload = ie_requete_attendue($mode, intval($id_evenement), $id_activite, $env_form);

    return generer_recapitulatif_multi($payload, $id_evenement, $public_or_prive);
}


/**
 * Construit le participants_json final en fusionnant :
 * - les données famille (depuis generer_detail_participants, mode famille)
 * - les invités hors famille (inscrit_N depuis array_post_inscrits)
 * - ou uniquement array_post_inscrits si pas de mode famille
 */
function ie_construire_participants_json($data_form) {
    $array_inscrits = is_array($data_form['array_post_inscrits'] ?? null) ? $data_form['array_post_inscrits'] : array();

    if (!empty($data_form['participants_json'])) {
        // Mode famille : base = membres famille, on fusionne les inscrit_N (invités)
        $base = json_decode($data_form['participants_json'], true) ?: array();
        foreach ($array_inscrits as $cle => $infos) {
            if (preg_match('/^inscrit_\d+$/', (string)$cle)) {
                $base[$cle] = $infos;
            }
        }
        $json = json_encode($base);
    } else {
        $json = json_encode(array_filter($array_inscrits));
    }

    return ($json !== false && $json !== '') ? $json : '{}';
}
/**
 * Insère une activité associative dans la base de données.
 *
 * Cette fonction crée une nouvelle entrée dans la table `spip_asso_activites`
 * pour enregistrer les informations relatives à une activité associative.
 *
 * @param int $id_evenement L'ID de l'événement associé à l'activité.
 * @param array $data_form Les données du formulaire d'inscription, incluant les informations des participants.
 * @param array $cal_result Les résultats du calcul de l'inscription, incluant le statut.
 * @param int $id_transaction L'ID de la transaction associée à l'inscription.
 * @param string $nouvelle_transaction Les données de la transaction sérialisées.
 * @param string $message_journal Un message de journalisation pour l'activité.
 *
 * @return int L'ID de l'activité nouvellement créée.
 */
function inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal,$ip_client='') {
    $premier_inscrit = isset($data_form['premier_inscrit']) && is_array($data_form['premier_inscrit']) ? $data_form['premier_inscrit'] : array();

    /* CREATION DE LA LIGNE DANS LA BDD */
    $id_activite = sql_insertq('spip_asso_activites', array(
        'date' => date('Y-m-d H:i:s'), // Date et heure actuelles
        'id_evenement' => $id_evenement, // ID de l'événement
        'id_auteur' => $data_form['id_auteur'], // ID de l'auteur
        'nom_inscrit' => $premier_inscrit['nom'] ?? '', // Nom du premier inscrit
        'prenom_inscrit' => $premier_inscrit['prenom'] ?? '', // Prénom du premier inscrit
        'email_inscrit' => $premier_inscrit['email'] ?? '', // Email du premier inscrit
        'tel_inscrit' => $premier_inscrit['tel'] ?? '', // Téléphone du premier inscrit
        'association' => $data_form['association'] ?? '', // Association liée
        'statut' => $cal_result['statut'], // Statut de l'inscription
        'nom_participants' => $data_form['nom_participants'] ?? '', // Noms des participants
        'participants_json' => ie_construire_participants_json($data_form),
        'nombre_inscrits' => isset($data_form['nombre_inscrits_calcule']) ? intval($data_form['nombre_inscrits_calcule']) : (isset($data_form['nombre_participants']) ? intval($data_form['nombre_participants']) : 0), // Nombre total de participants
        'nb_invite' => $data_form['nb_invite'] ?? 0, // Invités hors famille
        'commentaire' => $data_form['commentaire'] ?? '', // Commentaire de l'inscription
        'id_transaction' => $id_transaction, // ID de la transaction
        'tarifs_selectionnes' => $nouvelle_transaction,
        'annotation' => $data_form['annotation'] ?? '', // Annotation supplémentaire
        'journal' => $message_journal, // Message de journalisation
        'ip_inscrit' => $data_form['id_auteur'] ? '' : $ip_client ,// IP du client, si disponible
    ));

    return $id_activite; // Retourne l'ID de l'activité créée
}
/**
 * Modifie une activité associative.
 *
 * Cette fonction met à jour les informations d'une activité associative dans la base de données.
 *
 * @param int $id_activite L'ID de l'activité à modifier.
 * @param array $data_form Les données du formulaire d'inscription, incluant les informations du participant.
 * @param array $cal_result Les résultats du calcul de l'inscription.
 * @param int $id_transaction L'ID de la transaction associée.
 * @param string $nouvelle_transaction Les nouvelles informations de la transaction.
 * @param string $message_journal Le message de journal à enregistrer.
 * @return int L'ID de l'activité modifiée.
 */
function modifier_asso_activites($id_activite,$data_form,$cal_result,$id_transaction,$nouvelle_transaction,$message_journal,$ip_client=''){

    $premier_inscrit = isset($data_form['premier_inscrit']) && is_array($data_form['premier_inscrit']) ? $data_form['premier_inscrit'] : array();

    /* MODIFICATION DE LA LIGNE DANS LA BDD */
    $id_activite = sql_updateq('spip_asso_activites', array(
            'nom_inscrit' => $premier_inscrit['nom'] ?? '',
            'prenom_inscrit' => $premier_inscrit['prenom'] ?? '',
            'email_inscrit' => $premier_inscrit['email'] ?? '',
            'tel_inscrit' => $premier_inscrit['tel'] ?? '',
            'association' => $data_form['association_parente'] ?? ($data_form['association'] ?? ''),
            'statut' => $cal_result['statut'],
            'nom_participants' => $data_form['nom_participants'] ?? '',
            'participants_json' => ie_construire_participants_json($data_form),
            'nombre_inscrits' => isset($data_form['nombre_inscrits_calcule']) ? intval($data_form['nombre_inscrits_calcule']) : (isset($data_form['nombre_participants']) ? intval($data_form['nombre_participants']) : 0),
            'nb_invite' => $data_form['nb_invite'] ?? 0,
            'commentaire' => $data_form['commentaire'] ?? '',
            'id_transaction' => $id_transaction,
            'tarifs_selectionnes' => $nouvelle_transaction,
            'annotation' => $data_form['annotation'] ?? '',
            'journal' => $message_journal,
            'ip_inscrit' => $data_form['id_auteur'] ? '' : $ip_client ,// IP du client, si disponible
        )
        ,"id_activite = $id_activite");

    return $id_activite;
}
/**
 * Prépare une entrée de journal pour une inscription à une activité.
 *
 * Cette fonction génère un message de journal en fonction du statut de l'inscription
 * et du contexte (public ou privé).
 *
 * @param string $cal_result_statut Le statut de l'inscription (preinscrit, ok, liste_attente).
 * @param string $prive_ou_public Optionnel. Spécifie si le contexte est 'public' ou 'privé'. Par défaut, c'est 'prive'.
 * @return string Le message de journal formaté.
 */
function preparer_entree_journal($cal_result_statut,$prive_ou_public='prive'){
    /* PREPARATION Message Journal */
    if ($cal_result_statut == 'preinscrit') {
        $entree_journal = ($prive_ou_public == 'prive') ? _T('association_evenements:journal_preinscription_site_prive') : _T('association_evenements:journal_preinscription_site_public');
    } elseif ($cal_result_statut == 'ok') {
        $entree_journal = ($prive_ou_public == 'prive') ? _T('association_evenements:journal_inscription_site_prive') : _T('association_evenements:journal_inscription_site_public');
    } elseif ($cal_result_statut == 'liste_attente') {
        $entree_journal = ($prive_ou_public == 'prive') ? _T('association_evenements:journal_liste_attente_site_prive') : _T('association_evenements:journal_liste_attente_site_public');
    }
    $message_journal = date('d/m/Y H:i:s') . ' : ' . $entree_journal . '<br>';

    return $message_journal;
}
/**
 * Notifie l'inscription à une activité.
 *
 * Cette fonction envoie un email de notification en fonction du statut de l'inscription
 * et du contexte (public ou privé).
 *
 * @param int $id_activite L'ID de l'activité.
 * @param int $id_evenement L'ID de l'événement.
 * @param string $cal_result_statut Le statut de l'inscription (preinscrit, ok, liste_attente, modification).
 * @param string $prive_ou_public Optionnel. Spécifie si le contexte est 'public' ou 'privé'. Par défaut, c'est 'prive'.
 * @return int L'ID de la tâche de la file d'attente de jobs.
 */
function notifier_inscription_activite($id_activite,$id_evenement,$cal_result_statut,$prive_ou_public='prive'){

    if($cal_result_statut == 'preinscrit') {
        $type = ($prive_ou_public == 'prive') ? 'preinscription_backend' : 'preinscription_frontend';
    }elseif($cal_result_statut == 'ok'){
        $type = ($prive_ou_public == 'prive') ? 'inscription_backend' : 'inscription_frontend';
    }elseif($cal_result_statut == 'liste_attente') {
        $type = ($prive_ou_public == 'prive') ? 'attente_backend' : 'attente_frontend';
    }elseif($cal_result_statut == 'modification') {
        $type = ($prive_ou_public == 'prive') ? 'modification_backend' : 'modification_frontend';
    }else {
        spip_log('Erreur de notification d\'inscription à l\'activité : type inconnu' . $cal_result_statut, 'association' . _LOG_ERREUR);
        $type = 'inconnu';
    }
    // On envoie un mail de notification
    return job_queue_add('facteur_envoyer_mail_activites', 'Notification - ' . $type .' - ' .$id_activite, $arguments = array($id_evenement, $type, array($id_activite)), $file = '', $no_duplicate = FALSE, $time=0, $priority=0) ;
}
/**
 * Insère une transaction pour une activité.
 *
 * Cette fonction crée une nouvelle transaction dans la base de données pour une activité,
 * en calculant le montant hors taxes en fonction du pourcentage de taxe configuré.
 *
 * @param float $montant_total Le montant total de la transaction.
 * @param int $id_auteur L'ID de l'auteur de la transaction.
 * @return int L'ID de la transaction insérée.
 */
function inserer_transaction_activites($montant_total, $id_auteur)
{
	if (!association_evenements_integration_active('association_paiements')) {
		return 0;
	}
    $pourcentage_taxe = isset($GLOBALS['association_metas']['meta_cfg_taxe_evenement']) ? $GLOBALS['association_metas']['meta_cfg_taxe_evenement'] : false;
    if ($pourcentage_taxe) {
        $montant_ht = $montant_total * (1 - $pourcentage_taxe / 100);
    } else {
        $montant_ht = $montant_total;
    }
    $options = array(
        'id_auteur' => $id_auteur,
        'montant_ht' => $montant_ht,
        'montant' => $montant_total,
        'force' => true
    );
    include_spip('inc/association_evenements_paiements');
    $id_transaction = association_evenements_transaction_creer($montant_total, $options);

    return $id_transaction;
}

/**
 * Modifie une transaction existante pour une activité.
 *
 * Cette fonction met à jour le montant total et le montant hors taxes d'une transaction
 * dans la base de données en fonction du pourcentage de taxe configuré.
 *
 * @param float $montant_total Le montant total de la transaction.
 * @param int $id_transaction L'ID de la transaction à modifier.
 * @return bool Retourne true après la mise à jour de la transaction.
 */
function modifier_transaction_activites($montant_total, $id_transaction) {
	if (!association_evenements_integration_active('association_paiements')) {
		return true;
	}
    // Récupère le pourcentage de taxe configuré, s'il existe
    $pourcentage_taxe = isset($GLOBALS['association_metas']['meta_cfg_taxe_evenement']) ? $GLOBALS['association_metas']['meta_cfg_taxe_evenement'] : false;

    if ($pourcentage_taxe) {
        $montant_ht = $montant_total * (1 - $pourcentage_taxe / 100);
    } else {
        $montant_ht = $montant_total;
    }

    // Prépare les données à mettre à jour
    $data = array(
        'montant_ht' => $montant_ht,
        'montant' => $montant_total
    );

    // Met à jour la transaction dans la base de données
	return association_evenements_transaction_modifier($id_transaction, $data);
}

/**
 * Vérifie si une soumission de formulaire contient des indicateurs de spam.
 *
 * Cette fonction effectue plusieurs vérifications anti-spam :
 * - Champs invisibles remplis (honeypot)
 * - Présence de caractères chinois
 * - Présence de caractères russes
 * - Utilisation d'emails avec domaine qq.com
 * - Noms et prénoms identiques
 *
 * @param array $post_data Les données POST du formulaire
 * @param string $email_field Nom du champ contenant l'email
 * @param string $prenom_field Nom du champ contenant le prénom (optionnel)
 * @param string $nom_field Nom du champ contenant le nom (optionnel)
 * @param bool $check_identical_names Vérifier si nom et prénom sont identiques
 * @param int $id_auteur ID de l'auteur connecté (pour ignorer certaines vérifications si connecté)
 * @return array|bool Tableau des erreurs ou false si aucun spam détecté
 */
function verifier_spam_formulaire_inscription($post_data, $email_field = 'email_inscrit', $prenom_field = 'prenom_inscrit', $nom_field = 'nom_inscrit', $check_identical_names = true, $id_auteur = 0) {
    $erreurs = array();
    $ip_client = $_SERVER['REMOTE_ADDR'] ?? '';

    // Vérification SPAM - Champs Invisible
    // Honeypot: accepter la clé basée sur l'heure courante et l'heure précédente
    // ainsi que quelques clés fallback statiques pour réduire les faux positifs.
    $hour_now = date('YmdH');
    $hour_prev = date('YmdH', time() - 3600);
    $keys = array(
        'input_'.md5($hour_now),
        'checkbox_'.md5($hour_now),
        'input_'.md5($hour_prev),
        'checkbox_'.md5($hour_prev),
        'nobot',
        'input_nobot',
        'checkbox_nobot',
    );
    foreach ($keys as $k) {
        if (!empty($post_data[$k])) {
            $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
            association_evenements_inscription_debug('spam_nobot', array('champ_present' => 'oui'));
            break;
        }
    }

    // Vérification des données pour chaque champ
    foreach($post_data as $key => $value) {
        // Vérification SPAM Caractères chinois
        if(!is_array($value) && preg_match("/\p{Han}+/u", $value, $matches)) {
            $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
            spip_log('spam caractères chinois', 'spam_form_inscription_evenement' . _LOG_INFO);
        }

        // Vérification SPAM Caractères russes
        if(!is_array($value) && preg_match('/[\x{0410}-\x{042F}\x{0430}-\x{044F}\x{0401}\x{0451}]/u', $value, $matches)) {
            $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
            spip_log('spam caractères russes', 'spam_form_inscription_evenement' . _LOG_INFO);
        }

        // Vérification SPAM lien hypertexte
        if(!is_array($value) && strpos($value, "http") !== false) {
            $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
            spip_log('spam lien hypertexte', 'spam_form_inscription_evenement' . _LOG_INFO);
        }
    }

    // Vérification SPAM chinois - email qq.com
    if (isset($post_data[$email_field])) {
        $domain_email = explode('@', $post_data[$email_field]);
        $email_domain = isset($domain_email[1]) ? strtolower($domain_email[1]) : '';
        // Allow configuration via association_metas.meta_cfg_spam_email_blacklist as comma-separated string
        $default_blacklist = array('qq.com', 'mailbox.in.ua');
        $blacklist_meta = isset($GLOBALS['association_metas']['meta_cfg_spam_email_blacklist']) ? $GLOBALS['association_metas']['meta_cfg_spam_email_blacklist'] : '';
        $blacklist = $blacklist_meta ? array_map('trim', explode(',', $blacklist_meta)) : $default_blacklist;
        foreach ($blacklist as $blocked) {
            if ($blocked && $email_domain == strtolower($blocked)) {
                $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
                association_evenements_inscription_debug('spam_domaine_email', array('domaine_bloque' => 'oui'));
                break;
            }
        }
    }

    // Vérification SPAM - nom et prénom identique
    if ($check_identical_names && !$id_auteur && isset($post_data[$prenom_field]) && isset($post_data[$nom_field]) && $post_data[$prenom_field] == $post_data[$nom_field]) {
        $erreurs['message_erreur'] = _T('pass_rien_a_faire_ici');
        association_evenements_inscription_debug('spam_identite_identique', array('identite_presente' => 'oui'));
    }
    return !empty($erreurs) ? $erreurs : false;
}


/**
 * Inscrit un participant à la liste de diffusion des newsletters.
 *
 * Cette fonction garantit l'existence de la liste `liste_inscrit_activite`
 * puis abonne l'email (y compris si le mailsubscriber existe déjà).
 *
 * @param array $data_form Les données du formulaire d'inscription, incluant les informations du participant.
 * @return bool Retourne true.
 */
function inscrire_participant_mailsubscriber($data_form){

    include_spip('inc/mailsubscribers');
    include_spip('mailsubscribers_fonctions');

    // Charge la fonction d'abonnement à la newsletter
    $newsletter_subscribe = charger_fonction("subscribe","newsletter");
    $email_subscriber = $data_form['premier_inscrit']['email'];

    // Garantir l'existence de la liste cible via API avant abonnement.
    ie_creer_liste_diffusion_activite_si_absente('liste_inscrit_activite');

    // Option 3=oui: abonner aussi les mailsubscribers déjà existants à la liste.
    $options = array();
    $options['notify'] = false;
    $options['force'] = true;
    $options['listes'] = array('liste_inscrit_activite');
    $options['nom']  = $data_form['premier_inscrit']['prenom'] . ' ' . $data_form['premier_inscrit']['nom'];
    $newsletter_subscribe($email_subscriber,$options);

    return true;
}
/**
 * Crée la liste mailsubscribers si elle n'existe pas.
 */
function ie_creer_liste_diffusion_activite_si_absente($identifiant_liste = 'liste_inscrit_activite') {
    $identifiant_liste = trim((string)$identifiant_liste);
    if ($identifiant_liste === '') {
        return false;
    }

    $liste = sql_fetsel(
        'id_mailsubscribinglist',
        'spip_mailsubscribinglists',
        "identifiant=" . sql_quote($identifiant_liste) . " AND statut!='poubelle'"
    );
    if (!empty($liste['id_mailsubscribinglist'])) {
        return intval($liste['id_mailsubscribinglist']);
    }

    include_spip('action/editer_objet');
    $id_liste = objet_inserer('mailsubscribinglist');
    if ($id_liste) {
        objet_modifier('mailsubscribinglist', intval($id_liste), array(
            'identifiant' => $identifiant_liste,
            'titre' => 'Inscrits activites',
            'statut' => 'ouverte',
        ));
    }

    if (!$id_liste) {
        spip_log('ie_creer_liste_diffusion_activite_si_absente: creation impossible pour ' . $identifiant_liste, 'association' . _LOG_CRITIQUE);
        return false;
    }

    return intval($id_liste);
}

