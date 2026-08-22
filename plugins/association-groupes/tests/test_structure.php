<?php
$p=file_get_contents(dirname(__DIR__).'/paquet.xml');if(strpos($p,'nom="cextras"')===false){fwrite(STDERR,"Dépendance Champs Extras absente.\n");exit(1);}echo "OK: structure Association Groupes.\n";
