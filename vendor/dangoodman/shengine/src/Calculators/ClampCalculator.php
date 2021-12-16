<?php
namespace TrsVendors\Dgm\Shengine\Calculators;

use Dgm\Arrays\Arrays;
use Dgm\Range\Range;
use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Interfaces\IRate;
use Dgm\Shengine\Model\Rate;


class ClampCalculator implements \TrsVendors\Dgm\Shengine\Interfaces\ICalculator
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\ICalculator $calculator, \TrsVendors\Dgm\Range\Range $range)
    {
        $this->range = $range;
        $this->calculator = $calculator;
    }

    public function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        $range = $this->range;
        return \TrsVendors\Dgm\Arrays\Arrays::map($this->calculator->calculateRatesFor($package), function(\TrsVendors\Dgm\Shengine\Interfaces\IRate $rate) use($range) {
            return new \TrsVendors\Dgm\Shengine\Model\Rate($range->clamp($rate->getCost()), $rate->getTitle());
        });
    }

    public function multipleRatesExpected()
    {
        return $this->calculator->multipleRatesExpected();
    }

    private $calculator;
    private $range;
}