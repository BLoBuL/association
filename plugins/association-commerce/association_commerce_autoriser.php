<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_commerce_autoriser() {
}

function autoriser_commerce_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}
