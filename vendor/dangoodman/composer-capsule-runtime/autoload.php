<?php
require_once(__DIR__.'/src/Runtime.php');
require_once(__DIR__.'/src/Wrapper.php');
require_once(__DIR__.'/src/CCR.php');

return function($remappedNamesRegistryFile)
{
    /** @noinspection PhpIncludeInspection */
    $capsuled = require($remappedNamesRegistryFile);

    TrsVendors_CCR::$instance = new \TrsVendors\Dgm\ComposerCapsule\Runtime\Runtime(
        new \TrsVendors\Dgm\ComposerCapsule\Runtime\Wrapper(
            $capsuled['capsule'],
            $capsuled['uncapsule']
        )
    );
};