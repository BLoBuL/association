<?php
/**
 * Fonctions globales du plugin Association
 *
 * Ce fichier contient les fonctions utilitaires et helpers du plugin Association,
 * organisées par thématiques :
 * - Gestion des événements (agenda)
 * - Authentification et mot de passe
 * - Newsletters et listes de diffusion
 * - Rôles d'association
 * - Filtres de périodes et cotisations
 * - Helpers divers
 *
 * @package SPIP\Association\Fonctions
 * @author Blobul
 * @version 2025
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('association_paiements_fonctions');

/**
 * Ramene un message Saisies/CVT a son texte affichable.
 *
 * Cette fonction est declaree dans le fichier de fonctions du plugin afin que
 * le filtre homonyme soit disponible des le calcul des squelettes SPIP.
 *
 * @param mixed $message
 * @return string
 */


/**
 * Filtre public pour exporter les donnees RGPD metier de l'association.
 *
 * @param int $id_auteur
 * @return array
 */
function association_export_donnees_auteur($id_auteur) {
	include_spip('inc/rgpd_export');
	return association_rgpd_export_donnees_auteur($id_auteur);
}

/**
 * Genere un nom de fichier explicite pour l'export RGPD association.
 *
 * @param mixed $date
 * @param int $id_auteur
 * @return string
 */
function association_nom_fichier_export_rgpd($date = null, $id_auteur = 0) {
	$adresse_site = $GLOBALS['meta']['adresse_site'] ?? '';
	$host = parse_url($adresse_site, PHP_URL_HOST);
	if (!$host) {
		$host = preg_replace(',^https?://,i', '', (string)$adresse_site);
		$host = explode('/', $host)[0] ?? '';
	}
	$host = strtolower(trim((string)$host));
	$host = preg_replace('/^www\./', '', $host);
	$host = preg_replace('/[^a-z0-9.-]+/', '-', $host);
	$host = trim(str_replace('.', '_', $host), '-_');
	if ($host === '') {
		$host = 'site';
	}

	$timestamp = $date ? strtotime((string)$date) : time();
	if (!$timestamp) {
		$timestamp = time();
	}

	$suffixe_auteur = intval($id_auteur) > 0 ? '-' . intval($id_auteur) : '';

	return 'export-association-' . $host . $suffixe_auteur . '-' . date('Y-m-d', $timestamp) . '.json';
}


// ============================================================================
// GESTION DES ÉVÉNEMENTS (AGENDA)
// ============================================================================


/**
 * Charge les valeurs du formulaire d'édition d'événement
 *
 * Prépare les données pour l'affichage du formulaire d'édition d'un événement,
 * en chargeant les valeurs par défaut depuis la configuration de l'association
 * et en gérant les spécificités (fuseaux horaires, répétitions, inscriptions).
 *
 * @param string|int $id_evenement ID de l'événement ou 'new' pour création
 * @param int $id_article ID de l'article parent de l'événement
 * @param string $retour URL de retour après édition
 * @param int $lier_trad Lier à une traduction (0 ou 1)
 * @param string $config_fonc Fonction de configuration personnalisée
 * @param array $row Données de ligne déjà chargées (optionnel)
 * @param string $hidden Champs cachés additionnels
 * @return array Tableau des valeurs du formulaire
 */


/**
 * Configuration par défaut du formulaire d'édition d'événement
 *
 * @return array Configuration vide (utilise les valeurs par défaut)
 */


/**
 * Vérifie les données soumises du formulaire d'édition d'événement
 *
 * Valide les champs obligatoires (titre, dates), vérifie la cohérence des dates,
 * contrôle les autorisations et vérifie les montants pour les événements payants.
 *
 * @param string|int $id_evenement ID de l'événement ou 'new'
 * @param int $id_article ID de l'article parent
 * @param string $retour URL de retour
 * @param int $lier_trad Lier à une traduction
 * @param string $config_fonc Fonction de configuration
 * @param array $row Données de ligne
 * @param string $hidden Champs cachés
 * @return array Tableau des erreurs (vide si pas d'erreur)
 */



// ============================================================================
// AUTHENTIFICATION ET MOT DE PASSE
// ============================================================================

