<?php

use SpipLeague\EasyCodingStandard\Set\SetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withSets([SetList::SPIP])
	->withPaths([__DIR__])
	->withSkip([
		__DIR__ . '/vendor',
		__DIR__ . '/lib',
		__DIR__ . '/tests',
		__DIR__ . '/docs',
		__DIR__ . '/lang',
		__DIR__ . '/plugins/*/lang',
		__DIR__ . '/plugins/*/tests',
	]);
