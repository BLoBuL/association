<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

function facteur_envoyer_recu_participation($email_inscrit,$id_transaction,$id_activite,$type_recu,$raison = NULL) {
    include_spip('inc/filtres');
    include_spip('inc/notifications_emails');
    $query_activite = sql_fetsel('id_evenement,date', 'spip_asso_activites', "id_activite=$id_activite");
    $query_transaction = sql_fetsel('montant,devise','spip_transactions', "id_transaction=$id_transaction");
    $query_evenement = sql_fetsel("titre,date_debut","spip_evenements", "id_evenement=".$query_activite['id_evenement']);
    // Collecte des infos de l'evenement

    if($query_transaction['montant'] == 0){
        return false;
    }

    $id_evenement =$query_activite['id_evenement'];
    $titre_evenement = supprimer_numero($query_evenement['titre']);
    $numero_recu = affdate($query_evenement['date_debut'],'Y') . 'ACT'.$id_activite.'TRA'.$id_transaction;

    if($type_recu == 'encaissement'){
        // Tenter de retrouver l'auteur par email pour avoir un nom correct
        $nom_adherent = '';
        if (!empty($email_inscrit)) {
            // Construire dynamiquement la liste de champs disponibles dans spip_auteurs
            $desc = sql_showtable('spip_auteurs', true);
            $want = array('prenom','nom','nom_famille','nom_entreprise');
            $have = array();
            if (is_array($desc) && isset($desc['field']) && is_array($desc['field'])) {
                foreach ($want as $w) {
                    if (array_key_exists($w, $desc['field'])) $have[] = $w;
                }
            }
            if (empty($have)) {
                association_log('notifications', "facteur_envoyer_recu_participation: aucun des champs attendus (prenom, nom, nom_famille, nom_entreprise) n'existe dans spip_auteurs", 'critique');
            } else {
                $select = implode(',', $have);
                $auteur = sql_fetsel($select, 'spip_auteurs', 'email=' . sql_quote($email_inscrit));
                if ($auteur === false) {
                    association_log('notifications', "facteur_envoyer_recu_participation: erreur sql lors de la lecture de spip_auteurs pour email=" . $email_inscrit, 'critique');
                } elseif ($auteur) {
                    if ((!empty($auteur['prenom']) && in_array('prenom', $have)) || (!empty($auteur['nom']) && in_array('nom', $have))) {
                        $prenom = ($auteur['prenom'] ?? '');
                        $nom = ($auteur['nom'] ?? '');
                        $nom_adherent = trim($prenom . ' ' . $nom);
                    } elseif (!empty($auteur['nom_famille']) && in_array('nom_famille', $have)) {
                        $nom_adherent = $auteur['nom_famille'];
                    } elseif (!empty($auteur['nom_entreprise']) && in_array('nom_entreprise', $have)) {
                        $nom_adherent = $auteur['nom_entreprise'];
                    }
                }
            }
        }
        if (empty($nom_adherent)) {
            // fallback : utiliser l'email comme repère si aucun nom
            $nom_adherent = $email_inscrit;
        }
        $sujet = _T('association_evenements:email_recu_encaissement_participation_sujet', array(
                                'nom_adherent' => $nom_adherent,
                                'titre_evenement' => $titre_evenement,
                                'numero_recu' => $numero_recu)
               );
        $modele = 'notifications/recu_encaissement_participation';
    }elseif($type_recu == 'remboursement'){
         // idem pour remboursement
         $nom_adherent = '';
        if (!empty($email_inscrit)) {
            // Reprendre la logique dynamique de sélection des champs
            $desc = sql_showtable('spip_auteurs', true);
            $want = array('prenom','nom','nom_famille','nom_entreprise');
            $have = array();
            if (is_array($desc) && isset($desc['field']) && is_array($desc['field'])) {
                foreach ($want as $w) {
                    if (array_key_exists($w, $desc['field'])) $have[] = $w;
                }
            }
            if (empty($have)) {
                association_log('notifications', "facteur_envoyer_recu_participation remboursement: aucun des champs attendus n'existe dans spip_auteurs", 'critique');
            } else {
                $select = implode(',', $have);
                $auteur = sql_fetsel($select, 'spip_auteurs', 'email=' . sql_quote($email_inscrit));
                if ($auteur === false) {
                    association_log('notifications', "facteur_envoyer_recu_participation remboursement: erreur sql lors de la lecture de spip_auteurs pour email=" . $email_inscrit, 'critique');
                } elseif ($auteur) {
                    if ((!empty($auteur['prenom']) && in_array('prenom', $have)) || (!empty($auteur['nom']) && in_array('nom', $have))) {
                        $prenom = ($auteur['prenom'] ?? '');
                        $nom = ($auteur['nom'] ?? '');
                        $nom_adherent = trim($prenom . ' ' . $nom);
                    } elseif (!empty($auteur['nom_famille']) && in_array('nom_famille', $have)) {
                        $nom_adherent = $auteur['nom_famille'];
                    } elseif (!empty($auteur['nom_entreprise']) && in_array('nom_entreprise', $have)) {
                        $nom_adherent = $auteur['nom_entreprise'];
                    }
                }
            }
        }
        if (empty($nom_adherent)) {
            $nom_adherent = $email_inscrit;
        }
         $sujet = _T('association_evenements:email_recu_remboursement_participation_sujet', array(
                                'nom_adherent' => $nom_adherent,
                                'titre_evenement' => $titre_evenement,
                                'numero_recu' => $numero_recu)
               );
        $modele = 'notifications/recu_remboursement_participation';
    }

    $fond_content = array(
        'id_transaction' => $id_transaction,
        'id_evenement' => $id_evenement,
        'id_activite' => $id_activite,
        'devise' => $query_transaction['devise'],
        'numero_recu' => $numero_recu,
        'raison' => $raison
                                    );
    $html = recuperer_fond($modele, $fond_content);
    $bcc_meta = $GLOBALS['association_metas']['config_envoi_recu_participation_cc'] ?? '';
    $bcc_parsed = parser_emails_depuis_config($bcc_meta);
    $bcc = !empty($bcc_parsed) ? $bcc_parsed : false;

    $envoyer = facteur_envoyer_app($email_inscrit, $sujet, $html, $bcc, array('enqueued' => true, 'use_queue' => false));

    return $envoyer;
}