/**
 * Traite la réinitialisation du mot de passe d'un adhérent
 *
 * Permet à un adhérent de définir un nouveau mot de passe via un lien
 * de réinitialisation avec jeton. Après modification, l'adhérent est
 * automatiquement connecté et redirigé vers sa fiche.
 *
 * @param int|null $id_auteur ID de l'auteur/adhérent
 * @param string|null $jeton Jeton de réinitialisation
 * @return array Résultat avec 'redirect' ou 'message_erreur'
 */



// ============================================================================
// NEWSLETTERS ET LISTES DE DIFFUSION
// ============================================================================

/**
 * Construit le critere des listes automatiques selon le statut d'adhesion.
 *
 * Le role webmaster est un droit technique et ne doit pas retirer un adherent
 * d'une liste metier lorsque son statut interne correspond.
 *
 * @param string $statut_interne Statut interne attendu.
 * @return string Critere SQL borne aux comptes actifs.
 */


/**
 * Prépare une liste d'auteurs pour une newsletter selon leur statut interne
 *
 * @param string $statut_interne Statut interne des adhérents (ok, prospect, echu, relance, sorti)
 * @return array Tableau d'auteurs avec nom et email
 */


/**
 * Liste des adhérents "à jour" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */


/**
 * Liste des adhérents "prospects" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */


/**
 * Liste des adhérents "échus" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */


/**
 * Liste des adhérents "en relance" pour synchronisation avec Mailsubscribers
 * @return array Liste d'auteurs avec nom et email
 */


/**
 * Liste des conjoints d'adhérents pour synchronisation avec Mailsubscribers
 * @return array Liste de conjoints avec nom et email
 */



// ============================================================================
// HELPERS DIVERS
// ============================================================================

/**
 * Filtre pour obtenir l'identifiant de configuration bancaire
 *
 * Wrapper autour de bank_config_id() pour utilisation dans les squelettes SPIP.
 *
 * @param array $config Configuration bancaire
 * @return string Identifiant de configuration bancaire
 */

/**
 * Traite le formulaire d'édition de newsletter
 *
 * Gère la soumission du formulaire d'édition de newsletter, en tenant compte
 * des différents statuts (prepa, prop, prog) et des modifications possibles.
 * Corrige le bug d'incompatibilité avec CFG/Cextras dans la programmation.
 *
 * @param string $id_newsletter ID de la newsletter ou 'new' pour création
 * @param string $retour URL de retour après traitement
 * @param int $lier_trad Lier à une traduction (0 ou 1)
 * @param string $config_fonc Fonction de configuration personnalisée
 * @param array $row Données de ligne
 * @param string $hidden Champs cachés additionnels
 * @return array Résultat du traitement du formulaire
 */

/**
 * Désérialise récursivement les valeurs d'un tableau
 *
 * Parcourt un tableau et désérialise automatiquement les valeurs sérialisées.
 * Utile pour traiter les données des champs extras ou de configuration.
 *
 * @param array $array Tableau contenant potentiellement des valeurs sérialisées
 * @return array Tableau avec valeurs désérialisées
 */
function deserialize_values($array) {
	foreach ($array as $key => $value) {
		if (is_string($value) && association_is_serialized($value)) {
			$array[$key] = unserialize($value);
		}
	}
	return $array;
}

/**
 * Vérifie si une valeur est sérialisée
 *
 * Détecte si une valeur est une chaîne sérialisée PHP.
 * Préfixée pour éviter les collisions avec d'autres plugins (SPIP 4.2+, WordPress, etc.)
 *
 * @param mixed $value Valeur à vérifier
 * @return bool True si la valeur est sérialisée, false sinon
 */
function association_is_serialized($value) {
	return (is_string($value) && ($value === 'b:0;' || @unserialize($value) !== false));
}


/**
 * Filtre pour obtenir les rôles d'association nettoyés
 *
 * Récupère les rôles bénévoles/fonctions de l'association et les nettoie
 * en supprimant les groupes vides et en filtrant optionnellement par
 * liste de fonctions autorisées.
 *
 * @param array $fonctions_benevole Liste des fonctions à conserver (vide = toutes)
 * @return array Tableau des rôles avec groupes nettoyés
 */



// ============================================================================
// FILTRES DE COTISATIONS ET PÉRIODES
// ============================================================================

/**
 * Vérifie si le filtre "type de cotisation" doit être affiché
 *
 * Le filtre type_cotisation (basé sur les catégories) est affiché uniquement
 * si la colonne type_adherent n'existe PAS dans spip_auteurs (ancien système).
 *
 * @return bool True si le filtre doit être affiché, false sinon
 */


