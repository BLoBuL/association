<?php

define('_ECRIRE_INC_VERSION', 1);
function include_spip($path) {}
function association_normalize_qui($qui) { return $qui; }
function association_est_admin_complet($qui) { return $qui['statut'] === '0minirezo' && empty($qui['restreint']); }
function association_module_actif($module) { return $GLOBALS['compta_active']; }
require dirname(__DIR__) . '/plugins/association-compta/association_compta_autoriser.php';
$GLOBALS['compta_active'] = true;
foreach (['modifier', 'supprimer'] as $faire) {
	// Convention de résolution de autoriser_dist() : type sans soulignés.
	$fonction = 'autoriser_' . str_replace('_', '', 'asso_plan') . '_' . $faire . '_dist';
	foreach ([['statut' => '0minirezo', 'restreint' => []], ['statut' => '1comite', 'restreint' => []], ['statut' => '0minirezo', 'restreint' => [1]]] as $index => $qui) {
		if ($fonction($faire, 'assoplan', 12, $qui, []) !== ($index === 0)) {
			throw new RuntimeException('Droit incorrect : ' . $fonction);
		}
	}
}
$GLOBALS['compta_active'] = false;
if (autoriser_assoplan_modifier_dist('modifier', 'assoplan', 12, ['statut' => '0minirezo', 'restreint' => []], [])) {
	throw new RuntimeException('Configuration comptable désactivée ignorée');
}
echo "OK : noms SPIP du plan comptable, administrateur complet et module actif exigés.\n";
