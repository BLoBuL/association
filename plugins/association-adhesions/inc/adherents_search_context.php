<?php
/**
 * Contexte de recherche unifié pour les adhérents
 *
 * Permet de combiner filtres GET (période, statut, type)
 * avec recherches POST (rapide/avancée) de manière cohérente
 *
 * @package SPIP\Association\Adherents
 */

if (!defined('_ECRIRE_INC_VERSION')) return;
include_spip('prive/squelettes/contenu/adherents_fonctions');

class AdherentsSearchContext {

    // Filtres standards (GET)
    public $periode = null;
    public $statut_interne = null;
    public $type_adherent = null;
    public $type_compte = null;
    public $type_cotisation = null; // Nouveau filtre basé sur les catégories
    public $filtres_dynamiques = array();

    // Recherche rapide (POST)
    public $recherche_type = null; // 'rapide' ou 'avancee'
    public $nom = null;
    public $prenom = null;
    public $email = null;
    public $mobile = null;

    // Recherche avancée (POST)
    public $type_recherche = null; // 'statut_adhesion' ou 'multicritere'
    public $operateur_recherche = 'AND';
    public $criteres_avances = array();

    // Métadonnées
    public $periode_data = null; // Objet période complet

    /**
     * Crée un contexte depuis la requête courante (GET + POST)
     * @return AdherentsSearchContext
     */
    public static function fromRequest() {
        // 0. EFFACEMENT DE LA RECHERCHE SI DEMANDÉ
        if (_request('clear_search')) {
            if (!isset($_SESSION)) {
                session_start();
            }
            unset($_SESSION['adherents_recherche_rapide']);
            unset($_SESSION['adherents_recherche_avancee']);
        }

        // Démarrer session pour la persistance des filtres
        if (!isset($_SESSION)) {
            session_start();
        }

        // Réinitialisation ciblée d'un filtre demandé par l'UI
        $clear_filters = _request('clear_filters');
        if ($clear_filters) {
            $to_clear = is_array($clear_filters) ? $clear_filters : explode(',', (string)$clear_filters);
            foreach ($to_clear as $k) {
                $k = trim($k);
                if ($k) unset($_SESSION['adherents_filtres'][$k]);
            }
        }

        if (association_recherche_avancee_reset_demandee()) {
            unset($_SESSION['adherents_recherche_avancee']);
        }

        $champs_recherche = array('_input_nom_famille', '_input_prenom', '_input_email', '_input_mobile');
        if (($champ_a_effacer = _request('clear_search_field')) !== null) {
            $cibles = ($champ_a_effacer === '__all__') ? $champs_recherche : array($champ_a_effacer);
            foreach ($cibles as $champ) {
                if (!in_array($champ, $champs_recherche, true)) {
                    continue;
                }
                if (isset($_REQUEST[$champ])) unset($_REQUEST[$champ]);
                if (isset($_GET[$champ])) unset($_GET[$champ]);
                if (isset($_POST[$champ])) unset($_POST[$champ]);
                if (!empty($_SESSION['adherents_recherche_rapide'][$champ])) {
                    unset($_SESSION['adherents_recherche_rapide'][$champ]);
                }
            }
            if (!empty($_SESSION['adherents_recherche_rapide'])) {
                $reste = false;
                foreach ($champs_recherche as $champ) {
                    if (!empty($_SESSION['adherents_recherche_rapide'][$champ])) {
                        $reste = true;
                        break;
                    }
                }
                if (!$reste) {
                    unset($_SESSION['adherents_recherche_rapide']);
                }
            }
        }

        $ctx = new self();

        // 1. FILTRES GET (toujours pris en compte)
        // On lit les valeurs de la requête (brutes) pour persistance
        $raw_periode = isset($_REQUEST['periode']) ? $_REQUEST['periode'] : null;
        $raw_statut = isset($_REQUEST['statut_interne']) ? $_REQUEST['statut_interne'] : null;
        $raw_type = isset($_REQUEST['type_adherent']) ? $_REQUEST['type_adherent'] : null;
        $raw_compte = isset($_REQUEST['type_compte']) ? $_REQUEST['type_compte'] : null;

        // Normaliser 'tout' en null (pas de filtre) pour le contexte logique
        $ctx->periode = _request('periode');
        if ($ctx->periode === 'tout') $ctx->periode = null;

        $ctx->statut_interne = _request('statut_interne');
        if ($ctx->statut_interne === 'tout') $ctx->statut_interne = null;

        $ctx->type_adherent = _request('type_adherent');
        if ($ctx->type_adherent === 'tout') $ctx->type_adherent = null;

        // Conserver la valeur brute de type_compte (y compris 'defaut')
        $type_compte_req = _request('type_compte');
        $ctx->type_compte = $type_compte_req ? $type_compte_req : null;

        // Type cotisation (basé sur les catégories)
        $ctx->type_cotisation = _request('type_cotisation');
        if ($ctx->type_cotisation === 'tout') $ctx->type_cotisation = null;

        // 1.b Persistance des filtres dans la session si présents dans la requête
        $raw_type_cotisation = isset($_REQUEST['type_cotisation']) ? $_REQUEST['type_cotisation'] : null;
        $filters = array(
            'periode' => $raw_periode,
            'statut_interne' => $raw_statut,
            'type_adherent' => $raw_type,
            'type_compte' => $raw_compte,
            'type_cotisation' => $raw_type_cotisation,
        );
        foreach ($filters as $k => $v) {
            if (array_key_exists($k, $_REQUEST)) {
                if (!isset($_SESSION['adherents_filtres'])) $_SESSION['adherents_filtres'] = array();
                $_SESSION['adherents_filtres'][$k] = $v;
            }
        }

        // 1.c Si aucun paramètre n'est fourni, restaurer depuis la session
        foreach (array('periode','statut_interne','type_adherent','type_compte','type_cotisation') as $k) {
            if (!array_key_exists($k, $_REQUEST) // pas de paramètre explicite
                && (empty($ctx->$k))) {
                if (!empty($_SESSION['adherents_filtres'][$k])) {
                    $val = $_SESSION['adherents_filtres'][$k];
                    // Traduire 'tout' en null pour les filtres concernés
                    if (in_array($k, array('periode','statut_interne','type_adherent','type_cotisation'), true) && $val === 'tout') {
                        $ctx->$k = null; // filtre désactivé
                    } else {
                        $ctx->$k = $val;
                    }
                }
            }
        }

        // 1.d Filtres dynamiques configurés (champs extras radio/select)
        $dyn_defs = function_exists('liste_filtres_dynamiques_adherents') ? liste_filtres_dynamiques_adherents() : array();
        if (!isset($_SESSION['adherents_filtres'])) {
            $_SESSION['adherents_filtres'] = array();
        }
        foreach ($dyn_defs as $nom_filtre => $def) {
            $valeur_requete = _request($nom_filtre);
            if ($valeur_requete !== null) {
                $_SESSION['adherents_filtres'][$nom_filtre] = $valeur_requete;
                $ctx->filtres_dynamiques[$nom_filtre] = ($valeur_requete === 'tout') ? null : $valeur_requete;
            } elseif (isset($_SESSION['adherents_filtres'][$nom_filtre])) {
                $valeur_session = $_SESSION['adherents_filtres'][$nom_filtre];
                $ctx->filtres_dynamiques[$nom_filtre] = ($valeur_session === 'tout') ? null : $valeur_session;
            } else {
                $ctx->filtres_dynamiques[$nom_filtre] = null;
            }
        }

        // 2. RECHERCHE RAPIDE (POST, GET, ou SESSION)
        $recherche_rapide_active = false;

        // a) Depuis POST (nouvelle recherche)
        if (!empty($_POST['recherche']) && $_POST['recherche'] === 'rapide') {
            $ctx->recherche_type = 'rapide';
            $recherche_rapide_active = true;
        }

        // b) Lire depuis GET ou POST (priorité POST)
        $ctx->nom = _request('_input_nom_famille');
        $ctx->prenom = _request('_input_prenom');
        $ctx->email = _request('_input_email');
        $ctx->mobile = _request('_input_mobile');

        // Si clear_search est demandé, s'assurer que les _input_* sont effacés de la requête
        if (_request('clear_search')) {
            if (isset($_REQUEST['_input_nom_famille'])) unset($_REQUEST['_input_nom_famille']);
            if (isset($_REQUEST['_input_prenom'])) unset($_REQUEST['_input_prenom']);
            if (isset($_REQUEST['_input_email'])) unset($_REQUEST['_input_email']);
            if (isset($_REQUEST['_input_mobile'])) unset($_REQUEST['_input_mobile']);
            if (isset($_GET['_input_nom_famille'])) unset($_GET['_input_nom_famille']);
            if (isset($_GET['_input_prenom'])) unset($_GET['_input_prenom']);
            if (isset($_GET['_input_email'])) unset($_GET['_input_email']);
            if (isset($_GET['_input_mobile'])) unset($_GET['_input_mobile']);
            if (isset($_POST['_input_nom_famille'])) unset($_POST['_input_nom_famille']);
            if (isset($_POST['_input_prenom'])) unset($_POST['_input_prenom']);
            if (isset($_POST['_input_email'])) unset($_POST['_input_email']);
            if (isset($_POST['_input_mobile'])) unset($_POST['_input_mobile']);
        }

        if ($ctx->nom || $ctx->prenom || $ctx->email || $ctx->mobile) {
            if (!$ctx->recherche_type) {
                $ctx->recherche_type = 'rapide';
            }
            $recherche_rapide_active = true;
        }

        // c) Si pas de recherche POST/GET, lire depuis la session
        if (!$recherche_rapide_active && empty($_POST['type_recherche'])) {
            if (!isset($_SESSION)) {
                session_start();
            }

            // Restaurer recherche rapide depuis session
            if (!empty($_SESSION['adherents_recherche_rapide'])) {
                $session_rapide = $_SESSION['adherents_recherche_rapide'];
                $ctx->recherche_type = 'rapide';
                $ctx->nom = isset($session_rapide['_input_nom_famille']) ? $session_rapide['_input_nom_famille'] : null;
                $ctx->prenom = isset($session_rapide['_input_prenom']) ? $session_rapide['_input_prenom'] : null;
                $ctx->email = isset($session_rapide['_input_email']) ? $session_rapide['_input_email'] : null;
                $ctx->mobile = isset($session_rapide['_input_mobile']) ? $session_rapide['_input_mobile'] : null;
            }
            // Restaurer recherche avancée depuis session
            elseif (!empty($_SESSION['adherents_recherche_avancee'])) {
                $session_avancee = $_SESSION['adherents_recherche_avancee'];
                $ctx->recherche_type = 'avancee';
                $ctx->type_recherche = isset($session_avancee['type_recherche']) ? $session_avancee['type_recherche'] : null;
                $ctx->operateur_recherche = isset($session_avancee['operateur_recherche']) ? $session_avancee['operateur_recherche'] : 'AND';
                $ctx->criteres_avances = $session_avancee;
            }
        }

        // 3. RECHERCHE AVANCÉE (POST - nouvelle recherche)
        if (!empty($_POST['type_recherche'])) {
            $ctx->recherche_type = 'avancee';
            $ctx->type_recherche = $_POST['type_recherche'];
            $ctx->operateur_recherche = _request('operateur_recherche') ?: 'AND';
            $ctx->criteres_avances = $_POST;
        }

        // 4. VALEURS PAR DÉFAUT DES FILTRES (si rien n'est fourni et rien en session)
        // Inclure les fonctions utilitaires
        include_spip('prive/squelettes/contenu/adherents_fonctions');

        // 4.1 Période en cours par défaut (uniquement si pas de paramètre ET pas de valeur session)
        if (!$ctx->periode && !isset($_REQUEST['periode']) && empty($_SESSION['adherents_filtres']['periode'])) {
            $periodes = filtre_liste_periodes_cotisations(0, false);
            if (is_array($periodes)) {
                foreach ($periodes as $p) {
                    if (!empty($p['encours'])) {
                        $ctx->periode = isset($p['libelle']) ? $p['libelle'] : null;
                        // On laisse periode_data être positionnée lors du toSQL/buildPeriodeSQL
                        break;
                    }
                }
            }
        }

        // 4.2 Statut interne par défaut = 'ok'
        if (!$ctx->statut_interne && !isset($_REQUEST['statut_interne']) && empty($_SESSION['adherents_filtres']['statut_interne'])) {
            $ctx->statut_interne = 'ok';
        }

        // 4.3 Type de compte par défaut = 'compte_principal' (si gestion active)
        if (!$ctx->type_compte && !isset($_REQUEST['type_compte']) && empty($_SESSION['adherents_filtres']['type_compte']) && function_exists('est_actif_gestion_comptes_secondaires') && est_actif_gestion_comptes_secondaires()) {
            $ctx->type_compte = 'compte_principal';
        }

        return $ctx;
    }

