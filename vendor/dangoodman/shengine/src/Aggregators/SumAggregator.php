<?php
namespace TrsVendors\Dgm\Shengine\Aggregators;

use Dgm\Shengine\Interfaces\IRate;
use Dgm\Shengine\Processing\RateRegister;


class SumAggregator extends \TrsVendors\Dgm\Shengine\Aggregators\ReduceAggregator
{
    protected function reduce(\TrsVendors\Dgm\Shengine\Interfaces\IRate $carry = null, \TrsVendors\Dgm\Shengine\Interfaces\IRate $current)
    {
        if (!isset($carry)) {
            $carry = new \TrsVendors\Dgm\Shengine\Processing\RateRegister();
        }

        $carry->add($current);

        return $carry;
    }
}