<?php
$racine = dirname(__DIR__);
$module = $racine . '/plugins/association-bons-plans';
$erreurs = array();
$verifier = function ($condition, $message) use (&$erreurs) { if (!$condition) { $erreurs[] = $message; } };

$paquet = file_get_contents($module . '/paquet.xml');
$base = file_get_contents($module . '/base/association_bons_plans.php');
$administration = file_get_contents($module . '/association_bons_plans_administrations.php');
$proposition = file_get_contents($module . '/formulaires/proposer_bon_plan.php');

$verifier(str_contains($paquet, '<necessite nom="association"') && str_contains($paquet, '<necessite nom="saisies"'), 'Bons plans doit déclarer uniquement ses dépendances techniques directes.');
$verifier(str_contains($paquet, '<incompatible nom="spip_bon_plan"'), 'Le plugin historique concurrent doit être déclaré incompatible.');
$verifier(str_contains($base, "\$tables['spip_bons_plans']") && str_contains($base, "\$tables['spip_bons_plans_liens']"), 'Les tables historiques doivent être reprises sans copie de données.');
$verifier(!str_contains($administration, 'sql_drop_table'), 'La désactivation ne doit pas supprimer les bons plans historiques.');
$verifier(str_contains($proposition, "'statut' => 'prop'") && str_contains($proposition, 'association_notifier_metier'), 'La proposition publique doit rester modérable et notifier par le contrat facultatif.');
$verifier(!str_contains($proposition, 'facteur_envoyer_app') && !str_contains($proposition, 'job_queue_add'), 'Aucun appel Blobul ou Facteur historique ne doit subsister.');

foreach (array('bons_plans.html', 'bon_plan.html', 'proposer_bon_plan.html') as $page) {
	$verifier(is_file($module . '/squelettes/' . $page), 'Page publique absente : ' . $page);
}

if ($erreurs) { fwrite(STDERR, implode(PHP_EOL, $erreurs) . PHP_EOL); exit(1); }
echo "OK: Bons plans autonome conserve les données et les fonctions publiques historiques.\n";
