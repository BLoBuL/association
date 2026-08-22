<?php

define('_ECRIRE_INC_VERSION', true);
function bank_afficher_mode($mode) { return strtoupper($mode); }
require dirname(__DIR__) . '/association_paiements_fonctions.php';

$stripe = association_paiements_mode_icone('stripe_test/carte');
if (!str_contains($stripe, 'fa-cc-stripe') || !str_contains($stripe, 'STRIPE')) {
	fwrite(STDERR, "ECHEC: icône Stripe invalide.\n");
	exit(1);
}
$inconnu = association_paiements_mode_icone('prestataire_nouveau');
if (!str_contains($inconnu, 'fa-credit-card') || !str_contains($inconnu, 'PRESTATAIRE_NOUVEAU')) {
	fwrite(STDERR, "ECHEC: fallback de paiement invalide.\n");
	exit(1);
}
echo "OK: icônes de paiement indépendantes de tout plugin Blobul.\n";
