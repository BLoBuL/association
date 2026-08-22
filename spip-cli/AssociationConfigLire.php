<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssociationConfigLire extends Command
{
	protected function configure()
	{
		$this->setName('association:config:lire')
			->setDescription('Lit les options de configuration autorisees du plugin Association.')
			->addArgument('option', InputArgument::OPTIONAL, 'Option autorisee, par exemple debug ou debug.inscriptions')
			->addOption('format', null, InputOption::VALUE_REQUIRED, 'Format de sortie : human ou json', 'human')
			->addOption('snapshot', null, InputOption::VALUE_NONE, 'Ajoute un instantane restaurable complet a la sortie JSON')
			->addOption('pretty', null, InputOption::VALUE_NONE, 'Indente la sortie JSON');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$format = strtolower((string) $input->getOption('format'));
		if (!in_array($format, array('human', 'json'), true)) {
			$output->writeln('Format invalide : utiliser human ou json.');
			return self::INVALID;
		}
		if ($input->getOption('snapshot') && ($format !== 'json' || $input->getArgument('option'))) {
			$output->writeln('L option --snapshot exige --format=json et interdit un filtre optionnel.');
			return self::INVALID;
		}

		$this->demarrerSpip();
		include_spip('inc/association_config_cli');
		if (!function_exists('association_config_cli_lire')) {
			$output->writeln('Configuration Association indisponible.');
			return self::FAILURE;
		}

		$resultat = association_config_cli_lire($input->getArgument('option'));
		if (!empty($resultat['ok']) && $input->getOption('snapshot')) {
			$capture = association_config_cli_capturer();
			if (empty($capture['ok'])) {
				$resultat = $capture;
			} else {
				$resultat['snapshot'] = $capture['snapshot'];
			}
		}
		$code = association_config_cli_code_sortie($resultat);
		$this->afficherResultat($output, $format, (bool) $input->getOption('pretty'), $resultat);
		return $code;
	}

	private function afficherResultat(OutputInterface $output, $format, $pretty, array $resultat)
	{
		$payload = array(
			'status' => !empty($resultat['ok']) ? 'ok' : 'error',
			'command' => 'association:config:lire',
		);
		if (!empty($resultat['ok'])) {
			$payload['options'] = $resultat['options'];
			if (isset($resultat['snapshot'])) {
				$payload['snapshot'] = $resultat['snapshot'];
			}
		} else {
			$payload['reason'] = isset($resultat['reason']) ? $resultat['reason'] : 'unknown_error';
			$payload['option'] = isset($resultat['option']) ? $resultat['option'] : null;
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
			$output->writeln('Erreur : ' . $payload['reason'] . ($payload['option'] ? ' (' . $payload['option'] . ')' : ''));
			return;
		}
		foreach ($resultat['options'] as $option => $valeur) {
			$output->writeln($option . ': ' . $this->formaterValeur($valeur));
		}
	}

	private function formaterValeur($valeur)
	{
		return is_array($valeur)
			? json_encode($valeur, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
			: (string) $valeur;
	}
}
