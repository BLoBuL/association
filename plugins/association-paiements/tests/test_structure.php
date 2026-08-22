<?php
$p=file_get_contents(dirname(__DIR__).'/paquet.xml');foreach(array('nom="bank"','nom="association_adhesions"','nom="association_evenements"','nom="association_compta"') as $a){if(strpos($p,$a)===false){fwrite(STDERR,"Dépendance absente: $a\n");exit(1);}}echo "OK: structure Association Paiements.\n";
