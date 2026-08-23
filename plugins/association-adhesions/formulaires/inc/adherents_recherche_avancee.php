<?php
if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Fonction globale pour générer les saisies de la recherche avancée.
 *
 * Cette fonction génère les saisies nécessaires pour la recherche avancée des adhérents.
 * Elle inclut des options pour sélectionner le type de recherche (statut d'adhésion ou multicritère)
 * et formate les saisies en fonction des champs extras configurés.
 *
 * @return array Un tableau de saisies pour la recherche avancée.
 */
function adherents_recherche_avancee_saisies(){
    $saisies = array();
    $saisies_statut_adhesion_saisie = adherents_recherche_avancee_statut_adhesion_saisie();
    $saisies_multicritere_saisie = adherents_recherche_avancee_multicritere_saisie();
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'operateur',
            'label' => _T('association:fieldset_adherents_recherche_label'),
            'conteneur_class' => 'fieldset_type_recherche',
        ),
        'saisies' => array(
            array(
                'saisie' => 'radio',
                'options' => array(
                    'label' => _T('association:radio_type_recherche_label'),
                    'nom' => 'type_recherche',
                    'data' => array(
                        'statut_adhesion' => _T('association:radio_type_recherche_data_statut_adhesion'),
                        'multicritere' => _T('association:radio_type_recherche_data_multicritere')
                    ),
                    'defaut' => 'statut_adhesion',
                )
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_statut_adhesion',
                    'label' => _T('association:fieldset_statut_adhesion_label'),
                    'conteneur_class' => 'fieldset_statut_adhesion',
                    'afficher_si' => '@type_recherche@=="statut_adhesion"',
                ),
                'saisies' => $saisies_statut_adhesion_saisie,
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_multicritere',
                    'label' => _T('association:fieldset_multicritere_label'),
                    'conteneur_class' => 'fieldset_multicritere',
                    'afficher_si' => '@type_recherche@=="multicritere"',
                ),
                'saisies' => $saisies_multicritere_saisie,
            ),
        )
    );
    return $saisies;
}


/**
 * Génère les saisies pour la recherche avancée par statut d'adhésion.
 *
 * Cette fonction lit la configuration des champs extras des auteurs et génère
 * les saisies nécessaires pour la recherche avancée par statut d'adhésion.
 * Elle formate les saisies en fonction des champs extras configurés.
 *
 * @return array Un tableau de saisies pour la recherche avancée par statut d'adhésion.
 */
function adherents_recherche_avancee_statut_adhesion_saisie(){
    $saisies = array();
    $liste_champs_extra = lire_config('champs_extras_spip_auteurs');
    $liste_champs_table = array('statut_interne', 'inscription', 'validite');

    // Parcours des champs extras pour générer les saisies correspondantes
    foreach($liste_champs_extra as $champs_extra ) {
        $saisies[] = adherents_recherche_avancee_formater_saisie($champs_extra, $liste_champs_table,'statut_adhesion');
    }

    return array_values(array_filter($saisies));
}



/**
 * Formate une saisie pour la recherche avancée des adhérents.
 *
 * @param array $champs_extra Le champ extra à formater.
 * @param array $liste_champs_table La liste des champs de la table.
 * @return array|false La saisie formatée ou `false` si le champ ne répond pas aux critères.
 */
