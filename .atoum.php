<?php

use mageekguy\atoum\writers\std;
use mageekguy\atoum\reports\telemetry;

$script->addDefaultReport();

if (class_exists('mageekguy\atoum\reports\telemetry') === true)
{
    $telemetry = new telemetry();
    $telemetry->readProjectNameFromComposerJson(__DIR__ . '/composer.json');
    $telemetry->addWriter(new std\out());
    $runner->addReport($telemetry);
}

$script->noCodeCoverage();
$script->addTestsFromDirectory(__DIR__ . '/sources/tests/Unit');
