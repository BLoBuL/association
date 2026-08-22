<?php
$r=dirname(__DIR__);$b=file_get_contents($r.'/base/association_prets.php');$a=file_get_contents($r.'/association_prets_administrations.php');
foreach(array('spip_asso_ressources','spip_asso_prets') as $t){if(strpos($b,$t)===false){fwrite(STDERR,"Table absente: $t\n");exit(1);}}
if(strpos($a,'sql_drop_table')!==false){fwrite(STDERR,"Désinstallation destructive.\n");exit(1);}echo "OK: structure Association Prêts.\n";
