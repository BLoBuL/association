<?php

$racine = dirname(__DIR__) . '/plugins/association-evenements/notifications';
$reservation = file_get_contents($racine . '/inc/inc-email_event_reservation.html');
$remboursement = file_get_contents($racine . '/recu_remboursement_participation.html');
$erreurs = array();

if (str_contains($reservation, 'local_to_utc') || !str_contains($reservation, '|date_iso')) {
	$erreurs[] = 'Les dates structurées doivent utiliser le filtre SPIP date_iso.';
}
if (str_contains($remboursement, '|strtoupper #')) {
	$erreurs[] = 'Le reçu de remboursement contient encore un enchaînement de filtres invalide.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: les notifications Événements utilisent des filtres SPIP 4 valides.\n";
