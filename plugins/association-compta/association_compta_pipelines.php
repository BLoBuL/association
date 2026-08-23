<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

function association_compta_post_edition($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_edition'));
        }
    }
    return $flux;
}

function association_compta_post_insertion($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_insertion'));
        }
    }
    return $flux;
}
