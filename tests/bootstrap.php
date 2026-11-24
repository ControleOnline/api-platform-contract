<?php

// Same layout as the other modules: installed under api-community/modules/controleonline/contract.
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

foreach ($autoloadPaths as $autoloadPath) {
    if (is_file($autoloadPath)) {
        $loader = require_once $autoloadPath;
        // Make sure this checkout's sources win over any installed copy of the package.
        if ($loader instanceof \Composer\Autoload\ClassLoader) {
            $loader->addPsr4('ControleOnline\\', __DIR__ . '/../src/', true);
        }

        $reporting = __DIR__ . '/../../../../config/test-reporting.php';
        if (is_file($reporting)) {
            require_once $reporting;
            ensureTestReportDirectory(__DIR__ . '/../../../../var/tests/phpunit/contract');
        }

        return;
    }
}

throw new RuntimeException('Composer autoload.php not found for contract module tests.');