function adherents_recherche_avancee_formater_saisie(array $champs_extra, array $liste_champs_table, $type_recherche) {

    if (!empty($liste_champs_table) && !in_array($champs_extra['options']['nom'], $liste_champs_table) && $champs_extra['saisie'] != 'fieldset') {
        return [];
    }
    if (in_array($champs_extra['options']['nom'], ['statut_interne', 'inscription', 'validite']) && $type_recherche == 'statut_adhesion') {
        $champs_extra['options']['nom'] .= '_recherche_adhesion';
    } else {
        $champs_extra['options']['nom'] .= '_recherche_multicritere';
    }

    // Suppression des obligations et des valeurs par défaut
    $champs_extra['options']['obligatoire'] = $champs_extra['options']['defaut'] = false;

    // Modification des types de champ pour en faire des sélections multiples
    if (in_array($champs_extra['saisie'], ['radio', 'selection'])) {
        $champs_extra['saisie'] = ($champs_extra['saisie'] == 'radio') ? 'checkbox' : 'selection_multiple';
    }

    // Modification des types de champ case pour en faire des boutons radio
    if ($champs_extra['saisie'] == 'case') {
        $champs_extra['saisie'] = 'radio';
        $champs_extra['options']['data'] = saisies_tableau2chaine(array('on' => 'Oui','' => 'Non'));

    }

    // Correction de l'affichage des sélections multiples
    if ($champs_extra['saisie'] == 'selection_multiple') {
        $champs_extra['options']['cacher_option_intro'] = true;
        $champs_extra['options']['size'] = 5;
        return $champs_extra;
    }

    // Tag des champs de saisies libres pour générer une recherche approximative
    if ($champs_extra['saisie'] == 'input') {
        $champs_extra['options']['nom'] = '_input_' . $champs_extra['options']['nom'];
        return $champs_extra;
    }

    // Gestion des champs de type date
    if ($champs_extra['saisie'] == 'date') {

        $label = $champs_extra['options']['label'];
        $nom = $champs_extra['options']['nom'];
        return [
            'saisie' => 'fieldset',
            'options' => [
                'nom' => 'fieldset_' . $nom,
                'label' => $label,
                'conteneur_class' => 'fieldset_date',
                'explication' => _T('association:recherche_fieldset_date_explication'),
            ],
            'saisies' => [
                [
                    'saisie' => 'date',
                    'options' => [
                        'label' => $label . ' (début)',
                        'nom' => '_date_' . $nom . '_debut',
                        'conteneur_class' => 'date_debut',
                        'obligatoire' => false,
                        'defaut' => false,
                    ]
                ],
                [
                    'saisie' => 'date',
                    'options' => [
                        'label' => $label . ' (fin)',
                        'nom' => '_date_' . $nom . '_fin',
                        'conteneur_class' => 'date_fin',
                        'obligatoire' => false,
                        'defaut' => false
                    ]
                ],
            ]
        ];
}
    // Gestion des champs de type fieldset
    if ($champs_extra['saisie'] == 'fieldset') {
        $champs_extra['saisies'] = array_values(array_filter(array_map(
            function($saisie) use ($liste_champs_table, $type_recherche) {
                return adherents_recherche_avancee_formater_saisie($saisie, $liste_champs_table, $type_recherche);
            },
            $champs_extra['saisies']
        )));
        return !empty($champs_extra['saisies']) ? $champs_extra : [];
    }

    return $champs_extra;
}
/**
 * Permet de nettoyer la config d'Inscription 3 pour l'exploiter facilement dans le moteur de recherche.
 *
 * Cette fonction lit la configuration d'Inscription 3 et extrait les champs marqués comme activés.
 * Elle retourne une liste de noms de champs sans les suffixes '_table' et '_table_nocreation'.
 *
 * @return array Un tableau contenant les noms des champs activés.
 */
function nettoyage_liste_config_inscription3() {
    $config_inscription3 = lire_config('inscription3');
    $liste_champs_table = [];

    foreach ($config_inscription3 as $champ => $champ_valeur) {
        if (strpos($champ, '_table') !== false && $champ_valeur == 'on') {
            $liste_champs_table[] = str_replace(['_table_nocreation', '_table'], '', $champ);
        }
    }

    return $liste_champs_table;
}
/**
 * Génère les saisies pour la recherche avancée multicritère des adhérents.
 *
 * Cette fonction lit la configuration des champs extras des auteurs et génère
 * les saisies nécessaires pour la recherche avancée multicritère. Elle inclut
 * un champ radio pour sélectionner l'opérateur de recherche (ET ou OU) et
 * formate les saisies en fonction des champs extras configurés.
 *
 * @return array Un tableau de saisies pour la recherche avancée multicritère.
 */
