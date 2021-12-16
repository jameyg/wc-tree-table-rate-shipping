<?php
namespace TrsVendors\Dgm\Shengine\Aggregators;

use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\IAggregator;


class LastAggregator extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\IAggregator
{
    public function aggregateRates(array $rates)
    {
        return $rates ? end($rates) : null;
    }
}