<?php

$racine = dirname(__DIR__);
$erreurs = array();
$verifier = static function (bool $condition, string $message) use (&$erreurs): void {
	if (!$condition) {
		$erreurs[] = $message;
	}
};

$paquet_commerce = file_get_contents($racine . '/plugins/association-commerce/paquet.xml');
$catalogue = file_get_contents($racine . '/plugins/association-commerce/inclure/association-commerce-catalogue.html');
$paquet_ventes = file_get_contents($racine . '/plugins/association-ventes/paquet.xml');
$schema = file_get_contents($racine . '/plugins/association-ventes/base/association_ventes.php');
$contrat = file_get_contents($racine . '/inc/association_capacites.php');
$commandes = file_get_contents($racine . '/plugins/association-ventes/inc/association_ventes_commandes.php');

$verifier(str_contains($paquet_commerce, '<necessite nom="produits"'), 'Commerce doit exiger Produits.');
$verifier(str_contains($catalogue, '<BOUCLE_catalogue(PRODUITS)') && str_contains($catalogue, '#URL_PRODUIT'), 'Le catalogue doit afficher les Produits.');
$verifier(str_contains($catalogue, '#FORMULAIRE_REMPLIR_PANIER{produit,#ID_PRODUIT}'), 'Le panier doit recevoir un Produit.');
$verifier(!str_contains($catalogue, '(ARTICLES)') && !str_contains($catalogue, '#ID_ARTICLE'), 'Le catalogue ne doit plus utiliser Articles.');

foreach (array('produits', 'prix', 'commandes') as $plugin) {
	$verifier(str_contains($paquet_ventes, '<utilise nom="' . $plugin . '"'), "Ventes doit conserver $plugin facultatif.");
}
$verifier(str_contains($paquet_ventes, 'schema="1.1.0"'), 'Le schéma Ventes doit être migré en 1.1.0.');
foreach (array('id_produit', 'id_commande', 'id_commandes_detail', 'origine', 'prix_unitaire_ht', 'taxe', 'reduction') as $champ) {
	$verifier(str_contains($schema, "'$champ'"), "Le schéma Ventes doit posséder $champ.");
}
$verifier(str_contains($schema, 'UNIQUE KEY commande_detail'), 'La liaison commande/détail doit être unique.');
$verifier(str_contains($contrat, 'function association_enregistrer_vente('), 'Le socle doit publier le contrat Vente.');
$verifier(str_contains($commandes, 'function association_ventes_commande_synchroniser('), 'Ventes doit synchroniser une Commande.');
$verifier(str_contains($commandes, "\"objet='produit'\""), 'Seules les lignes Produit doivent devenir des ventes.');
$verifier(str_contains($commandes, "'id_commande=' . (int) \$vente['id_commande']") && str_contains($commandes, "'id_commandes_detail=' . (int) \$vente['id_commandes_detail']"), 'La synchronisation doit relire la clé idempotente.');

// Preuve fonctionnelle minimale : le même détail de commande est inséré puis
// mis à jour en conservant son identifiant.
if (!defined('_ECRIRE_INC_VERSION')) {
	define('_ECRIRE_INC_VERSION', true);
}
$GLOBALS['test_ventes_lignes'] = array();
function sql_getfetsel($champ, $table, $where) {
	foreach ($GLOBALS['test_ventes_lignes'] as $id => $ligne) {
		if ((int) $ligne['id_commande'] === 42 && (int) $ligne['id_commandes_detail'] === 7) {
			return $id;
		}
	}
	return null;
}
function sql_insertq($table, $champs) {
	$id = count($GLOBALS['test_ventes_lignes']) + 1;
	$GLOBALS['test_ventes_lignes'][$id] = $champs;
	return $id;
}
function sql_updateq($table, $champs, $where) {
	$id = (int) preg_replace('/\D+/', '', $where);
	$GLOBALS['test_ventes_lignes'][$id] = array_merge($GLOBALS['test_ventes_lignes'][$id], $champs);
	return true;
}
require_once $racine . '/plugins/association-ventes/inc/association_ventes_commandes.php';
$instantane = array(
	'id_commande' => 42,
	'id_commandes_detail' => 7,
	'origine' => 'commande',
	'article' => 'Produit de recette',
);
$premier = association_ventes_enregistrer($instantane);
$second = association_ventes_enregistrer($instantane + array('article' => 'Produit rejoué'));
$verifier($premier['id_vente'] === 1 && $second['id_vente'] === 1, 'Le rejeu doit conserver le même id_vente.');
$verifier(count($GLOBALS['test_ventes_lignes']) === 1, 'Le rejeu ne doit créer aucune vente supplémentaire.');

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK intégration Produits, Prix, Commandes et Ventes\n";