function adherents_recherche_avancee_multicritere_saisie(){
    $liste_champs_extra = lire_config('champs_extras_spip_auteurs');
    $liste_champs_table = nettoyage_liste_config_inscription3();
    $saisies = array();

    // Ajout d'un champ radio pour sélectionner l'opérateur de recherche (ET ou OU)
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'operateur',
            'label' => _T('association:fieldset_adherents_recherche_label'),
            'conteneur_class' => 'fieldset_operateur',
        ),
        'saisies' => array(
            array(
                'saisie' => 'radio',
                'options' => array(
                    'label' => _T('association:radio_type_recherche_label'),
                    'nom' => 'operateur_recherche',
                    'explication' => _T('association:radio_type_recherche_explication'),
                    'data' => array('OR' => 'Ou', 'AND' => 'Et'),
                    'defaut' => 'AND',
                )
            )
        )
    );

    // Parcours des champs extras pour générer les saisies correspondantes
    foreach ($liste_champs_extra as $champs_extra) {

        // Ajout des saisies pour les champs activés
        if (in_array($champs_extra['options']['nom'], $liste_champs_table) &&
            !in_array($champs_extra['saisie'], ['explication', 'oui_non']) ||
            $champs_extra['saisie'] == 'fieldset' &&
            empty($champs_extra['options']['disable'])) {

            $saisies[] = adherents_recherche_avancee_formater_saisie($champs_extra, $liste_champs_table,'multicritere');
        }
    }
    return array_values(array_filter($saisies));
}
/**
 * Fonction de réception d'une recherche et conversion de celle-ci en critère de recherche SQL.
 *
 * Cette fonction prend un contexte de recherche et le convertit en une chaîne de critères SQL.
 * Elle gère différents types de critères, y compris les entrées de texte, les dates de début et de fin.
 * Les critères sont combinés à l'aide d'un opérateur logique (ET ou OU).
 *
 * @param array $contexte Le contexte contenant les critères de recherche.
 * @return string La chaîne de critères SQL générée.
 */
