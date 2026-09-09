<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssociationConfigRestaurer extends Command
{
	protected function configure() {
		$this->setName('association:config:restaurer')
			->setDescription('Restaure un instantane JSON complet de la configuration Association autorisee.')
			->addArgument('fichier', InputArgument::REQUIRED, 'Chemin du fichier JSON cree par association:config:lire --snapshot')
			->addOption('format', null, InputOption::VALUE_REQUIRED, 'Format de sortie : human ou json', 'human')
			->addOption('pretty', null, InputOption::VALUE_NONE, 'Indente la sortie JSON');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$format = strtolower((string) $input->getOption('format'));
		if (!in_array($format, ['human', 'json'], true)) {
			$output->writeln('Format invalide : utiliser human ou json.');
			return self::INVALID;
		}
		$fichier = (string) $input->getArgument('fichier');
		if (!is_file($fichier) || !is_readable($fichier)) {
			$this->afficherResultat($output, $format, (bool) $input->getOption('pretty'), ['ok' => false, 'reason' => 'snapshot_unreadable']);
			return self::FAILURE;
		}
		$contenu = file_get_contents($fichier);
		$payload = json_decode($contenu, true);
		if (!is_array($payload)) {
			$this->afficherResultat($output, $format, (bool) $input->getOption('pretty'), ['ok' => false, 'reason' => 'invalid_snapshot']);
			return self::INVALID;
		}
		$snapshot = $payload['snapshot'] ?? $payload;
		$this->demarrerSpip();
		include_spip('inc/association_config_cli');
		$resultat = association_config_cli_restaurer($snapshot);
		$this->afficherResultat($output, $format, (bool) $input->getOption('pretty'), $resultat);
		return association_config_cli_code_sortie($resultat);
	}

	private function afficherResultat(OutputInterface $output, $format, $pretty, array $resultat) {
		$payload = ['status' => !empty($resultat['ok']) ? 'ok' : 'error', 'command' => 'association:config:restaurer'];
		if (!empty($resultat['ok'])) {
			$payload['restored'] = $resultat['restored'];
			$payload['verified'] = $resultat['verified'];
		} else {
			$payload['reason'] = $resultat['reason'] ?? 'unknown_error';
			if (isset($resultat['option'])) {
				$payload['option'] = $resultat['option'];
			}
			if (isset($resultat['rollback_restored'])) {
				$payload['rollback_restored'] = $resultat['rollback_restored'];
			}
		}
		if ($format === 'json') {
			$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
			if ($pretty) {
				$flags |= JSON_PRETTY_PRINT;
			}
			$output->writeln(json_encode($payload, $flags));
			return;
		}
		if (empty($resultat['ok'])) {
			$output->writeln('Erreur : ' . $payload['reason'] . (isset($payload['option']) ? ' (' . $payload['option'] . ')' : ''));
			return;
		}
		$output->writeln($resultat['restored'] . ' options restaurees et verifiees.');
	}
}
