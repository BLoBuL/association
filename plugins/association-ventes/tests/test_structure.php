<?php
$r=dirname(__DIR__);$b=file_get_contents($r.'/base/association_ventes.php');$a=file_get_contents($r.'/association_ventes_administrations.php');
if(strpos($b,'spip_asso_ventes')===false||strpos($a,'sql_drop_table')!==false){fwrite(STDERR,"Structure Ventes invalide.\n");exit(1);}echo "OK: structure Association Ventes.\n";
