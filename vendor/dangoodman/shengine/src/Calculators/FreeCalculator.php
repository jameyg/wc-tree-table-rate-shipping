<?php
namespace TrsVendors\Dgm\Shengine\Calculators;

use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Model\Rate;


class FreeCalculator implements \TrsVendors\Dgm\Shengine\Interfaces\ICalculator
{
    public function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return array(new \TrsVendors\Dgm\Shengine\Model\Rate(0));
    }

    public function multipleRatesExpected()
    {
        return false;
    }
}