<?php
namespace TrsVendors\Dgm\Shengine\Aggregators;

use Dgm\Shengine\Interfaces\IRate;


class MaxAggregator extends \TrsVendors\Dgm\Shengine\Aggregators\ReduceAggregator
{
    protected function reduce(\TrsVendors\Dgm\Shengine\Interfaces\IRate $carry = null, \TrsVendors\Dgm\Shengine\Interfaces\IRate $current)
    {
        if (!isset($carry) || $carry->getCost() < $current->getCost()) {
            $carry = $current;
        }

        return $carry;
    }
}