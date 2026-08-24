<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

include_spip('inc/association_ventes_comptabilite');
include_spip('inc/destinations');

function action_editer_asso_ventes($id_vente = null)
{
    if ($id_vente === null) {
        $securiser_action = charger_fonction('securiser_action', 'inc');
        $id_vente = $securiser_action();
    }
    $id_vente = (int) $id_vente;

    $id_compte=intval(_request('id_compte'));
    $date_vente = _request('date_vente');
    $article = _request('article');
    $code = _request('code');
    $acheteur = _request('acheteur');
    $id_acheteur = intval(_request('id_acheteur'));
    $quantite = association_recupere_montant(_request('quantite'));

    $date_envoi = _request('date_envoi');
    $frais_envoi = association_recupere_montant(_request('frais_envoi'));
    $prix_vente =  association_recupere_montant(_request('prix_vente'));

    $journal = _request('journal');
    $justification='[vente n&deg; '.$id_vente.'->asso_vente'.$id_vente.'] - '.$article;
    $commentaire=_request('commentaire');
    $recette=$quantite*$prix_vente;

    /* modification */
    if ($id_vente) {
        ventes_modifier($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $id_vente, $journal, $justification, $recette, $id_compte);
    } else { /* ajout */
        $id_vente = ventes_insert($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $journal, $recette);
    }

    return array($id_vente, '');
}

function ventes_modifier($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $id_vente, $journal, $justification, $recette, $id_compte)
{
    sql_updateq(
        'spip_asso_ventes',
        array(
            "date_vente" => $date_vente,
            "article" => $article,
            "code" => $code,
            "acheteur" => $acheteur,
            "id_acheteur" => $id_acheteur,
            "quantite" => $quantite,
            "date_envoi" => $date_envoi,
            "frais_envoi" => $frais_envoi,
            "prix_vente" => $prix_vente,
            "commentaire" => $commentaire),
        "id_vente=$id_vente"
    );

    if ($GLOBALS['association_metas']['pc_ventes']==$GLOBALS['association_metas']['pc_frais_envoi']) {
        /* si ventes et frais d'envoi sont associes a la meme reference, on modifie une seule operation */
        association_ventes_compte_modifier($id_compte, $date_vente, $recette+$frais_envoi, $justification, $journal, $id_vente, $id_acheteur, $GLOBALS['association_metas']['pc_ventes']);
    } else { /* sinon on en modifie deux */
        association_ventes_compte_modifier($id_compte, $date_vente, $recette, $justification, $journal, $id_vente, $id_acheteur, $GLOBALS['association_metas']['pc_ventes']);
        $compte_frais = association_ventes_compte_lire($id_vente, $GLOBALS['association_metas']['pc_frais_envoi']);
        association_ventes_compte_modifier(
			$compte_frais['id_compte'] ?? 0,
			$date_vente,
			$frais_envoi,
			$justification . ' - frais d\'envoi',
			$journal,
			$id_vente,
			$id_acheteur,
			$GLOBALS['association_metas']['pc_frais_envoi']
        );
    }
	$montant_destination = $recette
		+ ($GLOBALS['association_metas']['pc_ventes'] == $GLOBALS['association_metas']['pc_frais_envoi'] ? $frais_envoi : 0);
	ajouter_destinations((int) $id_compte, (float) $montant_destination, 0);
}

function ventes_insert($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $journal, $recette)
{
    $id_vente = sql_insertq('spip_asso_ventes', array(
        'date_vente' => $date_vente,
        'article' => $article,
        'code' => $code,
        'acheteur' => $acheteur,
        'id_acheteur' => $id_acheteur,
        'quantite' => $quantite,
        'date_envoi' => $date_envoi,
        'frais_envoi' => $frais_envoi,
        'prix_vente' => $prix_vente,
        'commentaire' => $commentaire));

    $justification='[vente n&deg; '.$id_vente.'->asso_vente'.$id_vente.'] - '.$article;
    if ($GLOBALS['association_metas']['pc_ventes']==$GLOBALS['association_metas']['pc_frais_envoi']) {
        /* si ventes et frais d'envoi sont associes a la meme reference, on ajoute une seule operation */
        $id_compte = association_ventes_compte_creer($date_vente, $recette+$frais_envoi, $justification, $journal, $id_vente, $id_acheteur, $GLOBALS['association_metas']['pc_ventes']);
    } else { /* sinon on en insere deux */
        $id_compte = association_ventes_compte_creer($date_vente, $recette, $justification, $journal, $id_vente, $id_acheteur, $GLOBALS['association_metas']['pc_ventes']);
        association_ventes_compte_creer($date_vente, $frais_envoi, $justification . ' - frais d\'envoi', $journal, $id_vente, $id_acheteur, $GLOBALS['association_metas']['pc_frais_envoi']);
    }
	$montant_destination = $recette
		+ ($GLOBALS['association_metas']['pc_ventes'] == $GLOBALS['association_metas']['pc_frais_envoi'] ? $frais_envoi : 0);
	ajouter_destinations((int) $id_compte, (float) $montant_destination, 0);
    return $id_vente;
}
