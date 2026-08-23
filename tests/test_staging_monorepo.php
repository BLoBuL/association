<?php

$root = dirname(__DIR__);
$script = $root . '/tools/stage-suite.ps1';
$stage = sys_get_temp_dir() . '/association-stage-' . bin2hex(random_bytes(6));
$erreurs = array();

function association_test_supprimer_stage($directory) {
	if (!is_dir($directory) || strpos(realpath($directory), realpath(sys_get_temp_dir())) !== 0) {
		return;
	}
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($iterator as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($directory);
}

try {
	$command = 'powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File '
		. escapeshellarg(str_replace('/', DIRECTORY_SEPARATOR, $script))
		. ' -Destination ' . escapeshellarg(str_replace('/', DIRECTORY_SEPARATOR, $stage));
	exec($command . ' 2>&1', $sortie, $code);
	if ($code !== 0) {
		$erreurs[] = "le staging PowerShell echoue:\n" . implode("\n", $sortie);
	} else {
		$plugins = array(
			'association', 'association-adhesions', 'association-communication',
			'association-compta', 'association-dons', 'association-evenements',
			'association-groupes', 'association-paiements', 'association-prets',
			'association-ventes',
		);
		foreach ($plugins as $plugin) {
			if (!is_file($stage . '/' . $plugin . '/paquet.xml')) {
				$erreurs[] = 'plugin absent du staging: ' . $plugin;
			}
		}
		if (is_dir($stage . '/association/plugins')) {
			$erreurs[] = 'les modules ne doivent jamais etre imbriques dans association/plugins';
		}
		$packages = glob($stage . '/*/paquet.xml');
		if (count($packages) !== 10) {
			$erreurs[] = 'le staging doit contenir exactement dix paquet.xml de premier niveau';
		}

		$manifestPath = $stage . '/association-suite-manifest.json';
		$manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
		if (!is_array($manifest) || count($manifest['plugins'] ?? array()) !== 10) {
			$erreurs[] = 'manifest JSON absent ou incomplet';
		} else {
			foreach ($manifest['plugins'] as $entry) {
				if (empty($entry['prefix']) || empty($entry['version']) ||
					!preg_match('/^[a-f0-9]{64}$/', $entry['sha256'] ?? '') ||
					(int) ($entry['files'] ?? 0) < 1) {
					$erreurs[] = 'entree de manifeste invalide: ' . json_encode($entry);
				}
			}
		}
	}
} finally {
	association_test_supprimer_stage($stage);
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: staging du monorepo en dix plugins freres.\n";
