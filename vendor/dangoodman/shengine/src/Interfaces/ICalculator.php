<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;


interface ICalculator
{
    /**
     * @param IPackage $package
     * @return IRate[]
     */
    function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package);

    /**
     * @return bool False if no more than one rate is expected to be produced on any package
     */
    function multipleRatesExpected();
}