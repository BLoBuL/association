<?php

$source = file_get_contents(dirname(__DIR__) . '/formulaires/inc/inscription_evenement.php');

if (!str_contains($source, 'if (!is_scalar($id_participant))')
	|| !str_contains($source, '$id_participant = trim((string) $id_participant)')
	|| !str_contains($source, "if (\$id_participant === '')")) {
	fwrite(STDERR, "Les participants vides ou non scalaires atteignent encore les index de mapping sous PHP 8.\n");
	exit(1);
}

echo "OK: les identifiants de participants sont normalisés avant leur usage comme index.\n";
