<?php

/** Lance chaque scénario dans un processus isolé, sans instance SPIP ni envoi réel. */
$fichiers = glob(__DIR__ . '/test_*.php');
$echecs = [];
foreach ($fichiers as $fichier) {
	if (in_array('--verbose', $argv, true)) {
		echo basename($fichier) . "\n";
	}
	$sortie = [];
	exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($fichier) . ' 2>&1', $sortie, $code);
	$texte = implode("\n", $sortie);
	if ($code !== 0 || preg_match('/PHP (?:Warning|Fatal error|Parse error|Deprecated):/', $texte)) {
		$echecs[] = basename($fichier);
		fwrite(STDERR, basename($fichier) . "\n" . $texte . "\n");
	}
}
echo count($fichiers) . ' scripts, ' . count($echecs) . " échec(s).\n";
exit($echecs ? 1 : 0);
