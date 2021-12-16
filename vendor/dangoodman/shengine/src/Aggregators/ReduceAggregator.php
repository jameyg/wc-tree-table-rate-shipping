<?php
namespace TrsVendors\Dgm\Shengine\Aggregators;


use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\IAggregator;
use Dgm\Shengine\Interfaces\IRate;
use Dgm\Shengine\Model\Rate;
use Dgm\Shengine\Processing\RateRegister;


abstract class ReduceAggregator extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\IAggregator
{
    public function aggregateRates(array $rates)
    {
        $rate = $this->_reduce($rates);

        if ($rate instanceof \TrsVendors\Dgm\Shengine\Processing\RateRegister) {
            $rate = $rate->toRate();
        }

        return $rate;
    }

    /**
     * @param IRate $carry
     * @param IRate $current
     * @return IRate
     */
    protected abstract function reduce(\TrsVendors\Dgm\Shengine\Interfaces\IRate $carry = null, \TrsVendors\Dgm\Shengine\Interfaces\IRate $current);

    private function _reduce(array $rates)
    {
        $carry = null;
        foreach ($rates as $rate) {
            $carry = $this->reduce($carry, $rate);
        }

        return $carry;
    }
}