    /**
     * Génère les critères SQL combinés
     * @param array $liste_periodes Liste des périodes disponibles
     * @return string Critères SQL
     */
    public function toSQL($liste_periodes = array()) {
        $criteres = array();

        // 1. FILTRE PÉRIODE (overlapping)
        if ($this->periode && $this->periode !== 'tout') {
            $critere_periode = $this->buildPeriodeSQL($liste_periodes);
            if ($critere_periode) {
                $criteres[] = $critere_periode;
            }
        }

        // 2. FILTRE STATUT INTERNE
        if ($this->statut_interne && $this->statut_interne !== 'tout') {
            $criteres[] = "statut_interne = " . sql_quote($this->statut_interne);
        }

        // 3. FILTRE TYPE ADHÉRENT
        if ($this->type_adherent && $this->type_adherent !== 'tout') {
            $col = $this->getTypeAdherentColumn();
            if ($col) {
                $criteres[] = $col . " = " . sql_quote($this->type_adherent);
            }
        }

        // 3b. FILTRE TYPE COMPTE (principal/secondaire) - ignorer 'defaut'
        if ($this->type_compte && $this->type_compte !== 'defaut') {
            if ($this->type_compte === 'compte_principal') {
                $criteres[] = "(auteur_compte_principal IS NULL OR auteur_compte_principal = '' OR auteur_compte_principal = 0)";
            } elseif ($this->type_compte === 'compte_secondaire') {
                $criteres[] = "(auteur_compte_principal IS NOT NULL AND auteur_compte_principal != '' AND auteur_compte_principal > 0)";
            }
        }

        // 3c. FILTRE TYPE COTISATION (basé sur les catégories de cotisation)
        if ($this->type_cotisation && $this->type_cotisation !== 'tout') {
            $critere_cotisation = $this->getCotisationCriteriaByType($this->type_cotisation);
            if ($critere_cotisation) {
                $criteres[] = $critere_cotisation;
            }
        }

        // 4. RECHERCHE RAPIDE
        if ($this->recherche_type === 'rapide') {
            $critere_rapide = $this->buildRechercheRapideSQL();
            if ($critere_rapide) {
                $criteres[] = $critere_rapide;
            }
        }

        // 5. RECHERCHE AVANCÉE
        if ($this->recherche_type === 'avancee' && !empty($this->criteres_avances)) {
            include_spip('formulaires/inc/adherents_recherche_avancee');
            $critere_avance = preparer_criteres_adherents($this->criteres_avances);
            if ($critere_avance) {
                $criteres[] = $critere_avance;
            }
        }

        // 6. Filtres dynamiques (champs extras configurés)
        $criteres_dynamiques = $this->buildDynamicFiltersSQL();
        if (!empty($criteres_dynamiques)) {
            $criteres = array_merge($criteres, $criteres_dynamiques);
        }

        // Combiner tous les critères avec AND
        $criteres_filtres = array_filter($criteres);
        return !empty($criteres_filtres) ? implode(' AND ', $criteres_filtres) : '';
    }

