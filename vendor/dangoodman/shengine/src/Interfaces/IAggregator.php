<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;


interface IAggregator
{
    /**
     * @param IRate[] $rates
     * @return IRate|null
     */
    public function aggregateRates(array $rates);
}