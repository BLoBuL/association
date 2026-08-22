<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Produit une icône accessible pour un mode fourni par le plugin Bank.
 *
 * Le libellé public reste fourni par Bank lorsqu'il existe. Les prestataires
 * non connus utilisent volontairement l'icône générique de carte.
 */
function association_paiements_mode_icone($mode) {
	$mode = strtolower(trim((string) explode('/', (string) $mode)[0]));
	if ($mode === '') {
		return '';
	}
	if (str_ends_with($mode, '_test')) {
		$mode = substr($mode, 0, -5);
	}

	$icones = array(
		'simu' => 'fa fa-wrench',
		'gratuit' => 'fa fa-gift',
		'cheque' => 'fa fa-envelope',
		'eft' => 'fa fa-building-columns',
		'virement' => 'fa-brands fa-wpforms',
		'interac' => 'fa-brands fa-wpforms',
		'espece' => 'fa-solid fa-coins',
		'a_payer' => 'fa-solid fa-lock',
		'paypal' => 'fa-brands fa-cc-paypal',
		'paypaladvanced' => 'fa-brands fa-paypal',
		'venmo' => 'fa-solid fa-mobile-screen-button',
		'mobilepay' => 'fa-solid fa-mobile-screen-button',
		'zettle' => 'fa-solid fa-z',
		'stripe' => 'fa-brands fa-cc-stripe',
		'swish' => 'fa-solid fa-s',
		'zelle' => 'fa-solid fa-z',
		'ziina' => 'fa-solid fa-bolt',
	);
	$classe = $icones[$mode] ?? 'fa fa-credit-card';
	$label = function_exists('bank_afficher_mode') ? bank_afficher_mode($mode) : ucfirst(str_replace('_', ' ', $mode));
	$label = trim(strip_tags((string) $label)) ?: ucfirst(str_replace('_', ' ', $mode));
	$label_attr = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

	return '<span class="association-paiement-mode" title="' . $label_attr . '"><i class="' . $classe . '" aria-hidden="true"></i><span class="visually-hidden">' . $label_attr . '</span></span>';
}
