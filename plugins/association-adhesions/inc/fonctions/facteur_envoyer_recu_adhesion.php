<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Envoie un reçu d'adhésion par email à un auteur.
 *
 * Cette fonction récupère les informations nécessaires sur une transaction,
 * un auteur et une catégorie, génère un reçu et l'envoie par email.
 *
 * @param int $id_auteur L'identifiant de l'auteur.
 * @param int $id_transaction L'identifiant de la transaction.
 * @param string $type_recu Le type de reçu (par exemple, 'encaissement').
 * @param string|null $raison (Optionnel) La raison associée au reçu.
 * @return array True si l'email a été envoyé avec succès, False sinon.
 */
function facteur_envoyer_recu_adhesion($id_auteur, $id_transaction, $type_recu, $raison = '') {
    include_spip('inc/filtres');
    include_spip('inc/notifications_emails');
    include_spip('inc/cotisations_devises');

    // Validation des entrées
    $id_transaction = intval($id_transaction);
    $id_auteur = intval($id_auteur);

    // Récupération des données de la transaction
    $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=$id_transaction");
    if (empty($query_transaction)) {
        association_log('notifications', "Transaction introuvable : $id_transaction", 'critique');
        return array('success' => false, 'message' => "Transaction introuvable : $id_transaction");
    }

    if ($query_transaction['montant'] == 0) {
        association_log('notifications', "Montant de la transaction nul : $id_transaction", 'critique');
        return array('success' => false, 'message' => "Montant de la transaction nul : $id_transaction");
    }

    // Récupération de l'auteur
    if (empty($id_auteur)) {
        $id_auteur = $query_transaction['id_auteur'];
    }
    // Récupérer dynamiquement les colonnes disponibles dans spip_auteurs
    $desc = sql_showtable('spip_auteurs', true);
    $fields = array('email','nom','nom_famille','prenom');
    if (is_array($desc) && isset($desc['field']) && array_key_exists('nom_entreprise', $desc['field'])) {
        $fields[] = 'nom_entreprise';
    }
    $query_auteur = sql_fetsel(implode(',', $fields), 'spip_auteurs', "id_auteur=$id_auteur");
    if (empty($query_auteur)) {
        association_log('notifications', "Auteur introuvable : $id_auteur", 'critique');
        return array('success' => false, 'message' => "Auteur introuvable : $id_auteur");
    }

    // Lightweight fallback: if nom_entreprise is empty, use nom_famille + prenom
    if (empty($query_auteur['nom_entreprise'])) {
        $fallback = trim( (
            ($query_auteur['nom_famille'] ?? '') . ' ' . ($query_auteur['prenom'] ?? '')
        ) );
        if ($fallback !== '') {
            $query_auteur['nom_entreprise'] = $fallback;
        }
    }

    // Récupération des données du compte et de la catégorie
    $query_compte = sql_fetsel('*', 'spip_asso_comptes', "id_transaction=$id_transaction");
    if (empty($query_compte)) {
        association_log('notifications', "Compte introuvable pour la transaction : $id_transaction", 'critique');
        return array('success' => false, 'message' => "Compte introuvable pour la transaction : $id_transaction");
    }

    // Si le compte n'a pas nom_entreprise, propager le fallback depuis l'auteur
    if (empty($query_compte['nom_entreprise']) && !empty($query_auteur['nom_entreprise'])) {
        $query_compte['nom_entreprise'] = $query_auteur['nom_entreprise'];
    }

    // Construire un nom affichable (Prénom Nom ou nom_famille si présent). Si compte entreprise définit le nom d'entreprise.
    $nom_adherent = '';
    if (!empty($query_auteur['prenom']) || !empty($query_auteur['nom'])) {
        $nom_adherent = trim(($query_auteur['prenom'] ?? '') . ' ' . ($query_auteur['nom'] ?? ''));
    } elseif (!empty($query_auteur['nom_famille'])) {
        $nom_adherent = $query_auteur['nom_famille'];
    }
    // Si c'est une entreprise et qu'on a l'info dans le compte ou l'auteur, l'utiliser
    if (empty($nom_adherent)) {
        if (!empty($query_compte['nom_entreprise'])) {
            $nom_adherent = $query_compte['nom_entreprise'];
        } elseif (!empty($query_auteur['nom_entreprise'])) {
            $nom_adherent = $query_auteur['nom_entreprise'];
        } else {
            $nom_adherent = $query_auteur['email'] ?? '';
        }
    }

    $query_categorie = sql_fetsel('id_categorie, valeur, cotisation, devise', 'spip_asso_categories_adherents', "id_categorie=" . intval($query_compte['id_categorie']));
    $titre_categorie = $query_categorie['valeur'] ?? _T('association_adhesions:categorie_inconnue');
    $devise_recu = $query_transaction['devise'] ?? '';
    if ($devise_recu === '') {
        $devise_recu = $query_categorie['devise'] ?? '';
    }

    // Génération du numéro de reçu
    $numero_recu = 'ADH' . $id_auteur . 'TRA'. $id_transaction;

    // Définition du sujet et du modèle en fonction du type de reçu
    if($type_recu == 'encaissement'){
        // Passer le nom de l'adhérent pour construire le sujet: "Nom - Reçu de votre paiement"
        $sujet = _T('association_adhesions:email_recu_encaissement_adhesion_sujet', array(
                'nom_adherent' => $nom_adherent,
                'titre_categorie' => $titre_categorie,
                'numero_recu' => $numero_recu)
               );
        $modele = 'notifications/recu_encaissement_adhesion';
    }

    // Préparation du contenu du modèle
    $fond_content = array(
        'id_transaction' => $id_transaction,
        'id_auteur' => $id_auteur,
        'id_compte' => $query_compte['id_compte'],
        'id_categorie' => $query_categorie['id_categorie'],
        'devise' => association_cotisation_resoudre_devise($devise_recu),
        'numero_recu' => $numero_recu,
        //'raison' => $raison
                                    );
    $html = recuperer_fond($modele, $fond_content);
    // Logger la taille du rendu HTML pour détecter un éventuel blocage ou rendu vide
    association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.render', 'len' => is_string($html) ? strlen($html) : 0, 'id_transaction' => $id_transaction], 'critique');

    // Préparation de l'email
    $email_adherent = isset($query_auteur['email']) ? $query_auteur['email'] : '';
    // Parser BCC multiple si la configuration contient plusieurs adresses

    $bcc_meta = $GLOBALS['association_metas']['config_envoi_recu_adhesion_cc'] ?? '';
    association_log('notifications', 'bcc_meta=' . $bcc_meta, 'critique');
    $bcc_parsed = parser_emails_depuis_config($bcc_meta);

    association_log('notifications', 'bcc_parsed=' . print_r($bcc_parsed, true), 'critique');
    // facteur_envoyer_app accepte soit string soit tableau. Si vide, garder false.
    $bcc = !empty($bcc_parsed) ? $bcc_parsed : false;

    // Journaliser le début de traitement pour le debug des jobs
    association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.start', 'id_auteur' => $id_auteur, 'id_transaction' => $id_transaction, 'email' => $email_adherent, 'bcc' => $bcc], 'critique');

    // Envoi de l'email
    try {
        $envoyer = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc, array('enqueued' => true, 'use_queue' => false));
        // Logger le résultat renvoyé par facteur
        association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.end', 'result' => $envoyer, 'id_transaction' => $id_transaction, 'id_auteur' => $id_auteur], 'critique');
        return $envoyer;
    } catch (Throwable $e) {
        association_log('notifications', 'Exception dans facteur_envoyer_recu_adhesion: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), 'critique');
        return array('success' => false, 'message' => 'Exception: ' . $e->getMessage());
    }
}