/**
 * Liste les types de cotisation depuis les catégories d'adhésion
 *
 * Récupère les valeurs distinctes du champ type_adherent depuis les catégories
 * actives, utilisé pour filtrer les cotisations par type.
 *
 * @return array Tableau associatif [type => type] des types disponibles
 */



/**
 * Convertit un type_adherent en liste d'IDs de catégories
 *
 * Retourne une chaîne d'IDs compatible avec le critère SPIP {id_categorie IN ...}.
 * Utilisé pour filtrer les cotisations par type via les catégories associées.
 *
 * @param string $type Type d'adhérent recherché (ex: "entreprise", "individuel")
 * @return string Liste d'IDs séparés par virgule (ex: "1,3,5") ou "0" si aucun
 */



/**
 * Liste les périodes de cotisations contenant au moins une cotisation
 *
 * Génère les périodes de cotisations (annuelles ou scolaires selon config)
 * depuis la première inscription jusqu'à aujourd'hui, et ne retourne que
 * les périodes où au moins une cotisation existe en base de données.
 *
 * Chaque période contient :
 * - libelle : "2024/2025" (scolaire) ou "2024" (annuelle)
 * - date_debut, date_fin : Bornes de la période
 * - encours : true si période actuelle
 * - nb_cotisations : Nombre de cotisations dans la période
 *
 * @param int $limite Nombre maximum de périodes à retourner (0 = illimité)
 * @param bool $avec_stats [Obsolète] Stats toujours incluses
 * @return array Tableau de périodes triées par date décroissante
 */


/**
 * Retourne le libellé de la période par défaut (période en cours)
 *
 * @return string Libellé de la période encours ou chaîne vide
 */


/**
 * Trouve une période par son libellé
 *
 * @param string $libelle Libellé de la période (ex: "2024/2025") ou "tout"
 * @return array|null Array de la période trouvée ou null
 */


/**
 * Retourne la date de début d'une période
 *
 * @param string $libelle Libellé de la période ou "tout"
 * @return string Date au format Y-m-d ou '0000-00-00' si "tout"
 */


/**
 * Retourne la date de fin d'une période
 *
 * @param string $libelle Libellé de la période ou "tout"
 * @return string Date au format Y-m-d ou '9999-12-31' si "tout"
 */

/**
 * Récupère les filtres effectifs pour la page cotisations
 *
 * Gère la persistance des filtres en session et combine avec les paramètres URL.
 * La priorité est : URL > Session > Défaut.
 *
 * Filtres gérés :
 * - periode : Libellé de période (défaut = encours, 'tout' = vide)
 * - statut_cotisation : Statut de la cotisation (ok, attente, etc.)
 * - reinscription : Type (reinscription/inscription)
 * - id_categorie : ID de catégorie de cotisation
 * - type_cotisation : Type via catégories (entreprise, individuel, etc.)
 *
 * @return array Tableau des filtres effectifs
 */


/**
 * Wrapper utilisé par les squelettes : #VAL|liste_periodes_cotisations
 * Détermine le contexte (entreprise / adherent) depuis la requête
 * et retourne la liste des périodes appropriée.
 */


/**
 * Filtre SPIP: scalar_val
 * Assure que la valeur fournie est une chaîne simple :
 * - si null ou vide -> retourne le défaut
 * - si tableau -> retourne le premier élément non vide (string) ou implode(',', ...) selon cas
 * - sinon retourne la valeur telle quelle
 * Utilisation dans les squelettes : (#GET{periode_contexte}|scalar_val{defaut})
 */
function filtre_scalar_val($val, $defaut = ''){
    if (is_array($val)){
        // Chercher le premier élément scalar non vide
        foreach ($val as $v) {
            if ($v === null) continue;
            if (is_array($v)) continue;
            $s = trim((string)$v);
            if ($s !== '') return $s;
        }
        // Si aucun élément scalar, tenter d'imploder
        $flat = array();
        array_walk_recursive($val, function($v) use (&$flat){ if (!is_array($v)) $flat[] = (string)$v; });
        if (count($flat)) return implode(',', $flat);
        return $defaut;
    }
    if ($val === null || $val === '') return $defaut;
    return (string)$val;
}