    /**
     * Construit le critère SQL pour la période (overlapping)
     */
    private function buildPeriodeSQL($liste_periodes) {
        // Trouver la période sélectionnée
        $periode_selectionnee = null;
        $p_param = trim((string)$this->periode);
        $p_param = rawurldecode($p_param);
        $norm_param = strtolower(str_replace(array(' ', '/','\\'), '-', $p_param));

        foreach ($liste_periodes as $periode) {
            if (!isset($periode['libelle'])) continue;
            $libelle = (string)$periode['libelle'];

            if ($libelle === $p_param) {
                $periode_selectionnee = $periode;
                break;
            }

            if (is_numeric($p_param)) {
                $annee = intval($p_param);
                if ((isset($periode['annee_debut']) && intval($periode['annee_debut']) === $annee)
                    || (isset($periode['annee_fin']) && intval($periode['annee_fin']) === $annee)) {
                    $periode_selectionnee = $periode;
                    break;
                }
            }

            $norm_lib = strtolower(str_replace(array(' ', '/','\\'), '-', $libelle));
            if ($norm_lib === $norm_param) {
                $periode_selectionnee = $periode;
                break;
            }
        }

        if (!$periode_selectionnee) {
            return null;
        }

        $this->periode_data = $periode_selectionnee;

        $date_debut = $periode_selectionnee['date_debut'];
        $date_fin = $periode_selectionnee['date_fin'];
        $annee_fin = $periode_selectionnee['annee_fin'];

        // Calculer date limite inscription selon le mode
        $type_periode = isset($GLOBALS['association_metas']['validite'])
            ? $GLOBALS['association_metas']['validite'] : 'annuelle';

        if ($type_periode === 'scolaire') {
            // Mode scolaire : la limite d'inscription doit correspondre à la date
            // d'expiration des cotisations configurée (meta
            // date_scolaire_nouvelle, ex: '15/09'). Cela permet d'inclure
            // les adhérents "à jour" jusqu'à cette date (inclus).
            $date_scolaire_nouvelle = isset($GLOBALS['association_metas']['date_scolaire_nouvelle'])
                ? $GLOBALS['association_metas']['date_scolaire_nouvelle'] : '30/09';
            list($jour_nouvelle, $mois_nouvelle) = explode('/', $date_scolaire_nouvelle);
            // Construire la date dans l'année de fin de la période (annee_fin)
            $date_inscription_limite = date('Y-m-d', mktime(0, 0, 0, intval($mois_nouvelle), intval($jour_nouvelle), intval($annee_fin)));
        } else {
            // Mode anniversaire : utiliser la date de fin de période
            $date_inscription_limite = $date_fin;
        }

        // CAS 1 : Adhésions antérieures toujours valides
        $critere_anterieurs = "(
            inscription IS NOT NULL 
            AND inscription != '0000-00-00' 
            AND inscription != '0000-00-00 00:00:00'
            AND inscription < " . sql_quote($date_debut) . "
            AND (
                (validite IS NOT NULL AND validite != '0000-00-00' AND validite != '0000-00-00 00:00:00' AND validite >= " . sql_quote($date_debut) . ")
                OR
                (validite IS NULL OR validite = '0000-00-00' OR validite = '0000-00-00 00:00:00')
            )
        )";

