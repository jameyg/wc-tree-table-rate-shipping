<?php
namespace TrsVendors\Dgm\Shengine\Calculators;

use Dgm\Shengine\Interfaces\IAggregator;
use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;


class AggregatedCalculator implements \TrsVendors\Dgm\Shengine\Interfaces\ICalculator
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\ICalculator $calculator, \TrsVendors\Dgm\Shengine\Interfaces\IAggregator $aggregator = null)
    {
        $this->calculator = $calculator;
        $this->aggregator = $aggregator;
    }

    public function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        $rates = $this->calculator->calculateRatesFor($package);

        if (isset($this->aggregator)) {
            $rate = $this->aggregator->aggregateRates($rates);
            $rates = isset($rate) ? array($rate) : array();
        }

        return $rates;
    }

    public function multipleRatesExpected()
    {
        return !isset($this->aggregator) && $this->calculator->multipleRatesExpected();
    }

    private $calculator;
    private $aggregator;
}