function preparer_criteres_adherents($contexte){
    // En mode debug, logger le contexte brut reçu du formulaire pour diagnostic
    if (_request('var_mode') === 'debug') {
        association_log('adherents', 'preparer_criteres_adherents: contexte brut -> ' . var_export($contexte, true), 'erreur');
    }
     $liste_champs_table = nettoyage_liste_config_inscription3();

     // On va collecter les clauses individuellement puis les joindre proprement
     $criteres = array();

     // OPERATEUR DE RECHERCHE
     if(isset($contexte['operateur_recherche'])) {
        $operateur_recherche = ' '.$contexte['operateur_recherche']. ' ';
    } else {
        $operateur_recherche = ' AND ';
    }

   if($contexte['type_recherche'] == 'statut_adhesion'){
        $suffixe = '_recherche_adhesion';
    } else {
        $suffixe = '_recherche_multicritere';
    }
    foreach (array_filter($contexte) as $critere => $valeur) {
        $critere = str_replace($suffixe,'',$critere);
        if(strstr($critere,'_input_')){
            $critere = str_replace('_input_','',$critere);
            $type = 'input';
        } elseif(strstr($critere,'_date_') AND strstr($critere,'_debut')){
            $critere = str_replace('_date_','',$critere);
            $critere = str_replace('_debut','',$critere);
            $type = 'date_debut';
        } elseif(strstr($critere,'_date_') AND strstr($critere,'_fin')){
            $critere = str_replace('_date_','',$critere);
            $critere = str_replace('_fin','',$critere);
            $type = 'date_fin';
        } else {
            $type = false;
        }

        if(!empty($valeur)){
            // Cas particulier: recherche rapide sur les numéros (_input_mobile)
            if ($type === 'input' && $critere === 'mobile') {
                $needle_raw = trim((string)$valeur);
                // Normaliser: garder uniquement les chiffres
                $needle_digits = preg_replace('/\D+/', '', $needle_raw);
                if ($needle_digits !== '') {
                    // Générer variantes (0xxxxxxxxx <-> 33xxxxxxxxx ; 00xx -> xx)
                    $needles = array($needle_digits);
                    if (preg_match('/^0\d{9,}$/', $needle_digits)) {
                        $needles[] = '33' . substr($needle_digits, 1);
                    } elseif (preg_match('/^33\d{6,}$/', $needle_digits)) {
                        $needles[] = '0' . substr($needle_digits, 2);
                    } elseif (preg_match('/^00(\d{6,})$/', $needle_digits, $m)) {
                        $needles[] = $m[1];
                    }

                    // Décrire les colonnes candidates dynamiquement
                    $desc = sql_showtable('spip_auteurs', true);
                    $cols = array();
                    if ($desc && isset($desc['field']) && is_array($desc['field'])) {
                        foreach (array_keys($desc['field']) as $colname) {
                            // Filtrer les colonnes de téléphone (exclure les champs de consentement/confidentialité)
                            if (preg_match('/(tel|mobile|whats ?app|gsm)/i', $colname)
                                && !preg_match('/(confidentialite|consentement|rgpd|oui_non)/i', $colname)) {
                                $cols[] = $colname;
                            }
                        }
                    }

                    if (!empty($cols)) {
                        // Fonction SQL de normalisation optimisée (REPLACE chaînés)
                        $normalize = function($col){
                            // Version simplifiée avec REPLACE imbriqués (compatible MySQL 5.x+)
                            return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE($col,''),' ',''),'-',''),'.',''),'/',''),'(',''),')',''),'+','')";
                        };

                        // Construire des comparaisons d'égalité sur les colonnes normalisées
                        // Cela évite de récupérer des valeurs contenant la suite de chiffres recherchée
                        // (trop permissif avec LIKE '%...%').
                        $ors = array();
                        foreach ($cols as $c) {
                            $expr = $normalize($c);
                            foreach ($needles as $nd) {
                                // Comparaison stricte sur la version normalisée
                                $ors[] = $expr . ' = ' . sql_quote($nd);
                            }
                        }
                        // Si aucune colonne correspondante (théoriquement impossible), fallback en LIKE
                        if (empty($ors)) {
                            $bloc = '1=0';
                        } else {
                            $bloc = '(' . implode(' OR ', $ors) . ')';
                        }

                        // Collecter la clause, on se chargera de lier avec l'opérateur plus tard
                        $criteres[] = $bloc;
                    }
                }
                // Passer au suivant (ne pas appliquer le LIKE générique sur un champ "mobile" inexistant)
                continue;
            }

            // Cas particulier: recherche rapide sur les emails (_input_email)
            if ($type === 'input' && $critere === 'email') {
                $needle_raw = trim((string)$valeur);
                // Normaliser: trim + lowercase pour recherche insensible à la casse
                $needle_clean = strtolower($needle_raw);
                if ($needle_clean !== '') {
                    // Décrire les colonnes candidates dynamiquement
                    $desc = sql_showtable('spip_auteurs', true);
                    $cols = array();
                    if ($desc && isset($desc['field']) && is_array($desc['field'])) {
                        foreach (array_keys($desc['field']) as $colname) {
                            // Filtrer les colonnes d'email (exclure les champs de consentement/confidentialité)
                            if (preg_match('/email/i', $colname)
                                && !preg_match('/(confidentialite|consentement|rgpd|oui_non)/i', $colname)) {
                                $cols[] = $colname;
                            }
                        }
                    }

                    if (!empty($cols)) {
                        // Fonction SQL de normalisation (LOWER + TRIM)
                        $normalize_email = function($col){
                            return "LOWER(TRIM(COALESCE($col,'')))";
                        };

                        // Optimisation: CONCAT_WS si plusieurs colonnes
                        if (count($cols) > 1) {
                            // Version ultra-optimisée: CONCAT toutes les colonnes normalisées
                            $exprs = array_map($normalize_email, $cols);
                            $concat_all = "CONCAT_WS('|'," . implode(',', $exprs) . ")";
                            $bloc = '(' . $concat_all . ' LIKE ' . sql_quote('%' . $needle_clean . '%') . ')';
                        } else {
                            // Version standard si une seule colonne
                            $expr = $normalize_email($cols[0]);
                            $bloc = '(' . $expr . ' LIKE ' . sql_quote('%' . $needle_clean . '%') . ')';
                        }

                        // Collecter la clause pour jointure ultérieure
                        $criteres[] = $bloc;
                    }
                }
                // Passer au suivant (ne pas appliquer le LIKE générique sur un champ "email" inexistant)
                continue;
            }
        }

        if(in_array($critere,$liste_champs_table) && !empty($valeur)){
            if(is_array($valeur)){
                // Nettoyer le tableau : garder seulement les valeurs non vides
                $valeurs_propres = array_values(array_filter($valeur, function($v){ return !($v === null || $v === '' ); }));
                if (count($valeurs_propres) > 0) {
                    // Utiliser sql_in pour générer proprement la clause IN (sql_in gère l'échappement)
                    $cla = sql_in($critere, $valeurs_propres);
                    $criteres[] = $cla;
                }
            } elseif (is_string($valeur) && strpos($valeur, ',') !== false) {
                // Le formulaire a pu envoyer une chaîne CSV — convertir en tableau et utiliser sql_in
                $parts = array_map('trim', explode(',', $valeur));
                $parts = array_values(array_filter($parts, function($v){ return !($v === null || $v === ''); }));
                if (count($parts) > 0) {
                    $criteres[] = sql_in($critere, $parts);
                }
             } elseif($type == 'input'){
                 $criteres[] = $critere. ' LIKE ' . sql_quote('%'.$valeur . '%');
             } elseif($type == 'date_debut'){
                 $date_valeur = affdate($valeur, 'Y-m-d');
                 $criteres[] = $critere. ' >= ' . sql_quote($date_valeur);
             } elseif($type == 'date_fin'){
                 $date_valeur = affdate($valeur, 'Y-m-d');
                 $criteres[] = $critere. ' <= ' . sql_quote($date_valeur);
             } else {
                 $criteres[] = $critere. '=' . sql_quote($valeur);
             }
         }

        // Log des clauses construites (pour debug)
        if (_request('var_mode') === 'debug' && !empty($criteres)) {
            association_log('adherents', 'preparer_criteres_adherents: clauses_intermediaires -> ' . var_export($criteres, true), 'erreur');
        }
         unset($type);
     }

     // Vérification que la chaîne n'est pas vide avant de supprimer les derniers caractères
    if(!empty($criteres)){
        // Joindre proprement les clauses avec l'opérateur choisi
        // Pour éviter des erreurs de priorité entre AND/OR, parenthétiser chaque clause
        $criteres_escaped = array();
        foreach ($criteres as $c) {
            $c_trim = trim($c);
            if ($c_trim === '') continue;
            // Déjà parenthésé ? éviter double-parenthèse
            if ($c_trim[0] === '(' && substr($c_trim, -1) === ')') {
                $criteres_escaped[] = $c_trim;
            } else {
                $criteres_escaped[] = '(' . $c_trim . ')';
            }
        }
        $criteres_sql_clean = implode($operateur_recherche, $criteres_escaped);
        // Sécuriser : supprimer un éventuel opérateur logique en tête (ex: 'AND ...' ou 'OR ...')
        $criteres_sql_clean = preg_replace('/^\s*(AND|OR)\s+/i', '', (string)$criteres_sql_clean);
        // Et si, après trim, la chaîne est vide, retourner null
        $final = !empty(trim($criteres_sql_clean)) ? $criteres_sql_clean : null;
        // Log pour debug : la chaîne SQL finale produite par le moteur de recherche
        if (_request('var_mode') === 'debug') {
            association_log('adherents', 'preparer_criteres_adherents: criteres_sql -> ' . var_export($final, true), 'erreur');
        }
        return $final;
    }

     return null;
}
/**
 * Génère un tableau d'adhérents en fonction des critères SQL fournis.
 *
 * Cette fonction sélectionne les adhérents selon deux critères :
 * 1. Critères SQL généraux (statut, validité, etc.)
 * 2. Pour une période donnée : avoir au moins une cotisation dans cette période
 *
 * Un adhérent peut apparaître sur plusieurs périodes s'il a renouvelé à chaque fois.
 * Il n'apparaît PAS sur une période future même si sa validité la chevauche,
 * tant qu'il n'a pas de cotisation enregistrée pour cette période.
 *
 * @param string|array $criteres_sql Les critères SQL pour filtrer les adhérents (string ou array).
 * @param array $periode_selectionnee Période avec 'date_debut' et 'date_fin' pour filtrer les cotisations.
 * @return array Un tableau des adhérents avec leurs cotisations de la période.
 */