        // CAS 2 : Nouvelles inscriptions pendant la période
        // On inclut aussi, uniquement pour la période en cours, les auteurs
        // sans date d'inscription (NULL / '0000-00-00') en les traitant
        // comme s'ils étaient inscrits au début de la période.
        $critere_nouveaux_base = "(
            inscription IS NOT NULL 
            AND inscription != '0000-00-00' 
            AND inscription != '0000-00-00 00:00:00'
            AND inscription >= " . sql_quote($date_debut) . "
            AND inscription <= " . sql_quote($date_inscription_limite) . "
        )";

        // Si la période sélectionnée est celle en cours, ajouter les comptes
        // sans date d'inscription (ils seront considérés comme inscrits
        // à la date_debut de la période et donc inclus).
        $critere_inscription_vide = "(inscription IS NULL OR inscription = '0000-00-00' OR inscription = '0000-00-00 00:00:00')";

        if (!empty($periode_selectionnee['encours'])) {
            // Ajouter la clause alternative qui inclut les inscriptions vides
            $critere_nouveaux = "( " . $critere_nouveaux_base . " OR " . $critere_inscription_vide . " )";
        } else {
            $critere_nouveaux = $critere_nouveaux_base;
        }

        return "(" . $critere_anterieurs . " OR " . $critere_nouveaux . ")";
    }

    /**
     * Construit le critère SQL pour la recherche rapide
     */
    private function buildRechercheRapideSQL() {
        include_spip('formulaires/inc/adherents_recherche_avancee');

        $criteres_rapide = array(
            '_input_nom_famille' => $this->nom,
            '_input_prenom' => $this->prenom,
            '_input_email' => $this->email,
            '_input_mobile' => $this->mobile,
        );

        return preparer_criteres_adherents($criteres_rapide);
    }

    /**
     * Détecte la colonne type_adherent (type_adherent ou radio_type_adherent)
     */
    private function getTypeAdherentColumn() {
        $desc = sql_showtable('spip_auteurs', true);
        if ($desc) {
            if (!empty($desc['field']['type_adherent'])) return 'type_adherent';
            if (!empty($desc['field']['radio_type_adherent'])) return 'radio_type_adherent';
        }
        return null;
    }

    /**
     * Génère le critère SQL pour filtrer par type de cotisation (via les catégories)
     * @param string $type Type de cotisation recherché
     * @return string Critère SQL (subquery EXISTS)
     */
    private function getCotisationCriteriaByType($type) {
        // Récupérer les IDs des catégories qui correspondent à ce type
        $categories = sql_allfetsel(
            'id_categorie',
            'spip_asso_categories_adherents',
            "type_adherent = " . sql_quote($type) . " AND statut='ok'"
        );

        if (!$categories || count($categories) == 0) {
            return "1=0"; // Aucune catégorie trouvée, retourne un critère qui ne matche rien
        }

        $ids_categories = array();
        foreach ($categories as $cat) {
            $ids_categories[] = intval($cat['id_categorie']);
        }

        // Générer une subquery EXISTS pour filtrer les adhérents ayant une cotisation de ce type
        $subquery = "EXISTS (
            SELECT 1 FROM spip_asso_cotisations
            WHERE spip_asso_cotisations.id_auteur = spip_auteurs.id_auteur
            AND spip_asso_cotisations.id_categorie IN (" . implode(',', $ids_categories) . ")
        )";

        return $subquery;
    }

    /**
     * Convertit en paramètres URL (pour préservation dans les liens)
     * @return array
     */
    public function toURLParams() {
        $params = array();

        if ($this->periode) $params['periode'] = $this->periode;
        if ($this->statut_interne) $params['statut_interne'] = $this->statut_interne;
        if ($this->type_adherent) $params['type_adherent'] = $this->type_adherent;

        // Pour recherche rapide, on peut aussi passer en GET
        if ($this->recherche_type === 'rapide') {
            if ($this->nom) $params['nom'] = $this->nom;
            if ($this->prenom) $params['prenom'] = $this->prenom;
            if ($this->email) $params['email'] = $this->email;
            if ($this->mobile) $params['mobile'] = $this->mobile;
        }

        foreach ($this->filtres_dynamiques as $nom => $valeur) {
            if ($valeur !== null && $valeur !== '') {
                $params[$nom] = $valeur;
            }
        }

        return $params;
    }

    /**
     * Vérifie si au moins un filtre/recherche est actif
     * @return bool
     */
    public function hasActiveFilters() {
        return ($this->periode && $this->periode !== 'tout')
            || ($this->statut_interne && $this->statut_interne !== 'tout')
            || ($this->type_adherent && $this->type_adherent !== 'tout')
            || ($this->type_compte && $this->type_compte !== 'defaut')
            || ($this->type_cotisation && $this->type_cotisation !== 'tout')
            || $this->nom
            || $this->prenom
            || $this->email
            || $this->mobile
            || $this->recherche_type === 'avancee'
            || $this->hasActiveDynamicFilters();
    }

    /**
     * Génère un résumé textuel des filtres actifs
     * @return array
     */
    public function getActiveFiltersLabels() {
        $labels = array();

        if ($this->periode && $this->periode !== 'tout') {
            $labels[] = array(
                'type' => 'periode',
                'label' => 'Période : ' . $this->periode,
                'param' => 'periode',
            );
        }

        if ($this->statut_interne && $this->statut_interne !== 'tout') {
            $labels[] = array(
                'type' => 'statut',
                'label' => 'Statut : ' . $this->statut_interne,
                'param' => 'statut_interne',
            );
        }

        if ($this->type_adherent && $this->type_adherent !== 'tout') {
            $labels[] = array(
                'type' => 'type',
                'label' => 'Type : ' . $this->type_adherent,
                'param' => 'type_adherent',
            );
        }

        if ($this->nom) {
            $labels[] = array(
                'type' => 'recherche',
                'label' => 'Nom : ' . $this->nom,
                'param' => 'nom',
            );
        }

        if ($this->prenom) {
            $labels[] = array(
                'type' => 'recherche',
                'label' => 'Prénom : ' . $this->prenom,
                'param' => 'prenom',
            );
        }

        if ($this->email) {
            $labels[] = array(
                'type' => 'recherche',
                'label' => 'Email : ' . $this->email,
                'param' => 'email',
            );
        }

        if ($this->mobile) {
            $labels[] = array(
                'type' => 'recherche',
                'label' => 'Mobile : ' . $this->mobile,
                'param' => 'mobile',
            );
        }

        if ($this->recherche_type === 'avancee') {
            $labels[] = array(
                'type' => 'recherche',
                'label' => 'Recherche avancée active',
                'param' => null, // Pas de suppression simple
            );
        }

        if (!empty($this->filtres_dynamiques)) {
            foreach ($this->filtres_dynamiques as $nom => $valeur) {
                if ($valeur === null || $valeur === '') {
                    continue;
                }
                $labels[] = array(
                    'type' => 'extra',
                    'label' => $nom . ' : ' . $valeur,
                    'param' => $nom,
                );
            }
        }

        return $labels;
    }

    /**
     * Construit les critères SQL associés aux filtres dynamiques
     */
    private function buildDynamicFiltersSQL(){
        if (empty($this->filtres_dynamiques)) {
            return array();
        }

        $desc = sql_showtable('spip_auteurs', true);
        if (!$desc || empty($desc['field'])) {
            return array();
        }

        $criteres = array();
        foreach ($this->filtres_dynamiques as $nom => $valeur) {
            if ($valeur === null || $valeur === '') {
                continue;
            }
            if (!isset($desc['field'][$nom])) {
                continue;
            }
            $criteres[] = 'spip_auteurs.' . $nom . ' = ' . sql_quote($valeur);
        }

        return $criteres;
    }

    /**
     * Indique si un filtre dynamique est actif
     */
    private function hasActiveDynamicFilters(){
        if (empty($this->filtres_dynamiques)) {
            return false;
        }
        foreach ($this->filtres_dynamiques as $valeur) {
            if ($valeur !== null && $valeur !== '') {
                return true;
            }
        }
        return false;
    }
 }
