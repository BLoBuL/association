<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_commerce_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, array('plugins' => array('association_commerce')));
}
