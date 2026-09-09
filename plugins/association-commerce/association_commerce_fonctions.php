<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_commerce_config($cle, $defaut = null) {
	return array_key_exists((string) $cle, $GLOBALS['association_metas'] ?? [])
		? $GLOBALS['association_metas'][(string) $cle]
		: $defaut;
}

function association_commerce_afficher_prix($valeur, $decimales = 2, $devise = 'EUR') {
	$montant = number_format((float) $valeur, (int) $decimales, ',', ' ');
	$code = strtoupper(trim((string) $devise));
	$symbole = $code === 'EUR' ? '€' : ($code === 'USD' ? '$' : $code);
	return '<span itemprop="price" content="' . attribut_html((string) $valeur) . '">' . $montant . '</span>'
		. '&nbsp;<span itemprop="priceCurrency" content="' . attribut_html($code) . '">' . entites_html($symbole) . '</span>';
}

function association_commerce_etapes_panier($id_panier = 0) {
	$etapes = ['panier', 'identite', 'commande', 'paiement'];
	if ($id_panier && intval(sql_getfetsel('id_auteur', 'spip_paniers', 'id_panier=' . intval($id_panier)))) {
		$etapes = array_values(array_diff($etapes, ['identite']));
	}
	return $etapes;
}