function generer_array_adherents($criteres_sql, $periode_selectionnee = array()) {
    // Critères SQL de base pour exclure les webmestres et les auteurs supprimés
    $criteres_sql_final = array(
        "webmestre = 'non'",
        "statut != '5poubelle'",
    );

    // Ajouter les critères supplémentaires si fournis (accepte string ou array)
    if (!empty($criteres_sql)) {
        if (is_array($criteres_sql)) {
            $criteres_sql_final = array_merge($criteres_sql_final, $criteres_sql);
        } else {
            $criteres_sql_final[] = $criteres_sql;
        }
    }

    // Normaliser les dates de la période
    $date_debut = null;
    $date_fin = null;
    if (!empty($periode_selectionnee['date_debut']) && !empty($periode_selectionnee['date_fin'])) {
        $date_debut = $periode_selectionnee['date_debut'];
        $date_fin = $periode_selectionnee['date_fin'];

        if (!preg_match('/\d{2}:\d{2}:\d{2}/', $date_debut)) {
            $date_debut .= ' 00:00:00';
        }
        if (!preg_match('/\d{2}:\d{2}:\d{2}/', $date_fin)) {
            $date_fin .= ' 23:59:59';
        }
    }

    // Préparer la liste des champs à sélectionner (ajouter type_adherent si présent)
    $desc_auteurs = sql_showtable('spip_auteurs', true);
    $col_type_adherent = null;
    if ($desc_auteurs) {
        if (!empty($desc_auteurs['field']['type_adherent'])) $col_type_adherent = 'type_adherent';
        elseif (!empty($desc_auteurs['field']['radio_type_adherent'])) $col_type_adherent = 'radio_type_adherent';
    }

    $champs = array('id_auteur', 'validite', 'inscription', 'statut', 'statut_interne');
    if ($col_type_adherent) {
        // sélectionner la colonne réelle mais aliaser en type_adherent pour un accès uniforme
        $champs[] = $col_type_adherent . ' AS type_adherent';
    }
    $champs = array_merge($champs, array('nom_famille', 'prenom', 'auteur_compte_principal', 'email'));

    $champs_dyn_config = association_config_champs_colonnes_triables();
    $champs_dyn_select = array();
    if ($desc_auteurs && isset($desc_auteurs['field']) && is_array($desc_auteurs['field'])) {
        foreach ($champs_dyn_config as $champ_cfg) {
            if (isset($desc_auteurs['field'][$champ_cfg])) {
                $alias = 'col_' . $champ_cfg;
                $champs[] = $champ_cfg . ' AS ' . $alias;
                $champs_dyn_select[$champ_cfg] = $alias;
            }
        }
    }

     $champs_auteurs = implode(',', $champs);

    // Exécution de la requête
    // préparer une représentation WHERE pour le log
    $where_for_log = is_array($criteres_sql_final) ? implode(' AND ', $criteres_sql_final) : (string)$criteres_sql_final;
    if (_request('var_mode') === 'debug') {
        association_log('adherents', 'generer_array_adherents: where -> ' . var_export($where_for_log, true), 'erreur');
    }
    $query_adherents = sql_select($champs_auteurs, 'spip_auteurs', $criteres_sql_final);

    $id_auteurs = array();
    $ids = array();

    while ($data = sql_fetch($query_adherents)) {
        $id = (int)$data['id_auteur'];
        $ids[] = $id;

        $tri_nom = '';
        if (!empty($data['nom_famille'])) {
            $tri_nom = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $data['nom_famille']));
            $tri_nom = preg_replace("~[^a-z0-9]+~", ' ', $tri_nom);
            $tri_nom = trim($tri_nom);
        }

        $id_auteurs[$id] = array(
            'id_auteur' => $id,
            'tri_nom' => $tri_nom,
            'validite' => isset($data['validite']) ? $data['validite'] : null,
            'inscription' => isset($data['inscription']) ? $data['inscription'] : null,
            'statut' => isset($data['statut']) ? $data['statut'] : null,
            'statut_interne' => isset($data['statut_interne']) ? $data['statut_interne'] : null,
            'type_adherent' => isset($data['type_adherent']) ? $data['type_adherent'] : null,
            'nom_famille' => isset($data['nom_famille']) ? $data['nom_famille'] : null,
            'prenom' => isset($data['prenom']) ? $data['prenom'] : null,
            'email' => isset($data['email']) ? $data['email'] : null,
            'auteur_compte_principal' => (isset($data['auteur_compte_principal']) && $data['auteur_compte_principal']) ? $data['auteur_compte_principal'] : null,
            'comptes' => array(),
            'colonnes_dyn' => array(),
        );

        foreach ($champs_dyn_select as $champ_cfg => $alias) {
            $valeur_dyn = isset($data[$alias]) ? $data[$alias] : null;
            $id_auteurs[$id][$alias] = $valeur_dyn;
            $id_auteurs[$id]['colonnes_dyn'][$champ_cfg] = $valeur_dyn;
        }
    }

    // Log résultat : ids trouvés
    if (_request('var_mode') === 'debug') {
        association_log('adherents', 'generer_array_adherents: ids trouvés -> ' . var_export(array_slice($ids,0,20), true) . ' (count=' . count($ids) . ')', 'erreur');
    }

    // Si aucun adhérent trouvé
    if (empty($ids)) {
        return $id_auteurs;
    }

    // Charger les cotisations pour information (optionnel)
    // NOTE : Le filtrage par période se fait maintenant dans les critères SQL (validité/inscription)
    // On ne filtre PLUS par cotisations ici, on les charge juste pour affichage
    if ($date_debut && $date_fin) {
        $where_comptes = array(
            "objet = " . sql_quote('cotisation'),
            "date >= " . sql_quote($date_debut),
            "date <= " . sql_quote($date_fin),
        );
        $champs_comptes = 'id_auteur, id_compte, date';
        $query_comptes = sql_select($champs_comptes, 'spip_asso_comptes', $where_comptes, '', 'id_auteur, date DESC');

        $nb_cotisations = 0;
        while ($compte = sql_fetch($query_comptes)) {
            $nb_cotisations++;
            $aid = (int)$compte['id_auteur'];
            if (isset($id_auteurs[$aid])) {
                $id_auteurs[$aid]['comptes'][] = $compte['id_compte'];
            }
        }
    }

    // Retourner tous les adhérents (le filtrage est fait dans les critères SQL)
    return $id_auteurs;
}
/**
* Affiche les résultats de la recherche avancée pour les membres.
*
* Cette fonction prépare les critères SQL pour la recherche avancée en fonction du contexte fourni.
* Elle génère ensuite un tableau d'ID de membres correspondant aux critères.
* Si aucun critère n'est fourni ou si aucun membre ne correspond aux critères, elle renvoie un tableau vide.
*
* @param array $contexte Le contexte contenant les critères de recherche.
* @return array Le tableau des ID de membres correspondant aux critères de recherche, ou un tableau vide si aucune correspondance n'est trouvée.
*/
function afficher_resultat_recherche_avancee($contexte) {
    $criteres_sql = preparer_criteres_adherents($contexte);

    if (!empty($criteres_sql)) {
       $id_auteurs = generer_array_adherents($criteres_sql);
       // Si la recherche retourne des résultats, on les renvoie
       if (is_array($id_auteurs) && count($id_auteurs)) {
           return $id_auteurs;
       }

       // Aucun résultat : fallback vers la liste des adhérents "à jour"
       // Définition : validite >= maintenant (on peut ajuster si besoin)
       $now = date('Y-m-d H:i:s');
       $fallback_criteres = array("validite >= " . sql_quote($now));
       if (_request('var_mode') === 'debug') {
           association_log('adherents', 'afficher_resultat_recherche_avancee: aucun resultat pour les criteres initiaux, fallback validite >= '. $now, 'erreur');
       }
       $id_auteurs = generer_array_adherents($fallback_criteres);
       return is_array($id_auteurs) && count($id_auteurs) ? $id_auteurs : array();
    }

    return array();
}
function association_config_champs_colonnes_triables(){
    static $cache = null;
    if (!is_null($cache)) {
        return $cache;
    }

    $cfg = null;
    if (isset($GLOBALS['association_metas']['config_champs_colonnes_adherents'])) {
        $cfg = $GLOBALS['association_metas']['config_champs_colonnes_adherents'];
    } else {
        include_spip('inc/config');
        $cfg = lire_config('association_metas/config_champs_colonnes_adherents');
    }

    if (is_string($cfg)) {
        $trim = trim($cfg);
        if ($trim !== '' && preg_match('/^[asibOCdN]:/', $trim)) {
             $decoded = @unserialize($cfg);
             if ($decoded !== false || $cfg === 'b:0;') {
                 $cfg = $decoded;
             }
         }
    }

    $fields = array();
    if (is_array($cfg)) {
        foreach ($cfg as $key => $value) {
            if (is_int($key)) {
                if ($value !== '' && $value !== null) {
                    $fields[] = $value;
                }
                continue;
            }
            if ($value === '' || $value === null || $value === 'non') {
                continue;
            }
            $fields[] = $key;
        }
    } elseif (is_string($cfg) && $cfg !== '') {
        if (strpos($cfg, ',') !== false) {
            $fields = array_map('trim', explode(',', $cfg));
        } else {
            $fields[] = trim($cfg);
        }
    }

    $cache = array_values(array_unique(array_filter($fields)));
    return $cache;
}
