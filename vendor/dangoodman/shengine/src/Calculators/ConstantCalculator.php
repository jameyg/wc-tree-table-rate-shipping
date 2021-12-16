<?php
namespace TrsVendors\Dgm\Shengine\Calculators;

use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Model\Rate;


class ConstantCalculator implements \TrsVendors\Dgm\Shengine\Interfaces\ICalculator
{
    public function __construct($cost)
    {
        $this->cost = $cost;
    }

    public function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return array(new \TrsVendors\Dgm\Shengine\Model\Rate($this->cost));
    }

    public function multipleRatesExpected()
    {
        return false;
    }

    private $cost;
}
